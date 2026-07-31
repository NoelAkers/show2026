<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Result Cards</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: 150mm 100mm landscape; margin: 0; }

        * { box-sizing: border-box; }

        .card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            font-family: ui-sans-serif, system-ui, sans-serif;
            padding: 5mm 4mm;
            position: relative;
        }

        .card-icon {
            position: absolute;
            top: 4mm;
            left: 4mm;
            width: 16mm;
            height: 16mm;
        }

        .card-show-title {
            font-size: 24px;
            font-weight: 700;
            color: black;
            line-height: 1.2;
            margin-top: 30px;
        }

        .card-result {
            font-size: 60px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.02em;
            max-width: 126mm;
        }

        .card-class {
            font-size: 28px;
            font-weight: 700;
            font-style: italic;
            color: black;
            line-height: 1.3;
            max-width: 126mm;
        }

        .card-winner {
            font-size: 36px;
            font-weight: 700;
            color: black;
            line-height: 1.2;
            max-width: 126mm;
        }

        .card-entry-info {
            font-size: 12px;
            font-weight: 700;
            color: black;
            letter-spacing: 0.02em;
            text-align: right;
            width: 100%;
            margin-right: 20mm;
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

            .screen-header-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }

            .btn {
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                white-space: nowrap;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
            }

            .btn-primary { background: #4f46e5; color: white; }
            .btn-primary:hover { background: #4338ca; }

            .btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
            .btn-secondary:hover { background: #e5e7eb; }

            .filter-link {
                font-size: 13px;
                color: #6b7280;
                text-decoration: none;
                padding: 6px 12px;
                border-radius: 5px;
                border: 1px solid transparent;
                white-space: nowrap;
            }

            .filter-link:hover { background: #f3f4f6; }
            .filter-link.active { background: #ede9fe; color: #4f46e5; border-color: #c4b5fd; }

            .cards-list {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                padding: 20px;
            }

            .card {
                width: 150mm;
                height: 100mm;
                border: 1px dashed #bbb;
                background: white;
            }

            .reprint-badge {
                position: absolute;
                top: 6px;
                right: 8px;
                font-size: 10px;
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fde68a;
                padding: 2px 6px;
                border-radius: 4px;
                font-weight: 600;
            }
        }

        @media print {
            .screen-header { display: none; }
            .reprint-badge { display: none; }
            .card {
                width: 150mm;
                height: 100mm;
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
        <h1>
            Result Cards —
            @if ($filter === 'unprinted')
                {{ $unprintedCount }} unprinted
            @else
                {{ $allCount }} total
            @endif
        </h1>
        <div class="screen-header-actions">
            <a href="{{ route('admin.result-cards', ['filter' => 'unprinted']) }}"
               class="filter-link {{ $filter === 'unprinted' ? 'active' : '' }}">
                Unprinted ({{ $unprintedCount }})
            </a>
            <a href="{{ route('admin.result-cards', ['filter' => 'all']) }}"
               class="filter-link {{ $filter === 'all' ? 'active' : '' }}">
                All ({{ $allCount }})
            </a>
            <button class="btn btn-secondary" onclick="window.print()">Print</button>
            @if ($results->isNotEmpty())
                <form method="POST" action="{{ route('admin.result-cards.mark-printed') }}" style="display:contents">
                    @csrf
                    @foreach ($results as $result)
                        <input type="hidden" name="result_ids[]" value="{{ $result->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-primary">Mark as Printed</button>
                </form>
            @endif
        </div>
    </div>

    @if ($results->isEmpty())
        <p style="text-align:center; color:#6b7280; padding: 40px 0;">
            @if ($filter === 'unprinted')
                All result cards have been printed.
                <a href="{{ route('admin.result-cards', ['filter' => 'all']) }}" style="color:#4f46e5;">View all</a>
            @else
                No results to print yet.
            @endif
        </p>
    @else
        <div class="cards-list">
            @foreach ($results as $result)
                <div class="card">
                    @if ($result->prizeIcon())
                        <img src="{{ asset($result->prizeIcon()) }}" alt="{{ $result->prizeLabel() }}" class="card-icon">
                    @endif
                    @if ($result->needsReprint()) --}}
                        <div class="reprint-badge">Modified since last print</div>
                    @endif
                    <div class="card-show-title">{{ $title }}</div>
                    <div class="card-class">Class {{ $result->entry->showClass->id }} &ndash; {{ $result->entry->showClass->name }}</div>
                    <div class="card-result" style="color: {{ $result->prizeColour() }}">{{ $result->prizeLabel() }}</div>
                    <div class="card-winner">{{ $result->entry->exhibitor->full_name }}</div>
                    <div class="card-entry-info">Entry {{ $result->entry->formatted_entry_number }} </div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
