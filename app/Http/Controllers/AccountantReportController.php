<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Budget;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;

class AccountantReportController extends Controller
{
    public function index(Request $request)
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login')->with('error', 'Please login first');
        }
        
        $selectedYear = $request->get('year', date('Y'));

        // Summary Statistics
        $totalIncome = Income::where('schoolID', $schoolID)->whereYear('date', $selectedYear)->sum('amount');
        $totalExpenses = Expense::where('schoolID', $schoolID)->where('status', 'Approved')->whereYear('date', $selectedYear)->sum('amount');
        $budgetBalance = Budget::where('schoolID', $schoolID)->where('status', 'Active')->where('fiscal_year', $selectedYear)->sum('remaining_amount');
        $pendingExpensesCount = Expense::where('schoolID', $schoolID)->where('status', 'Pending')->whereYear('date', $selectedYear)->count();

        // Get unique years for the filter
        $budgetYears = Budget::where('schoolID', $schoolID)
            ->select('fiscal_year as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $expenseYears = Expense::where('schoolID', $schoolID)
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->pluck('year')
            ->toArray();

        $availableYears = array_unique(array_merge($budgetYears, $expenseYears, [date('Y')]));
        arsort($availableYears);
        $availableYears = array_values($availableYears);

        return view('accountant.reports.index', compact(
            'totalIncome', 
            'totalExpenses', 
            'budgetBalance', 
            'pendingExpensesCount',
            'selectedYear',
            'availableYears'
        ));
    }

    public function getChartData(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->get('year', date('Y'));
        
        // Trends for the selected year (by month)
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::createFromDate($year, $m, 1);
            $monthName = $date->format('M');
            $months[] = $monthName;

            $income = Income::where('schoolID', $schoolID)
                ->whereMonth('date', $m)
                ->whereYear('date', $year)
                ->sum('amount');
            $incomeData[] = $income;

            $expense = Expense::where('schoolID', $schoolID)
                ->where('status', 'Approved')
                ->whereMonth('date', $m)
                ->whereYear('date', $year)
                ->sum('amount');
            $expenseData[] = $expense;
        }

        // Category breakdown (Selected Year)
        $categoriesStats = Expense::where('schoolID', $schoolID)
            ->where('status', 'Approved')
            ->whereYear('date', $year)
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->get();

        // Budget vs Expenses Comparison (Selected Year)
        $budgets = Budget::where('schoolID', $schoolID)
            ->where('fiscal_year', $year)
            ->get();

        $budgetComparison = [];
        $budgetLabels = [];
        $budgetAllocated = [];
        $budgetSpent = [];
        $expenseDetails = [];

        // Get all unique categories from both budgets and actual expenses
        $allCategories = $budgets->pluck('budget_category')
            ->merge($categoriesStats->pluck('expense_category'))
            ->unique()
            ->values();

        foreach ($allCategories as $category) {
            $allocated = $budgets->where('budget_category', $category)->sum('allocated_amount');
            
            $spent = Expense::where('schoolID', $schoolID)
                ->where('expense_category', $category)
                ->where('status', 'Approved')
                ->whereYear('date', $year)
                ->sum('amount');

            $details = Expense::where('schoolID', $schoolID)
                ->where('expense_category', $category)
                ->where('status', 'Approved')
                ->whereYear('date', $year)
                ->select('date', 'description', 'amount', 'voucher_number')
                ->orderBy('date', 'desc')
                ->limit(20) // limit for visibility
                ->get();

            $budgetLabels[] = $category;
            $budgetAllocated[] = (float)$allocated;
            $budgetSpent[] = (float)$spent;
            
            $expenseDetails[$category] = [
                'allocated' => (float)$allocated,
                'spent' => (float)$spent,
                'is_over' => $spent > $allocated,
                'overage' => $spent > $allocated ? ($spent - $allocated) : 0,
                'percentage' => $allocated > 0 ? round(($spent / $allocated) * 100, 1) : ($spent > 0 ? 100 : 0),
                'items' => $details
            ];
        }

        return response()->json([
            'trends' => [
                'labels' => $months,
                'income' => $incomeData,
                'expenses' => $expenseData,
                'title' => "Financial Trends ($year)"
            ],
            'categories' => [
                'labels' => $categoriesStats->pluck('expense_category'),
                'data' => $categoriesStats->pluck('total')
            ],
            'budget_comparison' => [
                'labels' => $budgetLabels,
                'allocated' => $budgetAllocated,
                'spent' => $budgetSpent,
                'details' => $expenseDetails
            ]
        ]);
    }

    public function exportExpenses(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $filename = "Expenses_Export_" . date('Y-m-d') . ".csv";
        $columns = ['Date', 'Voucher Number', 'Category', 'Description', 'Amount', 'Status', 'Entered By'];

        $callback = function() use ($schoolID, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $expenses = Expense::where('schoolID', $schoolID)
                ->with('enteredBy')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->date,
                    $expense->voucher_number,
                    $expense->expense_category,
                    $expense->description,
                    $expense->amount,
                    $expense->status,
                    $expense->enteredBy->name ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function exportIncome(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $filename = "Income_Export_" . date('Y-m-d') . ".csv";
        $columns = ['Date', 'Receipt Number', 'Category', 'Payer', 'Description', 'Amount', 'Method'];

        $callback = function() use ($schoolID, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $incomes = Income::where('schoolID', $schoolID)
                ->orderBy('date', 'desc')
                ->get();

            foreach ($incomes as $income) {
                fputcsv($file, [
                    $income->date,
                    $income->receipt_number,
                    $income->income_category,
                    $income->payer_name,
                    $income->description,
                    $income->amount,
                    $income->payment_method
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function exportReportPdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->get('year', date('Y'));
        $school = School::find($schoolID);

        // Fetch Overview Stats
        $income = Income::where('schoolID', $schoolID)->whereYear('date', $year)->sum('amount');
        $expenses = Expense::where('schoolID', $schoolID)->whereYear('date', $year)->where('status', 'Approved')->sum('amount');
        $budget = Budget::where('schoolID', $schoolID)->where('fiscal_year', $year)->sum('allocated_amount');

        // Fetch Category Breakdown for Bar Chart
        $categoriesStats = Expense::where('schoolID', $schoolID)
            ->whereYear('date', $year)
            ->where('status', 'Approved')
            ->select('expense_category', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_category')
            ->get();

        // Budget vs Actual for Bar Chart
        $budgetAllocations = Budget::where('schoolID', $schoolID)
            ->where('fiscal_year', $year)
            ->select('budget_category as category_name', DB::raw('SUM(allocated_amount) as allocated'))
            ->groupBy('budget_category')
            ->get();

        $budgetVsActual = [];
        foreach ($budgetAllocations as $ba) {
            $spent = Expense::where('schoolID', $schoolID)
                ->whereYear('date', $year)
                ->where('expense_category', $ba->category_name)
                ->where('status', 'Approved')
                ->sum('amount');

            $budgetVsActual[] = [
                'category' => $ba->category_name,
                'allocated' => (float)$ba->allocated,
                'spent' => (float)$spent,
                'is_over' => $spent > $ba->allocated
            ];
        }

        $data = [
            'school' => $school,
            'year' => $year,
            'stats' => (object)[
                'income' => $income,
                'expenses' => $expenses,
                'budget' => $budget,
                'balance' => $income - $expenses
            ],
            'categories' => collect($categoriesStats),
            'budgetVsActual' => $budgetVsActual,
            'title' => "Financial Report - $year"
        ];

        $pdf = PDF::loadView('accountant.pdf.financial_report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("Financial_Report_{$year}.pdf");
    }

    public function exportExpensesPdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->get('year', date('Y'));
        $school = School::find($schoolID);

        $expenses = Expense::where('schoolID', $schoolID)
            ->whereYear('date', $year)
            ->with('enteredBy')
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            'school' => $school,
            'year' => $year,
            'expenses' => $expenses,
            'total' => $expenses->sum('amount'),
            'title' => "Expense Report - $year"
        ];

        $pdf = PDF::loadView('accountant.pdf.expenses_report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("Expenses_Report_{$year}.pdf");
    }

    public function exportIncomePdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->get('year', date('Y'));
        $school = School::find($schoolID);

        $incomes = Income::where('schoolID', $schoolID)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();

        $data = [
            'school' => $school,
            'year' => $year,
            'incomes' => $incomes,
            'total' => $incomes->sum('amount'),
            'title' => "Income Report - $year"
        ];

        $pdf = PDF::loadView('accountant.pdf.income_report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("Income_Report_{$year}.pdf");
    }

    public function exportBudgetPdf(Request $request)
    {
        $schoolID = Session::get('schoolID');
        $year = $request->get('year', date('Y'));
        $school = School::find($schoolID);

        $budgets = Budget::where('schoolID', $schoolID)
            ->where('fiscal_year', $year)
            ->orderBy('budget_category')
            ->get();

        // Calculate spent for each budget category
        $budgets = $budgets->map(function($b) use ($schoolID, $year) {
            $b->spent = Expense::where('schoolID', $schoolID)
                ->whereYear('date', $year)
                ->where('expense_category', $b->budget_category)
                ->where('status', 'Approved')
                ->sum('amount');
            return $b;
        });

        $data = [
            'school' => $school,
            'year' => $year,
            'budgets' => $budgets,
            'total_allocated' => $budgets->sum('allocated_amount'),
            'total_spent' => $budgets->sum('spent'),
            'title' => "School Budget - $year"
        ];

        $pdf = PDF::loadView('accountant.pdf.budget_report_pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'tempDir' => public_path(),
                'chroot' => public_path(),
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("Budget_Report_{$year}.pdf");
    }
}
