# ESC/POS Label Printing

## Overview

This document describes how to generate ESC/POS binary data from the entry labels page, enabling direct printing to a thermal receipt printer without the browser print dialog.

---

## What ESC/POS Is and How It Works

ESC/POS (Epson Standard Code for POS) is a binary command language supported by most POS thermal receipt printers — Epson TM series, Star Micronics, Bixolon, HPRT, and many others. Unlike TSPL, which uses absolute dot coordinates, ESC/POS builds output sequentially from top to bottom, like printing a receipt.

**Key differences from TSPL:**

- **Layout is sequential** — content flows top-to-bottom; there are no absolute x/y positions.
- **Label size is implicit** — height is determined by content; width by the paper roll loaded.
- **Labels are separated by a cut command**, not a die-cut gap in the roll.
- **Commands are binary bytes**, not plain text.
- **Paper stock** is a continuous roll (58 mm or 80 mm receipt paper), not pre-cut labels.

**A minimal label in PHP:**

```php
$ESC = "\x1B";
$GS  = "\x1D";
$LF  = "\x0A";

$label  = $ESC . '@';                                       // ESC @ — initialize

$label .= $GS . '!' . "\x10";                              // double-height text
$label .= $ESC . 'E' . "\x01";                             // bold on
$label .= 'FLOWERS' . $LF;                                 // section name

$label .= $GS . '!' . "\x00";                              // normal size
$label .= $ESC . 'E' . "\x00";                             // bold off
$label .= 'Best Rose' . $LF;                               // class name

$label .= str_repeat('-', 32) . $LF;                       // divider
$label .= 'Smith, Alice' . $LF;                            // exhibitor name

$label .= $GS . '!' . "\x22";                              // 3× height & width
$label .= '42' . $LF;                                      // entry number
$label .= $GS . '!' . "\x00";                              // back to normal

$label .= $GS . 'h' . chr(80);                            // barcode height 80 dots
$label .= $GS . 'w' . chr(2);                             // module width 2 dots
$label .= $GS . 'H' . chr(2);                             // HRI text below
$label .= $GS . 'k' . chr(73) . chr(4) . '{B42';         // Code128 barcode

$label .= $LF . $LF;
$label .= $ESC . 'd' . chr(3);                            // feed 3 lines (tear margin)
$label .= $GS . 'V' . "\x00";                             // full cut
```

**Key commands:**

| Command | Hex | Notes |
|---|---|---|
| `ESC @` | `1B 40` | Initialize — must precede each print job |
| `GS ! n` | `1D 21 n` | Character size: bits 0–2 = width mult, bits 4–6 = height mult |
| `ESC E n` | `1B 45 n` | Bold: `01`=on, `00`=off |
| `ESC a n` | `1B 61 n` | Alignment: `00`=left, `01`=centre, `02`=right |
| `GS h n` | `1D 68 n` | Barcode height in dots (default 162; `50`=80 dots) |
| `GS w n` | `1D 77 n` | Barcode module width in dots (2–6) |
| `GS H n` | `1D 48 n` | HRI text position: `00`=none, `01`=above, `02`=below |
| `GS k 49 n [data]` | `1D 6B 49 n [data]` | Code128 barcode — n = data length; prefix `{B` selects Code Set B |
| `ESC d n` | `1B 64 n` | Feed n lines |
| `GS V 00` | `1D 56 00` | Full paper cut |

**Character size byte (`GS !`):** Bits 0–2 control width multiplier (0=×1, 1=×2, 2=×3); bits 4–6 control height (same scale). Common values:

| n (hex) | Width | Height |
|---|---|---|
| `00` | ×1 | ×1 (normal) |
| `10` | ×1 | ×2 (double-height) |
| `01` | ×2 | ×1 (double-wide) |
| `11` | ×2 | ×2 |
| `22` | ×3 | ×3 |

**Multiple labels:** Each label is its own command sequence ending with a cut. There is no global header — `ESC @` at the very start covers the whole job. Each subsequent label begins immediately after the preceding cut.

---

## How to Send the File to the Printer

The app generates and downloads a `labels.bin` file. The admin sends it directly to the printer — no print dialog involved. The file must reach the printer as raw bytes, without any driver reinterpreting them.

**Windows:**
```bat
copy /b labels.bin \\SERVER\PRINTERSHARE
```

**macOS / Linux:**
```bash
lpr -l -P "PrinterName" labels.bin
```
(`-l` = raw pass-through, bypasses driver filtering)

