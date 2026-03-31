<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice — {{ $project->name }} — {{ \Carbon\Carbon::parse($week->week_start)->format('M d') }}–{{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: #f4f5f7;
            color: #1a1a2e;
            font-size: 13px;
            line-height: 1.5;
        }

        .page-wrapper {
            max-width: 860px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Print toolbar */
        .print-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #1a1a2e;
            border-radius: 10px;
            color: #fff;
        }
        .print-toolbar span { font-size: 13px; opacity: 0.6; }
        .btn-print {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: #f97316;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #ea6c0a; }

        /* Invoice card */
        .invoice {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        /* Invoice header */
        .invoice-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 36px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }
        .company-sub {
            font-size: 12px;
            color: rgba(255,255,255,0.45);
            margin-top: 4px;
        }
        .invoice-meta { text-align: right; }
        .invoice-label {
            font-size: 28px;
            font-weight: 800;
            color: #f97316;
            letter-spacing: -1px;
        }
        .invoice-number {
            font-size: 12px;
            color: rgba(255,255,255,0.45);
            margin-top: 4px;
        }
        .invoice-dates {
            margin-top: 8px;
            font-size: 12px;
            color: rgba(255,255,255,0.55);
        }
        .invoice-dates strong { color: rgba(255,255,255,0.85); }

        /* Bill to / from */
        .bill-section {
            padding: 28px 40px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
            background: #fafafa;
            border-bottom: 1px solid #e8e8ee;
        }
        .bill-block { flex: 1; }
        .bill-block-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #999;
            margin-bottom: 10px;
        }
        .bill-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
        }
        .bill-detail {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }

        /* Project info bar */
        .project-bar {
            display: flex;
            align-items: center;
            gap: 32px;
            padding: 14px 40px;
            background: #f0f1f8;
            border-bottom: 1px solid #e8e8ee;
        }
        .project-bar-item { }
        .project-bar-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
        }
        .project-bar-value {
            font-size: 13px;
            font-weight: 600;
            color: #1a1a2e;
            margin-top: 1px;
        }

        /* Time log table */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #aaa;
            padding: 20px 40px 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            padding: 10px 16px;
            text-align: left;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            background: #f7f7fb;
            border-top: 1px solid #e8e8ee;
            border-bottom: 1px solid #e8e8ee;
        }
        thead th:first-child { padding-left: 40px; }
        thead th:last-child { padding-right: 40px; text-align: right; }
        thead th.right { text-align: right; }

        tbody tr { border-bottom: 1px solid #f0f0f4; }
        tbody tr:last-child { border-bottom: none; }
        tbody td {
            padding: 11px 16px;
            font-size: 12.5px;
            color: #333;
            vertical-align: middle;
        }
        tbody td:first-child { padding-left: 40px; }
        tbody td:last-child { padding-right: 40px; text-align: right; }
        tbody td.right { text-align: right; }
        tbody td.muted { color: #999; }
        tbody td.task-name { font-weight: 500; color: #1a1a2e; max-width: 220px; }

        /* Member subtotal rows */
        .member-header td {
            padding: 10px 16px;
            background: #f7f7fb;
            font-size: 11px;
            font-weight: 700;
            color: #444;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-top: 2px solid #e2e2ec;
        }
        .member-header td:first-child { padding-left: 40px; }
        .member-header td:last-child { padding-right: 40px; text-align: right; }
        .member-header td.right { text-align: right; }

        .member-subtotal td {
            padding: 10px 16px;
            background: #eef0fb;
            font-size: 12px;
            font-weight: 600;
            color: #333;
            border-top: 1px solid #dde0f0;
        }
        .member-subtotal td:first-child { padding-left: 40px; }
        .member-subtotal td:last-child { padding-right: 40px; text-align: right; color: #f97316; font-weight: 700; }
        .member-subtotal td.right { text-align: right; }

        /* Summary section */
        .invoice-summary {
            padding: 24px 40px;
            display: flex;
            justify-content: flex-end;
            border-top: 2px solid #e2e2ec;
            background: #fafafa;
        }
        .summary-table { min-width: 280px; }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 13px;
            color: #555;
        }
        .summary-row.total {
            border-top: 2px solid #e2e2ec;
            padding-top: 12px;
            margin-top: 8px;
            font-size: 18px;
            font-weight: 800;
            color: #1a1a2e;
        }
        .summary-row.total .amount { color: #f97316; }
        .summary-label { }
        .summary-amount { font-weight: 600; }

        /* Footer */
        .invoice-footer {
            padding: 20px 40px;
            background: #f0f1f8;
            border-top: 1px solid #e2e2ec;
            text-align: center;
            font-size: 11px;
            color: #aaa;
        }

        /* Print styles */
        @media print {
            body { background: #fff; }
            .page-wrapper { margin: 0; padding: 0; max-width: 100%; }
            .print-toolbar { display: none !important; }
            .invoice {
                border-radius: 0;
                box-shadow: none;
            }
            @page { margin: 15mm; size: A4; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    {{-- Print Toolbar --}}
    <div class="print-toolbar">
        <span>Invoice Preview — {{ $project->name }}</span>
        <button class="btn-print" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Download PDF
        </button>
    </div>

    <div class="invoice">

        {{-- Header --}}
        <div class="invoice-header">
            <div>
                <div class="company-name">{{ config('app.name', 'BAI') }}</div>
                @if(auth()->user())
                    <div class="company-sub">{{ auth()->user()->email }}</div>
                @endif
            </div>
            <div class="invoice-meta">
                <div class="invoice-label">INVOICE</div>
                <div class="invoice-number">#INV-{{ str_pad($week->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="invoice-dates">
                    <div>Issue Date: <strong>{{ now()->format('M d, Y') }}</strong></div>
                    <div>Period: <strong>{{ \Carbon\Carbon::parse($week->week_start)->format('M d') }} – {{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}</strong></div>
                </div>
            </div>
        </div>

        {{-- Bill To / From --}}
        <div class="bill-section">
            <div class="bill-block">
                <div class="bill-block-label">Bill From</div>
                <div class="bill-name">{{ config('app.name', 'BAI') }}</div>
                @if(auth()->user())
                    <div class="bill-detail">{{ auth()->user()->name }}</div>
                    <div class="bill-detail">{{ auth()->user()->email }}</div>
                @endif
            </div>
            <div class="bill-block">
                <div class="bill-block-label">Bill To</div>
                @if($project->client)
                    <div class="bill-name">{{ $project->client->name }}</div>
                    @if($project->client->company)
                        <div class="bill-detail">{{ $project->client->company }}</div>
                    @endif
                    @if($project->client->email)
                        <div class="bill-detail">{{ $project->client->email }}</div>
                    @endif
                    @if($project->client->phone)
                        <div class="bill-detail">{{ $project->client->phone }}</div>
                    @endif
                @else
                    <div class="bill-name" style="color:#bbb;">No client assigned</div>
                @endif
            </div>
        </div>

        {{-- Project bar --}}
        <div class="project-bar">
            <div class="project-bar-item">
                <div class="project-bar-label">Project</div>
                <div class="project-bar-value">{{ $project->name }}</div>
            </div>
            <div class="project-bar-item">
                <div class="project-bar-label">Billing Period</div>
                <div class="project-bar-value">{{ \Carbon\Carbon::parse($week->week_start)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}</div>
            </div>
            <div class="project-bar-item">
                <div class="project-bar-label">Hourly Rate</div>
                <div class="project-bar-value">${{ number_format($project->hourly_rate ?? 0, 2) }}/hr</div>
            </div>
            <div class="project-bar-item">
                <div class="project-bar-label">Total Hours</div>
                <div class="project-bar-value">{{ number_format($week->total_billable_hours, 1) }} hrs</div>
            </div>
        </div>

        {{-- Time log table --}}
        <div class="section-title">Time Log Details</div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Task</th>
                    <th class="right">Hours Logged</th>
                    <th class="right">Notes</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($week->entries as $entry)
                    @php
                        $userLogs = $logsByUser->get($entry->user_id, collect());
                        $memberActual   = $userLogs->sum('hours');
                        $memberBillable = floatval($entry->billable_hours);
                        $memberAmount   = $memberBillable * floatval($project->hourly_rate ?? 0);
                    @endphp

                    {{-- Member header --}}
                    <tr class="member-header">
                        <td colspan="4" style="display:flex;align-items:center;gap:8px;">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#f97316;color:#fff;font-size:10px;font-weight:700;">
                                {{ strtoupper(substr($entry->user->name ?? '?', 0, 2)) }}
                            </span>
                            {{ $entry->user->name ?? 'Unknown' }}
                        </td>
                        <td class="right">Subtotal</td>
                    </tr>

                    {{-- Individual log rows --}}
                    @forelse($userLogs as $log)
                        <tr>
                            <td class="muted">{{ \Carbon\Carbon::parse($log->logged_at)->format('M d') }}</td>
                            <td class="task-name">{{ $log->task->title ?? '—' }}</td>
                            <td class="right">{{ number_format($log->hours, 1) }} h</td>
                            <td class="right muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $log->notes ?: '—' }}</td>
                            <td class="right muted">—</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="muted" style="padding-left:40px;">No time logs for this member this period.</td>
                        </tr>
                    @endforelse

                    {{-- Member subtotal --}}
                    <tr class="member-subtotal">
                        <td colspan="2">
                            Logged: {{ number_format($memberActual, 1) }} h
                            @if($memberBillable != $memberActual)
                                <span style="color:#f97316;margin-left:8px;">→ Billed: {{ number_format($memberBillable, 1) }} h</span>
                            @endif
                            @if($entry->notes)
                                <span style="color:#888;font-size:11px;font-weight:400;margin-left:8px;">· {{ $entry->notes }}</span>
                            @endif
                        </td>
                        <td class="right">{{ number_format($memberBillable, 1) }} h</td>
                        <td class="right">@ ${{ number_format($project->hourly_rate ?? 0, 2) }}/hr</td>
                        <td class="right">${{ number_format($memberAmount, 2) }}</td>
                    </tr>

                @endforeach
            </tbody>
        </table>

        {{-- Summary --}}
        <div class="invoice-summary">
            <div class="summary-table">
                <div class="summary-row">
                    <span class="summary-label">Total Hours Logged</span>
                    <span class="summary-amount">{{ number_format($week->total_actual_hours, 1) }} hrs</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total Billable Hours</span>
                    <span class="summary-amount">{{ number_format($week->total_billable_hours, 1) }} hrs</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Rate</span>
                    <span class="summary-amount">${{ number_format($project->hourly_rate ?? 0, 2) }}/hr</span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">Total Due</span>
                    <span class="amount">${{ number_format($week->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="invoice-footer">
            Generated by {{ config('app.name') }} · {{ now()->format('M d, Y \a\t h:i A') }}
            @if($week->locker)
                · Locked by {{ $week->locker->name }}
            @endif
        </div>

    </div>{{-- /.invoice --}}

</div>{{-- /.page-wrapper --}}

</body>
</html>
