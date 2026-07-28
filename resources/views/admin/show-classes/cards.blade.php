<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Class Cards</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: 148mm 105mm landscape; margin: 0; }

        * { box-sizing: border-box; }

        .card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-family: ui-sans-serif, system-ui, sans-serif;
            padding: 6mm 26mm;
            gap: 4mm;
            position: relative;
            z-index: 0;
        }

        .card-border {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .card-id {
            font-size: 52px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            color: #111827;
        }

        .card-name {
            font-size: 32px;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
            max-width: 90mm;
        }

        .card-divider {
            width: 100%;
            border-top: 2px solid #d1d5db;
            margin: 0;
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

            .cards-list {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                padding: 20px;
            }

            .card {
                width: 148mm;
                height: 105mm;
                border: 1px dashed #bbb;
                background: white;
            }
        }

        @media print {
            .screen-header { display: none; }
            .card {
                width: 148mm;
                height: 105mm;
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
        <a href="{{ route('admin.show-sections.index') }}">← Show Sections</a>
        <h1>Class Cards — {{ $classes->count() }} {{ Str::plural('Class', $classes->count()) }}</h1>
        <button class="btn-print" onclick="window.print()">Print</button>
    </div>

    @if ($classes->isEmpty())
        <p style="text-align:center; color:#6b7280; padding: 40px 0;">No classes to print.</p>
    @else
        <div class="cards-list">
            @foreach ($classes as $class)
                <div class="card">
                    <img src="{{ asset('images/Class-card-border.png') }}" alt="" class="card-border">
                    <div class="card-id">Class {{ $class->id }}</div>
                    <hr class="card-divider">
                    <div class="card-name">{{ $class->name }}</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
