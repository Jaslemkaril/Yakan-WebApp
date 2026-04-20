<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post-Order Requests Report – Yakan Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            background: #fff;
            padding: 24px 32px;
        }

        /* ── Header ── */
        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #800000;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .report-brand { display: flex; align-items: center; gap: 10px; }
        .report-brand-name { font-size: 22px; font-weight: 700; color: #800000; }
        .report-title-block { text-align: right; }
        .report-title { font-size: 16px; font-weight: 700; color: #111827; }
        .report-subtitle { font-size: 11px; color: #6b7280; margin-top: 2px; }

        /* ── Filter summary ── */
        .filter-summary {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 16px;
            font-size: 11px;
            color: #374151;
        }
        .filter-summary span { font-weight: 600; color: #800000; }

        /* ── KPI grid ── */
        .kpi-section { margin-bottom: 20px; }
        .kpi-section-title { font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .kpi-row { display: flex; gap: 8px; }
        .kpi-card {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
            background: #fff;
        }
        .kpi-value { font-size: 20px; font-weight: 700; }
        .kpi-label { font-size: 10px; color: #6b7280; margin-top: 2px; }
        .kpi-amber { color: #d97706; }
        .kpi-green { color: #16a34a; }
        .kpi-red   { color: #dc2626; }
        .kpi-blue  { color: #2563eb; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        thead tr { background: #800000; color: #fff; }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:nth-child(even) { background: #fafafa; }
        tbody td { padding: 7px 10px; font-size: 11px; vertical-align: middle; }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
        }
        .badge-cancel  { background: #fef3c7; color: #92400e; }
        .badge-refund  { background: #dbeafe; color: #1e40af; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved, .badge-refunded { background: #dcfce7; color: #166534; }
        .badge-review  { background: #dbeafe; color: #1e40af; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
        }

        /* ── No-print buttons ── */
        .no-print {
            margin-bottom: 16px;
            display: flex;
            gap: 10px;
        }
        .btn-print {
            padding: 8px 20px;
            background: #800000;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-close {
            padding: 8px 20px;
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 10px 16px; }
            @page { margin: 12mm 10mm; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Print / Save as PDF</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<!-- Header -->
<div class="report-header">
    <div class="report-brand">
        <div>
            <div class="report-brand-name">Yakan</div>
            <div style="font-size:10px;color:#6b7280;">E-commerce Platform</div>
        </div>
    </div>
    <div class="report-title-block">
        <div class="report-title">Post-Order Requests Report</div>
        <div class="report-subtitle">Generated: {{ now()->format('F d, Y h:i A') }}</div>
    </div>
</div>

<!-- Filter Summary -->
@php
    $typeLabel = match($typeFilter) {
        'cancel' => 'Cancel Requests',
        'refund' => 'Refund Requests',
        default  => 'All Requests',
    };
    $statusLabel = $statusFilter !== '' ? ucwords(str_replace('_', ' ', $statusFilter)) : 'All Statuses';
    $dateLabel   = ($dateFrom !== '' || $dateTo !== '')
        ? (($dateFrom !== '' ? $dateFrom : '—') . ' → ' . ($dateTo !== '' ? $dateTo : '—'))
        : 'All dates';
@endphp
<div class="filter-summary">
    Type: <span>{{ $typeLabel }}</span> &nbsp;|&nbsp;
    Status: <span>{{ $statusLabel }}</span> &nbsp;|&nbsp;
    Date range: <span>{{ $dateLabel }}</span> &nbsp;|&nbsp;
    Total records: <span>{{ $rows->count() }}</span>
</div>

<!-- KPI Summary -->
<div class="kpi-section">
    <div class="kpi-section-title">Cancel Requests Summary</div>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-value kpi-amber">{{ $stats['cancel']['pending'] }}</div>
            <div class="kpi-label">Pending</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value kpi-green">{{ $stats['cancel']['approved'] }}</div>
            <div class="kpi-label">Approved</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value kpi-red">{{ $stats['cancel']['rejected'] }}</div>
            <div class="kpi-label">Rejected</div>
        </div>
    </div>
</div>

<div class="kpi-section">
    <div class="kpi-section-title">Refund Requests Summary</div>
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-value kpi-blue">{{ $stats['refund']['under_review'] }}</div>
            <div class="kpi-label">Under Review</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value kpi-green">{{ $stats['refund']['refunded'] }}</div>
            <div class="kpi-label">Refunded</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value kpi-red">{{ $stats['refund']['rejected'] }}</div>
            <div class="kpi-label">Rejected</div>
        </div>
    </div>
</div>

<!-- Data Table -->
<table>
    <thead>
        <tr>
            <th style="width:14%">ID</th>
            <th style="width:22%">Customer</th>
            <th style="width:10%">Type</th>
            <th style="width:10%">Order Type</th>
            <th style="width:13%">Amount</th>
            <th style="width:13%">Status</th>
            <th style="width:18%">Date Requested</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            @php
                $typeBadge   = $row['type'] === 'cancel' ? 'badge-cancel' : 'badge-refund';
                $statusBadge = match($row['status_key'] ?? '') {
                    'pending'       => 'badge-pending',
                    'approved'      => 'badge-approved',
                    'refunded'      => 'badge-refunded',
                    'under_review'  => 'badge-review',
                    'rejected'      => 'badge-rejected',
                    default         => '',
                };
            @endphp
            <tr>
                <td style="font-weight:600;">{{ $row['display_id'] }}</td>
                <td>{{ $row['customer'] }}</td>
                <td><span class="badge {{ $typeBadge }}">{{ ucfirst($row['type']) }}</span></td>
                <td>{{ ucfirst($row['order_type']) }}</td>
                <td>&#8369;{{ number_format((float) $row['amount'], 2) }}</td>
                <td><span class="badge {{ $statusBadge }}">{{ $row['status_label'] }}</span></td>
                <td>{{ optional($row['created_at'])->format('M d, Y h:i A') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:20px;color:#6b7280;">No records match the selected filters.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Footer -->
<div class="report-footer">
    <span>Yakan E-commerce Platform &mdash; Admin Report</span>
    <span>Printed on {{ now()->format('F d, Y h:i A') }}</span>
</div>

<script>
    // Auto-trigger print dialog when opened in a new tab
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>
</body>
</html>
