<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paper Backup{{ $selectedUser ? ' — ' . $selectedUser->name : '' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4 portrait; margin: 12mm; }

        * { box-sizing: border-box; }

        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        .section-block {
            break-after: page;
            page-break-after: always;
        }

        .section-block:last-child {
            break-after: avoid;
            page-break-after: avoid;
        }

        .section-heading {
            font-size: 16px;
            font-weight: 700;
            border-bottom: 2px solid #111827;
            padding-bottom: 3mm;
            margin-bottom: 2mm;
        }

        .section-meta {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 5mm;
        }

        .class-block {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 2.5mm;
        }

        .class-heading {
            font-size: 11px;
            font-weight: 700;
            background: #f3f4f6;
            padding: 1mm 2mm;
            border: 1px solid #d1d5db;
            border-bottom: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            padding: 0.5mm 2mm;
            text-align: left;
            font-weight: 600;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 0.5mm 2mm;
            vertical-align: middle;
        }

        .col-entry  { width: 12mm; }
        .col-check  { width: 14mm; text-align: center; }
        .cb-cell    { text-align: center; font-size: 16px; line-height: 1.2; }

        @media screen {
            body { background: #f3f4f6; margin: 0; }

            .screen-header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 12px 20px;
                display: flex;
                align-items: center;
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
                flex: 1;
            }

            .screen-header-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }

            select {
                padding: 7px 10px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 14px;
                color: #111827;
                background: white;
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
            .btn-print:disabled { background: #a5b4fc; cursor: default; }

            .print-content {
                padding: 20px;
                max-width: 210mm;
                margin: 0 auto;
            }

            .section-block {
                background: white;
                padding: 12px 16px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 16px;
            }

            .empty-state {
                text-align: center;
                color: #6b7280;
                padding: 60px 0;
                font-size: 14px;
            }
        }

        @media print {
            .screen-header { display: none; }
            .print-content { padding: 0; }

            .section-block {
                background: none;
                border: none;
                border-radius: 0;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="screen-header">
    <a href="{{ route('admin.show-sections.index') }}">← Back</a>

    <h1>Paper Backup{{ $selectedUser ? ' — ' . $selectedUser->name : '' }}</h1>

    <div class="screen-header-actions">
        <form method="GET" action="{{ route('admin.paper-backup') }}" style="display:contents">
            <select name="user_id" onchange="this.form.submit()">
                <option value="">Select judge or steward…</option>
                @foreach ($personnel as $label => $group)
                    <optgroup label="{{ $label }}">
                        @foreach ($group as $person)
                            <option value="{{ $person->id }}"
                                {{ $selectedUser?->id === $person->id ? 'selected' : '' }}>
                                {{ $person->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </form>

        <button class="btn-print"
                onclick="window.print()"
                {{ $selectedUser ? '' : 'disabled' }}>
            Print
        </button>
    </div>
</div>

<div class="print-content">
    @if (! $selectedUser)
        <p class="empty-state">Select a judge or steward above to generate their paper backup report.</p>
    @elseif ($sections->isEmpty() && $trophies->isEmpty())
        <p class="empty-state">{{ $selectedUser->name }} has no sections or trophies assigned.</p>
    @else
        @foreach ($sections as $section)
            <div class="section-block">
                <div class="section-heading">{{ $section->name }}</div>
                @php $classesWithEntries = $section->showClasses->filter(fn ($c) => $c->entries->isNotEmpty()); @endphp
                <div class="section-meta">
                    {{ $classesWithEntries->count() }} {{ Str::plural('class', $classesWithEntries->count()) }}
                    &bull;
                    {{ $classesWithEntries->sum(fn ($c) => $c->entries->count()) }} {{ Str::plural('entry', $classesWithEntries->sum(fn ($c) => $c->entries->count())) }}
                </div>

                @foreach ($section->showClasses->filter(fn ($c) => $c->entries->isNotEmpty()) as $class)
                    <div class="class-block">
                        <div class="class-heading">
                            {{ $class->name }}
                            <span style="font-weight:400;color:#6b7280">({{ $class->entries->count() }} {{ Str::plural('entry', $class->entries->count()) }})</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-entry">Entry</th>
                                    <th class="col-check">1st</th>
                                    <th class="col-check">2nd</th>
                                    <th class="col-check">3rd</th>
                                    <th class="col-check">HC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($class->entries as $entry)
                                    <tr>
                                        <td class="col-entry" style="font-variant-numeric:tabular-nums">{{ $entry->formatted_entry_number }}</td>
                                        <td class="cb-cell">&#9744;</td>
                                        <td class="cb-cell">&#9744;</td>
                                        <td class="cb-cell">&#9744;</td>
                                        <td class="cb-cell">&#9744;</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endforeach

        @if ($trophies->isNotEmpty())
            <div class="section-block">
                <div class="section-heading">Trophies</div>
                <div style="display:flex;flex-wrap:wrap;gap:6mm;">
                    @foreach ($trophies as $trophy)
                        <div style="break-inside:avoid;page-break-inside:avoid;min-width:60mm;max-width:90mm;">
                            <div style="font-weight:700;font-size:11px;">{{ $trophy->name }}</div>
                            @if ($trophy->description)
                                <div style="font-size:10px;color:#6b7280;margin-bottom:2mm;">{{ $trophy->description }}</div>
                            @else
                                <div style="margin-bottom:2mm;"></div>
                            @endif
                            <div style="border:1px solid #d1d5db;width:30mm;height:10mm;"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

</body>
</html>
