<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entry Labels — {{ $exhibitor->full_name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: 2in 1in; margin: 1.5mm; }

        .label {
            padding: 1mm 2mm;
            display: flex;
            flex-direction: column;
            justify-content: center; /* centers vertically */
            /* align-items: center;
            text-align: center; */
            gap: 2mm;
            font-family: ui-sans-serif, system-ui, sans-serif;
            box-sizing: border-box;
        }

        .label-section {
            font-size: 6px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #888;
            width: 100%;
        }

        .label-class {
            font-size: 10px;
            font-weight: 500;
            line-height: 1.2;
            text-align: center;
        }

        .label-divider {
            width: 100%;
            border-top: 1px solid #ddd;
        }

        .label-exhibitor {
            font-size: 7px;
            color: #777;
            text-align: right;
        }

        .label-entry {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            text-align: center;
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

            .labels-list {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
                padding: 20px;
            }

            .label {
                width: 2in;
                height: 1in;
                border: 1px dashed #bbb;
                background: white;
            }
        }

        @media print {
            .screen-header { display: none; }
            .label {
                width: 100%;
                border: none;
                background: none;
                break-after: page;
                page-break-after: always;
            }
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

    @if ($entries->isEmpty())
        <p style="text-align:center; color:#6b7280; padding: 40px 0;">No entries to print.</p>
    @else
        <div class="labels-list">
            @foreach ($entries as $entry)
                <div class="label">
                    {{-- <div class="label-section">{{ $entry->showClass->showSection->name }}</div> --}}
                    <div class="label-class">{{ $entry->showClass->id }}. {{ Str::limit($entry->showClass->name, 30) }}</div>
                    <hr class="label-divider">
                    <div class="label-entry">{{ $entry->formatted_entry_number }}</div>
                    <div class="label-exhibitor">Exhibitor: {{ $exhibitor->id }}</div>
                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