**Direct TCP/IP (any OS):**
Many ESC/POS printers listen on port 9100:
```bash
cat labels.bin | nc 192.168.1.100 9100
```

No printer driver is required — only a raw USB or TCP/IP connection to the printer.

---

## Planned Implementation

### New route (`routes/web.php`)

```php
Route::get('exhibitors/{exhibitor}/labels/escpos', [ExhibitorController::class, 'labelsAsEscPos'])
    ->name('exhibitors.labels-escpos');
```

### New controller method (`ExhibitorController`)

```php
public function labelsAsEscPos(Request $request, Exhibitor $exhibitor): Response
{
    $entryIds = array_filter((array) $request->query('entries', []));

    $entries = $exhibitor->entries()
        ->with(['showClass.showSection'])
        ->when(! empty($entryIds), fn ($q) => $q->whereIn('id', $entryIds))
        ->orderBy('entry_number')
        ->get();

    $data = $this->buildEscPos($exhibitor, $entries);

    return response($data)
        ->header('Content-Type', 'application/octet-stream')
        ->header('Content-Disposition', 'attachment; filename="labels.bin"');
}

private function buildEscPos(Exhibitor $exhibitor, \Illuminate\Support\Collection $entries): string
{
    $ESC = "\x1B";
    $GS  = "\x1D";
    $LF  = "\x0A";

    $out = $ESC . '@';  // initialize once at the start of the job

    foreach ($entries as $entry) {
        $section       = mb_strtoupper(Str::ascii(mb_substr($entry->showClass->showSection->name, 0, 24)));
        $class         = Str::ascii(mb_substr($entry->showClass->name, 0, 32));
        $exhibitorName = Str::ascii(mb_substr($exhibitor->sort_name, 0, 32));
        $num           = (string) $entry->entry_number;

        // Section name — double-height bold
        $out .= $GS . '!' . "\x10";
        $out .= $ESC . 'E' . "\x01";
        $out .= $section . $LF;

        // Class name — normal
        $out .= $GS . '!' . "\x00";
        $out .= $ESC . 'E' . "\x00";
        $out .= $class . $LF;

        // Divider and exhibitor name
        $out .= str_repeat('-', 32) . $LF;
        $out .= $exhibitorName . $LF;

        // Entry number — triple size
        $out .= $GS . '!' . "\x22";
        $out .= $num . $LF;
        $out .= $GS . '!' . "\x00";

        // Code128 barcode
        $barData = '{B' . $num;  // {B selects Code Set B
        $out .= $GS . 'h' . chr(80);
        $out .= $GS . 'w' . chr(2);
        $out .= $GS . 'H' . chr(2);
        $out .= $GS . 'k' . chr(73) . chr(strlen($barData)) . $barData;

        // Tear margin and cut
        $out .= $LF . $LF;
        $out .= $ESC . 'd' . chr(3);
        $out .= $GS . 'V' . "\x00";
    }

    return $out;
}
```

### "Download ESC/POS" button on the labels page

Add alongside the existing Print button in `resources/views/admin/exhibitors/labels.blade.php`:

```html
<a href="{{ $escPosUrl }}" class="btn-escpos">Download ESC/POS (.bin)</a>
```

---

## Notes and Assumptions

- **Paper width**: 58 mm (32-character line at default font) assumed. Change `str_repeat('-', 32)` and string truncation lengths to 48 for 80 mm paper.
- **No label size configuration**: Label height is determined by content; each label ends with a cut. There is no equivalent to TSPL's `SIZE` and `GAP` commands.
- **No library needed**: All commands are generated as raw bytes using PHP string functions — no additional dependency required.
- **Alternative library**: `mike42/escpos-php` (`composer require mike42/escpos-php`) provides a higher-level API and adds logo/image printing if needed in future.
- **Non-ASCII characters**: `Str::ascii()` transliterates accented characters. ESC/POS printers use their own code page (usually PC437 or PC850), so pure ASCII is the safest approach.
- **Barcode content**: The `{B` prefix selects Code128 Code Set B, which handles all printable ASCII. For pure digit strings, `{C` (Code Set C) is more compact but requires even-length input.
- **Cutter**: Not all ESC/POS printers have an auto-cutter. If `GS V` is sent to a printer without one, it is silently ignored and the operator tears manually.
- **Inspecting output**: To verify the generated bytes, use `xxd labels.bin` — you should see `1b 40` at the start (ESC @) and `1d 56 00` at the end of each label (GS V full cut).
