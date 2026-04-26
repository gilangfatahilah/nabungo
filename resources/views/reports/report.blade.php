<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.5; }

        .header { background: #1e293b; color: white; padding: 24px 32px; margin-bottom: 24px; }
        .header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .header p  { font-size: 12px; opacity: 0.75; }

        .section { margin-bottom: 24px; padding: 0 32px; }
        .section-title { font-size: 13px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px; }

        /* Summary cards */
        .cards { display: flex; gap: 12px; flex-wrap: wrap; }
        .card { flex: 1; min-width: 120px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; }
        .card-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .card-value { font-size: 15px; font-weight: 700; color: #1e293b; }
        .card-value.positive { color: #16a34a; }
        .card-value.negative { color: #dc2626; }
        .card-value.neutral  { color: #2563eb; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        thead tr { background: #1e293b; color: white; }
        thead th { padding: 7px 10px; text-align: left; font-weight: 600; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; text-transform: uppercase; }
        .badge-ok      { background: #dcfce7; color: #16a34a; }
        .badge-warning { background: #fef9c3; color: #b45309; }
        .badge-over    { background: #fee2e2; color: #dc2626; }
        .badge-income  { background: #dcfce7; color: #16a34a; }
        .badge-expense { background: #fee2e2; color: #dc2626; }

        .progress-bar-bg { background: #e2e8f0; border-radius: 4px; height: 8px; width: 100%; }
        .progress-bar    { height: 8px; border-radius: 4px; }
        .progress-ok      { background: #22c55e; }
        .progress-warning { background: #f59e0b; }
        .progress-over    { background: #ef4444; }

        .footer { text-align: center; font-size: 9px; color: #94a3b8; padding: 16px 32px; border-top: 1px solid #e2e8f0; margin-top: 16px; }
    </style>
</head>
<body>

<div class="header">
    <h1>Financial Report</h1>
    <p>Period: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, H:i') }}</p>
</div>

{{-- Summary Cards --}}
<div class="section">
    <div class="section-title">Summary</div>
    <div class="cards">
        <div class="card">
            <div class="card-label">Total Income</div>
            <div class="card-value positive">{{ number_format($summary['income']['value'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Total Expense</div>
            <div class="card-value negative">{{ number_format($summary['expense']['value'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Net Savings</div>
            @php $netVal = $summary['net']['value']; @endphp
            <div class="card-value {{ $netVal >= 0 ? 'positive' : 'negative' }}">{{ number_format($netVal, 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Savings Rate</div>
            <div class="card-value neutral">{{ $summary['savingsRate']['value'] }}%</div>
        </div>
    </div>
</div>

{{-- Category Breakdown --}}
@if(!empty($categoryBreakdown))
<div class="section">
    <div class="section-title">Spending by Category</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align:right">Amount (IDR)</th>
                <th style="text-align:right">% of Expense</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categoryBreakdown as $cat)
            <tr>
                <td>{{ $cat['category_name'] }}</td>
                <td style="text-align:right">{{ number_format($cat['total'], 0, ',', '.') }}</td>
                <td style="text-align:right">{{ $cat['percentage'] }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Budget vs Actual --}}
@if(!empty($budgetVsActual))
<div class="section">
    <div class="section-title">Budget vs Actual</div>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align:right">Budgeted</th>
                <th style="text-align:right">Actual</th>
                <th style="text-align:right">Remaining</th>
                <th style="text-align:center">Usage</th>
                <th style="text-align:center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgetVsActual as $b)
            <tr>
                <td>{{ $b['category_name'] }}</td>
                <td style="text-align:right">{{ number_format($b['budgeted'], 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($b['actual'], 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($b['remaining'], 0, ',', '.') }}</td>
                <td style="text-align:center">{{ $b['usage'] }}%</td>
                <td style="text-align:center">
                    <span class="badge badge-{{ $b['status'] }}">{{ strtoupper($b['status']) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Top Transactions --}}
@if(!empty($topTransactions['income']) || !empty($topTransactions['expense']))
<div class="section">
    <div class="section-title">Top Transactions</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Category</th>
                <th>Account</th>
                <th style="text-align:right">Amount (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_merge($topTransactions['income'], $topTransactions['expense']) as $tx)
            <tr>
                <td>{{ $tx['transaction_date'] }}</td>
                <td><span class="badge badge-{{ $tx['type'] }}">{{ strtoupper($tx['type']) }}</span></td>
                <td>{{ $tx['description'] ?? '-' }}</td>
                <td>{{ $tx['category'] ?? '-' }}</td>
                <td>{{ $tx['account'] ?? '-' }}</td>
                <td style="text-align:right">{{ number_format($tx['amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Goals --}}
@if(!empty($goalSnapshot))
<div class="section">
    <div class="section-title">Goal Progress</div>
    <table>
        <thead>
            <tr>
                <th>Goal</th>
                <th style="text-align:right">Target</th>
                <th style="text-align:right">Saved</th>
                <th style="text-align:right">Remaining</th>
                <th style="text-align:center">Progress</th>
                <th>Due Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goalSnapshot as $g)
            <tr>
                <td>{{ $g['title'] }}</td>
                <td style="text-align:right">{{ number_format($g['target_amount'], 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($g['saved_amount'], 0, ',', '.') }}</td>
                <td style="text-align:right">{{ number_format($g['remaining'], 0, ',', '.') }}</td>
                <td style="text-align:center">{{ $g['progress'] }}%</td>
                <td>{{ $g['due_date'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Debt Summary --}}
@if($debtSummary['debt']['total'] > 0 || $debtSummary['receivable']['total'] > 0)
<div class="section">
    <div class="section-title">Debt Summary</div>
    <div class="cards">
        <div class="card">
            <div class="card-label">Total Debt (I Owe)</div>
            <div class="card-value negative">{{ number_format($debtSummary['debt']['remaining'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Total Receivable</div>
            <div class="card-value positive">{{ number_format($debtSummary['receivable']['remaining'], 0, ',', '.') }}</div>
        </div>
        @if(!empty($debtSummary['overdue']))
        <div class="card">
            <div class="card-label">Overdue Items</div>
            <div class="card-value negative">{{ count($debtSummary['overdue']) }}</div>
        </div>
        @endif
    </div>
</div>
@endif

<div class="footer">
    Nabungo – Personal Finance Management &nbsp;|&nbsp; Report generated on {{ now()->format('d M Y H:i:s') }}
</div>

</body>
</html>
