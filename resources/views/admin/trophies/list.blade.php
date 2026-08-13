<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trophy List</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4 portrait; margin: 12mm; }

        * { box-sizing: border-box; }

        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 12px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 2mm 3mm;
            text-align: left;
            font-weight: 600;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 2mm 3mm;
            vertical-align: top;
        }

        .trophy-name { font-weight: 700; }
        .trophy-description { color: #6b7280; }
        .no-winner { color: #9ca3af; }

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

            .print-content {
                padding: 20px;
                max-width: 210mm;
                margin: 0 auto;
                background: white;
            }
        }

        @media print {
            .screen-header { display: none; }
            .print-content { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="screen-header">
        <a href="{{ route('admin.leaderboard') }}">← Leaderboard</a>
        <h1>Trophy List</h1>
        <button class="btn-print" onclick="window.print()">Print</button>
    </div>

    <div class="print-content">
        @if ($trophies->isEmpty())
            <p style="text-align:center; color:#6b7280; padding: 40px 0;">No trophies yet.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Trophy</th>
                        <th>Current Leader(s)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($trophies as $trophy)
                        <tr>
                            <td>
                                <div class="trophy-name">{{ $trophy->name }}</div>
                                @if ($trophy->description)
                                    <div class="trophy-description">{{ $trophy->description }}</div>
                                @endif
                            </td>
                             <td>
                                @if ($trophy->is_points_based)
                                    @php $winners = $trophy->winners(); @endphp
                                    @if ($winners->isEmpty())
                                        <span class="no-winner">No winner yet</span>
                                    @else
                                        {{ $winners->pluck('exhibitor')->pluck('full_name')->join(', ') }}
                                    @endif
                                @else
                                    @if ($trophy->winningEntry)
                                        {{ $trophy->winningEntry->exhibitor->full_name }}
                                    @else
                                        <span class="no-winner">Not yet awarded</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>
</html>
