<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    /**
     * Parse and validate date range from request, returning Carbon instances.
     *
     * @return array{from: Carbon, to: Carbon, preset: string}
     */
    private function parsePeriod(Request $request): array
    {
        $preset = $request->get('preset', 'this_month');
        $now    = Carbon::now();

        switch ($preset) {
            case 'last_month':
                $from = $now->copy()->subMonth()->startOfMonth();
                $to   = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $from = $now->copy()->subMonths(2)->startOfMonth();
                $to   = $now->copy()->endOfMonth();
                break;
            case 'last_6_months':
                $from = $now->copy()->subMonths(5)->startOfMonth();
                $to   = $now->copy()->endOfMonth();
                break;
            case 'this_year':
                $from = $now->copy()->startOfYear();
                $to   = $now->copy()->endOfYear();
                break;
            case 'last_year':
                $from = $now->copy()->subYear()->startOfYear();
                $to   = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                $from = $request->filled('from')
                    ? Carbon::parse($request->get('from'))->startOfDay()
                    : $now->copy()->startOfMonth();
                $to = $request->filled('to')
                    ? Carbon::parse($request->get('to'))->endOfDay()
                    : $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $preset = 'this_month';
                $from   = $now->copy()->startOfMonth();
                $to     = $now->copy()->endOfMonth();
                break;
        }

        return compact('from', 'to', 'preset');
    }

    /**
     * Display the report page.
     */
    public function index(Request $request)
    {
        $userId   = Auth::id();
        $period   = $this->parsePeriod($request);
        $from     = $period['from'];
        $to       = $period['to'];

        $cashFlowGroup = $request->get('cash_flow_group', 'daily');
        if (!in_array($cashFlowGroup, ['daily', 'weekly', 'monthly'])) {
            $cashFlowGroup = 'daily';
        }

        return Inertia::render('report/Index', [
            'period' => [
                'preset' => $period['preset'],
                'from'   => $from->format('Y-m-d'),
                'to'     => $to->format('Y-m-d'),
            ],
            'cashFlowGroup'     => $cashFlowGroup,
            'summary'           => $this->service->getSummary($userId, $from, $to),
            'categoryBreakdown' => $this->service->getCategoryBreakdown($userId, $from, $to),
            'cashFlow'          => $this->service->getCashFlow($userId, $from, $to, $cashFlowGroup),
            'budgetVsActual'    => $this->service->getBudgetVsActual($userId, $from, $to),
            'topTransactions'   => $this->service->getTopTransactions($userId, $from, $to),
            'accountTrends'     => $this->service->getAccountTrends($userId, $from, $to),
            'goalSnapshot'      => $this->service->getGoalSnapshot($userId),
            'debtSummary'       => $this->service->getDebtSummary($userId),
        ]);
    }

    /**
     * Export report as CSV or PDF.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:csv,pdf',
            'preset' => 'nullable|string',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date',
        ]);

        $userId = Auth::id();
        $period = $this->parsePeriod($request);
        $from   = $period['from'];
        $to     = $period['to'];

        $format = $request->get('format');

        if ($format === 'csv') {
            return $this->exportCsv($userId, $from, $to);
        }

        return $this->exportPdf($userId, $from, $to);
    }

    /**
     * Stream a CSV file with all transactions in the period.
     */
    private function exportCsv(int $userId, Carbon $from, Carbon $to)
    {
        $transactions = $this->service->getTransactionsForExport($userId, $from, $to);
        $filename     = 'report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Date', 'Type', 'Amount', 'Description', 'Category', 'Account', 'To Account']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->transaction_date,
                    $t->type,
                    $t->amount,
                    $t->description ?? '',
                    $t->category?->name ?? '',
                    $t->account?->name ?? '',
                    $t->accountTarget?->name ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate and return a PDF report for the period.
     */
    private function exportPdf(int $userId, Carbon $from, Carbon $to)
    {
        $summary           = $this->service->getSummary($userId, $from, $to);
        $categoryBreakdown = $this->service->getCategoryBreakdown($userId, $from, $to);
        $budgetVsActual    = $this->service->getBudgetVsActual($userId, $from, $to);
        $topTransactions   = $this->service->getTopTransactions($userId, $from, $to, 5);
        $goalSnapshot      = $this->service->getGoalSnapshot($userId);
        $debtSummary       = $this->service->getDebtSummary($userId);

        $pdf = Pdf::loadView('reports.report', compact(
            'from',
            'to',
            'summary',
            'categoryBreakdown',
            'budgetVsActual',
            'topTransactions',
            'goalSnapshot',
            'debtSummary'
        ))->setPaper('a4', 'portrait');

        $filename = 'report-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
