<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\ProductServiceCategory;
use App\Models\Revenue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow\Constant\Periodic\Payments;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

            $budgets = Budget::where('created_by', '=', \Auth::user()->creatorId())->get();
            $periods = Budget::$period;
            return view('budget.index', compact('budgets', 'periods'));


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
            $periods = Budget::$period;

            $data['monthList'] = $month = $this->yearMonth();          //Monthly

            $data['quarterly_monthlist'] = [                          //Quarterly
                                                                      'Jan-Mar',
                                                                      'Jul-Sep',
                                                                      'Apr-Jun',
                                                                      'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [                     // Half - Yearly
                                                                   'Jan-Jun',
                                                                   'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [                   // Yearly
                                                            'Jan-Dec',
            ];


            $data['yearList'] = $this->yearList();
            if(!empty($request->from))
            {
                $year = $request->from;
            }
            else
            {
                $year = date('Y');
            }
            $data['currentYear'] = $year;


            $incomeproduct = InvoicePayment::rightjoin('payments', 'invoice_payments.created_by', '=', 'payments.created_by')->limit(1)
               ->get(['payments.*', 'invoice_payments.*']);

            $expenseproduct = Expense::where('created_by', '=', \Auth::user()->creatorId())->limit(1)->get();

            return view('budget.create', compact('periods','incomeproduct','expenseproduct'), $data);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            $validator = \Validator::make($request->all(), [
                'name' => 'required',
                'year' => 'required',
                // 'to' => 'required',
                'period' => 'required',


            ]);
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $budget               = new Budget();
            $budget->name         = $request->name;
            $budget->from         = $request->year;
            // $budget->to           = $request->to;
            $budget->period       = $request->period;
            $budget->income_data  = json_encode($request->income);
            $budget->expense_data = json_encode($request->expense);
            $budget->created_by   = \Auth::user()->creatorId();
            $budget->save();

            return redirect()->route('budget.index')->with('success', __('Budget Plan successfully created.'));
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function show($ids)
    {
            $id                    = Crypt::decrypt($ids);
            $budget                = Budget::find($id);
            $budget['income_data'] = json_decode($budget->income_data, true);
            $budgetTotalArrs       = array_values($budget['income_data']);

            $budgetTotal = array();

            foreach($budgetTotalArrs as $budgetTotalArr)
            {
                foreach($budgetTotalArr as $k => $value)
                {
                    $budgetTotal[$k] = (isset($budgetTotal[$k]) ? $budgetTotal[$k] + $value : $value);

                }
            }

            $budget['expense_data'] = json_decode($budget->expense_data, true);

            $budgetExpenseTotalArrs       = array_values($budget['expense_data']);

            $budgetExpenseTotal = array();
            foreach($budgetExpenseTotalArrs as $budgetExpenseTotalArr)
            {

                foreach($budgetExpenseTotalArr as $k => $value)
                {
                    $budgetExpenseTotal[$k] = (isset($budgetExpenseTotal[$k]) ? $budgetExpenseTotal[$k] + $value : $value);

                }


            }

            $data['monthList']      = $month = $this->yearMonth();          //Monthly
            $data['quarterly_monthlist'] = [                          //Quarterly
                                                                      '1-3' => 'Jan-Mar',
                                                                      '4-6' => 'Apr-Jun',
                                                                      '7-9' => 'Jul-Sep',
                                                                      '10-12' => 'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [                     // Half - Yearly
                                                                   '1-6' => 'Jan-Jun',
                                                                   '7-12' => 'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [                   // Yearly
                                                            '1-12' => 'Jan-Dec',
            ];

            $data['yearList'] = $this->yearList();
            if(!empty($budget->from))
            {
                $year = $budget->from;
            }
            else
            {
                $year = date('Y');
            }
            $data['currentYear'] = $year;
            $incomeproduct = InvoicePayment::rightjoin('payments', 'invoice_payments.created_by', '=', 'payments.created_by')->limit(1)
            ->get(['payments.*', 'invoice_payments.*']);

            $incomeArr      = [];
            $incomeTotalArr = [];

            foreach($incomeproduct as $cat)
            {

                if($budget->period == 'monthly')
                {
                    $monthIncomeArr      = [];
                    $monthTotalIncomeArr = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $revenuTotalAmount = InvoicePayment::where('created_by', '=', \Auth::user()->creatorId());
                        $revenuTotalAmount->whereRAW('YEAR(date) =?', [$year]);
                        $revenuTotalAmount->whereRAW('MONTH(date) =?', [$i]);
                        $revenuinvoiceAmount = $revenuTotalAmount->sum('amount');


                        $revenuTotalAmount = Payment::where('created_by', '=', \Auth::user()->creatorId());
                        $revenuTotalAmount->whereRAW('YEAR(date) =?', [$year]);
                        $revenuTotalAmount->whereRAW('MONTH(date) =?', [$i]);
                        $revenupaymentAmount = $revenuTotalAmount->sum('amount');


                        $month = date("F", strtotime(date('Y-' . $i)));

                        $monthIncomeArr[$month] = $revenupaymentAmount + $revenuinvoiceAmount;
                    }

                    $incomeArr[$cat->id] = $monthIncomeArr;

                 }
                else if($budget->period == 'quarterly' || $budget->period == 'half-yearly' || $budget->period == 'yearly')
                {

                    if($budget->period == 'quarterly')
                    {
                        $durations = $data['quarterly_monthlist'];
                    }
                    elseif($budget->period == 'yearly')
                    {
                        $durations = $data['yearly_monthlist'];
                    }
                    else
                    {
                        $durations = $data['half_yearly_monthlist'];
                    }

                    $monthIncomeArr = [];

                    foreach($durations as $monthnumber => $monthName)
                    {
                        $month        = explode('-', $monthnumber);
                        $invoiceTotalAmount = InvoicePayment::where('created_by', '=', \Auth::user()->creatorId());
                        $invoiceTotalAmount->whereRAW('YEAR(date) =?', [$year]);
                        $invoiceTotalAmount->whereRAW('MONTH(date) >=?', $month[0]);
                        $invoiceTotalAmount->whereRAW('MONTH(date) <=?', $month[1]);
                        $invoiceAmount = $invoiceTotalAmount->sum('amount');


                        $month             = explode('-', $monthnumber);
                        $invoiceTotalAmount = Payment::where('created_by', '=', \Auth::user()->creatorId());
                        $invoiceTotalAmount->whereRAW('YEAR(date) =?', [$year]);
                        $invoiceTotalAmount->whereRAW('MONTH(date) >=?', $month[0]);
                        $invoiceTotalAmount->whereRAW('MONTH(date) <=?', $month[1]);
                        $revenupaymentAmount = $invoiceTotalAmount->sum('amount');

                         $monthIncomeArr[$monthName] = $invoiceAmount + $revenupaymentAmount;


                    }
                    $incomeArr[$cat->id] = $monthIncomeArr;
                }

            }



            $expenseproduct = Expense::where('created_by', '=', \Auth::user()->creatorId())->limit(1)->get();

            $expenseArr = [];
            $expenseTotalArr = [];

            foreach($expenseproduct as $expense)
            {
                if($budget->period == 'monthly')
                {
                    $monthExpenseArr = [];
                    $monthTotalExpenseArr = [];
                    for($i = 1; $i <= 12; $i++)
                    {

                        $paymentAmount = Expense::where('created_by', '=', \Auth::user()->creatorId());
                        $paymentAmount->whereRAW('YEAR(date) =?', [$year]);
                        $paymentAmount->whereRAW('MONTH(date) =?', [$i]);
                        $paymentAmount = $paymentAmount->sum('amount');

                        $month                   = date("F", strtotime(date('Y-' . $i)));
                        $monthExpenseArr[$month] =  $paymentAmount;
                    }
                    $expenseArr[$expense->id] = $monthExpenseArr;
                }

                else if($budget->period == 'quarterly' || $budget->period == 'half-yearly' || $budget->period == 'yearly')
                {
                    if($budget->period == 'quarterly')
                    {
                        $durations = $data['quarterly_monthlist'];
                    }
                    elseif($budget->period == 'yearly')
                    {
                        $durations = $data['yearly_monthlist'];
                    }
                    else
                    {
                        $durations = $data['half_yearly_monthlist'];
                    }

                    $monthExpenseArr = [];
                    foreach($durations as $monthnumber => $monthName)
                    {
                        $month         = explode('-', $monthnumber);
                        $paymentAmount = Expense::where('created_by', '=', \Auth::user()->creatorId());
                        $paymentAmount->whereRAW('YEAR(date) =?', [$year]);
                        $paymentAmount->whereRAW('MONTH(date) >=?', $month[0]);
                        $paymentAmount->whereRAW('MONTH(date) <=?', $month[1]);
                        $paymentAmount = $paymentAmount->sum('amount');

                        $monthExpenseArr[$monthName] = $paymentAmount;


                    }

                    $expenseArr[$expense->id] = $monthExpenseArr;

                }


                // NET PROFIT OF BUDGET
                $budgetprofit = [];
                $keys   = array_keys($budgetTotal + $budgetExpenseTotal);

                foreach($keys as $v)
                {
                    $budgetprofit[$v] = (empty($budgetTotal[$v]) ? 0 : $budgetTotal[$v]) - (empty($budgetExpenseTotal[$v]) ? 0 : $budgetExpenseTotal[$v]);

                }
                $data['budgetprofit']              = $budgetprofit;

                // NET PROFIT OF ACTUAL
                $actualprofit = [];
                $keys   = array_keys($monthIncomeArr + $monthExpenseArr);

                foreach($keys as $v)
                {
                    $actualprofit[$v] = (empty($monthIncomeArr[$v]) ? 0 : $monthIncomeArr[$v]) - (empty($monthExpenseArr[$v]) ? 0 : $monthExpenseArr[$v]);
                }

                $data['actualprofit']              = $actualprofit;
            }


            return view('budget.show', compact('id', 'budget', 'incomeproduct', 'expenseproduct', 'incomeArr', 'expenseArr', 'incomeTotalArr','expenseTotalArr','budgetExpenseTotal'
            ), $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($ids)
    {

            $id     = Crypt::decrypt($ids);
            $budget = Budget::find($id);

            $budget['income_data']  = json_decode($budget->income_data, true);
            $budget['expense_data'] = json_decode($budget->expense_data, true);

            $periods = Budget::$period;

            $data['monthList'] = $month = $this->yearMonth();        //Monthly

            $data['quarterly_monthlist'] = [                      //Quarterly
                                                                  'Jan-Mar',
                                                                  'Apr-Jun',
                                                                  'Jul-Sep',
                                                                  'Oct-Dec',
            ];

            $data['half_yearly_monthlist'] = [                      // Half - Yearly
                                                                    'Jan-Jun',
                                                                    'Jul-Dec',
            ];

            $data['yearly_monthlist'] = [                           // Yearly
                                                                    'Jan-Dec',
            ];


            $data['yearList'] = $this->yearList();
            if(!empty($budget->from))
            {
                $year = $budget->from;
            }
            else
            {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            $incomeproduct = InvoicePayment::rightjoin('payments', 'invoice_payments.created_by', '=', 'payments.created_by')->limit(1)
            ->get(['payments.*', 'invoice_payments.*']);
            $expenseproduct = Expense::where('created_by', '=', \Auth::user()->creatorId())->limit(1)->get();

            return view('budget.edit', compact('periods', 'budget', 'incomeproduct', 'expenseproduct'), $data);



    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Budget $budget)
    {
            if($budget->created_by == \Auth::user()->creatorId())
            {
                $validator = \Validator::make($request->all(), [
                    'name' => 'required',
                    'year' => 'required',
                    // 'to' => 'required',
                    'period' => 'required',

                ]);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $budget->name         = $request->name;
                $budget->from         = $request->year;
                // $budget->to           = $request->to;
                $budget->period       = $request->period;
                $budget->income_data  = json_encode($request->income);
                $budget->expense_data = json_encode($request->expense);
                $budget->save();


                return redirect()->route('budget.index')->with('success', __('Budget Plan successfully updated.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Budget $budget
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Budget $budget)
    {

            if($budget->created_by == \Auth::user()->creatorId())
            {
                $budget->delete();
                return redirect()->route('budget.index')->with('success', __('Budget Plan successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }


    }


    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }


    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach(range($ending_year, $starting_year) as $year)
        {
            $years[$year] = $year;
        }

        return $years;
    }

}
