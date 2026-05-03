# TSPL Label Printing

## Overview

This document describes a planned feature to generate TSPL (TSC Printer Standard Command Language) files from the entry labels page, enabling direct printing to a thermal label printer without the browser print dialog.

---

## What TSPL Is and How It Works

TSPL is a plain-text command language interpreted directly by TSC thermal printers (and many compatible brands — Bixolon, Argox, etc.). A `.prn` file is just a text file containing TSPL commands. No special driver is needed; the printer receives the bytes and executes them directly.

**A minimal label looks like:**
```
SIZE 60 mm, 40 mm        ; label dimensions (width x height)
GAP 3 mm, 0 mm           ; gap between labels on the roll
DIRECTION 0,0            ; feed direction
SET TEAR ON

CLS                      ; clear image buffer -- must precede each label
TEXT 10,5,"2",0,1,1,"FLOWERS"
TEXT 10,25,"3",0,1,1,"Best Rose"
BAR 10,52,380,2          ; horizontal rule (x, y, width, height in dots)
TEXT 10,60,"2",0,1,1,"Smith, Alice"
TEXT 10,85,"4",0,2,2,"42"
BARCODE 10,155,"128",50,0,0,2,2,"42"
PRINT 1,1                ; print 1 label, 1 copy
```

**Key commands:**

| Command | Syntax | Notes |
|---|---|---|
| `SIZE` | `SIZE w mm, h mm` | Label roll width x height |
| `GAP` | `GAP n mm, 0 mm` | Inter-label gap (check your roll) |
| `CLS` | `CLS` | Clears buffer — one per label |
| `TEXT` | `TEXT x, y, "font", rot, xmult, ymult, "data"` | Built-in fonts "1"–"5"; rot=0/90/180/270 |
| `BAR` | `BAR x, y, width, height` | Filled rectangle — used for dividers |
| `BARCODE` | `BARCODE x, y, "128", height, readable, rot, narrow, wide, "data"` | "128" = Code128; readable=0 hides digits |
| `PRINT` | `PRINT n, copies` | n labels, each printed copies times |

**Coordinate system:** dots. Most TSC printers are **203 DPI** (~8 dots/mm). A 60 mm wide label is ~480 dots wide. Some printers are 300 DPI — the `SIZE` and `GAP` commands always use mm so they are DPI-agnostic, but `TEXT`/`BAR`/`BARCODE` x/y positions are in dots and would need scaling by ×1.5 for 300 DPI.

**Multiple labels:** Each label needs its own `CLS` → content → `PRINT 1,1` block. The header (`SIZE`, `GAP`, `DIRECTION`, `SET TEAR ON`) appears once at the top of the file.

---

## How to Send the File to the Printer

The app will generate and download a `labels.prn` file. The admin then sends it directly to the printer — no print dialog involved.

**Windows:**
```bat
copy /b labels.prn \\SERVER\PRINTERSHARE
```
Or drag the `.prn` file onto the printer in **Settings → Printers & scanners → Open print queue → Printer → Add document**.

**macOS / Linux:**
```bash
lpr -l -P "PrinterName" labels.prn
```
(`-l` = raw pass-through, bypasses any filter that would reinterpret the file)

No printer driver is needed for raw TSPL — only a raw TCP/IP or USB port connection to the printer.

---

## Planned Implementation

### New route (`routes/web.php`)

```php
Route::get('exhibitors/{exhibitor}/labels/tspl', [ExhibitorController::class, 'labelsAsTspl'])
    ->name('exhibitors.labels-tspl');
```

### New controller method (`ExhibitorController`)

```php
public function labelsAsTspl(Request $request, Exhibitor $exhibitor): Response
{
    $entryIds = array_filter((array) $request->query('entries', []));

    $entries = $exhibitor->entries()
        ->with(['showClass.showSection'])
        ->when(! empty($entryIds), fn ($q) => $q->whereIn('id', $entryIds))
        ->orderBy('entry_number')
        ->get();

    $tspl = $this->buildTspl($exhibitor, $entries);

    return response($tspl)
        ->header('Content-Type', 'text/plain')
        ->header('Content-Disposition', 'attachment; filename="labels.prn"');
}

private function buildTspl(Exhibitor $exhibitor, \Illuminate\Support\Collection $entries): string
{
    $lines = [];
    $lines[] = 'SIZE 60 mm, 40 mm';
    $lines[] = 'GAP 3 mm, 0 mm';
    $lines[] = 'DIRECTION 0,0';
    $lines[] = 'SET TEAR ON';

    foreach ($entries as $entry) {
        $section       = mb_strtoupper(mb_substr($entry->showClass->showSection->name, 0, 26));
        $class         = mb_substr($entry->showClass->name, 0, 26);
        $exhibitorName = mb_substr($exhibitor->sort_name, 0, 26);
        $num           = (string) $entry->entry_number;

        $lines[] = '';
        $lines[] = 'CLS';
        $lines[] = "TEXT 10,5,\"2\",0,1,1,\"{$section}\"";
        $lines[] = "TEXT 10,25,\"3\",0,1,1,\"{$class}\"";
        $lines[] = 'BAR 10,52,380,2';
        $lines[] = "TEXT 10,60,\"2\",0,1,1,\"{$exhibitorName}\"";
        $lines[] = "TEXT 10,85,\"4\",0,2,2,\"{$num}\"";
        $lines[] = "BARCODE 10,155,\"128\",50,0,0,2,2,\"{$num}\"";
        $lines[] = 'PRINT 1,1';
    }

    return implode("\r\n", $lines);  // CRLF for Windows printer compatibility
}
```

### "Download TSPL" button on the labels page

Add alongside the existing Print button in `resources/views/admin/exhibitors/labels.blade.php`:

```html
<a href="{{ $tsplUrl }}" class="btn-tspl">Download TSPL (.prn)</a>
```

---

## Notes and Assumptions

- **Label size**: 60 mm × 40 mm with 3 mm gap — adjust to match the roll loaded in the printer.
- **DPI**: 203 DPI assumed. Dot positions need scaling by ×1.5 for a 300 DPI printer.
- **String truncation**: Names truncated at 26 characters to prevent overflow (font "2" = 12 dots/char × 26 ≈ 312 dots, fits within ~480-dot label width with margins).
- **Line endings**: `\r\n` (CRLF) for Windows/printer compatibility.
- **Non-ASCII characters**: TSPL TEXT does not support UTF-8 by default. Accented characters should be transliterated with `Str::ascii()` before embedding.
