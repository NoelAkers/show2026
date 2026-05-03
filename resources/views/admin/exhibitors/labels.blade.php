<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entry Labels — {{ $exhibitor->full_name }}</title>
    @vite(['resources/css/app.css'])
    <script src="{{ asset('vendor/jsbarcode.min.js') }}"></script>
    <style>
        @page { margin: 10mm; }

        .label-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4mm;
        }

        .label {
            border: 1px dashed #bbb;
            padding: 3mm 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1.5mm;
            font-family: ui-sans-serif, system-ui, sans-serif;
        }

        .label-section {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #888;
            width: 100%;
        }

        .label-class {
            font-size: 11px;
            font-weight: 700;
            line-height: 1.2;
        }

        .label-divider {
            width: 100%;
            border-top: 1px solid #ddd;
        }

        .label-exhibitor {
            font-size: 9px;
            color: #555;
        }

        .label-entry {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }

        .label-barcode svg {
            display: block;
            width: 100%;
            max-width: 58mm;
            height: auto;
        }

        @media screen {
            body { background: #f3f4f6; margin: 0; }

            .screen-header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 12px 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                position: sticky;
                top: 0;
                z-index: 10;
            }

            .screen-header a {
                color: #4f46e5;
                text-decoration: none;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 4px;
                white-space: nowrap;
            }

            .screen-header a:hover { text-decoration: underline; }

            .screen-header h1 {
                font-size: 15px;
                font-weight: 600;
                color: #111827;
                margin: 0;
            }

            .btn-print {
                background: #4f46e5;
                color: white;
                border: none;
                padding: 8px 20px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                white-space: nowrap;
            }

            .btn-print:hover { background: #4338ca; }

            .screen-body { padding: 20px; }

            .label-grid { background: white; padding: 16px; border-radius: 8px; }
        }

        @media print {
            .screen-header { display: none; }
            .screen-body { padding: 0; }
            .label-grid { background: none; padding: 0; border-radius: 0; }
            .label { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="screen-header">
        <a href="{{ route('admin.exhibitors.add-entry', $exhibitor) }}">
            ← Add Entries
        </a>
        <h1>{{ $entries->count() }} {{ Str::plural('Label', $entries->count()) }} — {{ $exhibitor->full_name }}</h1>
        <button class="btn-print" onclick="window.print()">Print</button>
    </div>

    <div class="screen-body">
        @if ($entries->isEmpty())
            <p style="text-align:center; color:#6b7280; padding: 40px 0;">No entries to print.</p>
        @else
            <div class="label-grid">
                @foreach ($entries as $entry)
                    <div class="label">
                        <div class="label-section">{{ $entry->showClass->showSection->name }}</div>
                        <div class="label-class">{{ $entry->showClass->name }}</div>
                        <hr class="label-divider">
                        <div class="label-exhibitor">{{ $exhibitor->sort_name }}</div>
                        <div class="label-entry">{{ $entry->entry_number }}</div>
                        <div class="label-barcode" data-value="{{ $entry->entry_number }}"></div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('.label-barcode').forEach(function (el) {
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            el.appendChild(svg);
            JsBarcode(svg, el.dataset.value, {
                format: 'CODE128',
                width: 1.5,
                height: 36,
                displayValue: false,
                margin: 0,
            });
        });
    </script>
</body>
</html>
