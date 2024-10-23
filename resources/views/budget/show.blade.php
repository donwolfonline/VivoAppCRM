@extends('layouts.admin')
@section('page-title')
    {{__('Budget Vs Actual: ')}}{{ $budget->name }}
@endsection

@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Show Budget Planner')}}</h5>
</div>
   
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page"> {{ $budget->name }}</li>
@endsection


@push('script-page')
    <!-- <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script> -->
    <script>
        //Income Total
        $(document).on('keyup', '.income_data', function () {
            //category wise total
            var el = $(this).parent().parent();
            var inputs = $(el.find('.income_data'));

            var totalincome = 0;
            for (var i = 0; i < inputs.length; i++) {
                var price = $(inputs[i]).val();
                totalincome = parseFloat(totalincome) + parseFloat(price);
            }
            el.find('.totalIncome').html(totalincome);

            // month wise total //
            var month_income = $(this).data('month');
            var month_inputs = $(el.parent().find('.' + month_income + '_income'));
            var month_totalincome = 0;
            for (var i = 0; i < month_inputs.length; i++) {
                var month_price = $(month_inputs[i]).val();
                month_totalincome = parseFloat(month_totalincome) + parseFloat(month_price);
            }
            var month_total_income = month_income + '_total_income';
            el.parent().find('.' + month_total_income).html(month_totalincome);

            //all total //
            var total_inputs = $(el.parent().find('.totalIncome'));
            console.log(total_inputs)
            var income = 0;
            for (var i = 0; i < total_inputs.length; i++) {
                var price = $(total_inputs[i]).html();
                income = parseFloat(income) + parseFloat(price);
            }
            el.parent().find('.income').html(income);

        })


        //Expense Total
        $(document).on('keyup', '.expense_data', function () {
            //category wise total
            var el = $(this).parent().parent();
            var inputs = $(el.find('.expense_data'));

            var totalexpense = 0;
            for (var i = 0; i < inputs.length; i++) {
                var price = $(inputs[i]).val();
                totalexpense = parseFloat(totalexpense) + parseFloat(price);
            }
            el.find('.totalExpense').html(totalexpense);

            // month wise total //
            var month_expense = $(this).data('month');
            var month_inputs = $(el.parent().find('.' + month_expense + '_expense'));
            var month_totalexpense = 0;
            for (var i = 0; i < month_inputs.length; i++) {
                var month_price = $(month_inputs[i]).val();
                month_totalexpense = parseFloat(month_totalexpense) + parseFloat(month_price);
            }
            var month_total_expense = month_expense + '_total_expense';
            el.parent().find('.' + month_total_expense).html(month_totalexpense);

            //all total //
            var total_inputs = $(el.parent().find('.totalExpense'));
            console.log(total_inputs)
            var expense = 0;
            for (var i = 0; i < total_inputs.length; i++) {
                var price = $(total_inputs[i]).html();
                expense = parseFloat(expense) + parseFloat(price);
            }
            el.parent().find('.expense').html(expense);

        })

        //Hide & Show
        $(document).on('change', '.period', function () {
            var period = $(this).val();

            $('.budget_plan').removeClass('d-block');
            $('.budget_plan').addClass('d-none');
            $('#' + period).removeClass('d-none');
            $('#' + period).addClass('d-block');
        });

        // trigger
        $('.period').trigger('change');

    </script>
@endpush


@section('action-btn')
    <div class="all-button-box row d-flex justify-content-end">
        @can('create goal')
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                <a href="{{ route('budget.index') }}" class="btn btn-xs btn-white btn-icon-only width-auto">
                     {{__('Back')}}
                </a>
            </div>
        @endcan
    </div>
@endsection


@section('content')



    <div class="row">
        <div class="col-12">
            <div class="card bg-none card-box overflow-auto px-4">
                {{--  Monthly Budget--}}
                @if($budget->period == 'monthly')
                    <table class="table table-bordered table-item data">
                        <thead>
                        <tr>
                            <td rowspan="2"></td>
                            @foreach($monthList as $month)
                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($monthList as $month)
                                <th scope="col" class="br-1px">Budget</th>
                                <th scope="col" class="br-1px">Actual</th>
                                <th scope="col" class="br-1px">Over Budget</th>
                            @endforeach
                        </tr>
                        </thead>
                        <!----INCOME Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>

                        @php
                            $overBudgetTotal=[];
                        @endphp

                        @foreach ($incomeproduct as $productService)
                        
                            <tr>
                                <td> </td>
                                @foreach($monthList as $month)
                                    @php
                                        $budgetAmount= (@$budget['income_data'][$productService->id][$month])?@$budget['income_data'][$productService->id][$month]:0;
                                        $actualAmount=$incomeArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                    @endphp
                                    <td class="income_data {{$month}}_income">{{!empty (\Auth::user()->priceFormat(@$budget['income_data'][$productService->id][$month]))?\Auth::user()->priceFormat(@$budget['income_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($incomeArr[$productService->id][$month])}}
                                        <p>{{(@$budget['income_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ? '('.(\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>
                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{(@$budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{(@$budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}" >{{@($budget['income_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$overBudgetAmount) !=0) ?'('.(\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>

                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        @php
                            $overBudgetTotalArr = array();
                              foreach($overBudgetTotal as $overBudget)
                              {
                                  foreach($overBudget as $k => $value)
                                  {
                                      $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                  }
                              }
                        @endphp
                       

                        <!------------ EXPENSE Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>
                        @php
                            $overExpenseBudgetTotal=[];
                        @endphp

                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td> </td>
                                @foreach($monthList as $month)
                                    @php
                                        $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                        $actualAmount=$expenseArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                    @endphp
                                    <td class="expense_data {{$month}}_expense">{{\Auth::user()->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?\Auth::user()->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($expenseArr[$productService->id][$month])}}
                                        <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month])!=0) ? '('.(\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}" >{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount) !=0) ?'('.(\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>

                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        @php
                            $overExpenseBudgetTotalArr = array();
                              foreach($overExpenseBudgetTotal as $overExpenseBudget)
                              {
                                  foreach($overExpenseBudget as $k => $value)
                                  {
                                      $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                  }
                              }
                        @endphp

                    

                        <td></td>

                        <tfoot>
                        <tr class="total">
                            <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                            @php
                               // NET PROFIT OF OVER BUDGET
                                $overbudgetprofit = [];
                                $keys   = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                foreach($keys as $v)
                                {
                                    $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                }
                                $data['overbudgetprofit']              = $overbudgetprofit;
                            @endphp

                            @foreach($monthList as $month)
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($budgetprofit[$month]) }}</strong></td>
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($actualprofit[$month]) }}</strong>
                                    <p>{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ?'('.(\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>

                                </td>
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($overbudgetprofit[$month]) }}</strong>
                                    <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}}">{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ? '('.(\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>

                                </td>
                            @endforeach

                        </tr>
                        </tfoot>


                    </table>

                {{--  Quarterly Budget--}}

                @elseif($budget->period == 'quarterly')
                    <table class="table table-bordered table-item data">
                        <thead>
                        <tr>
                            <td rowspan="2"></td> <!-- merge two rows -->
                            @foreach($quarterly_monthlist as $month)
                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($quarterly_monthlist as $month)
                                <th scope="col" class="br-1px">Budget</th>
                                <th scope="col" class="br-1px">Actual</th>
                                <th scope="col" class="br-1px">Over Budget</th>
                            @endforeach
                        </tr>
                        </thead>

                        <!----INCOME Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>

                        @php
                            $overBudgetTotal=[];
                        @endphp

                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td> </td>
                                @foreach($quarterly_monthlist as $month)
                                    @php
                                   
                                        $budgetAmount= ($budget['income_data'][$productService->id][$month]) ? $budget['income_data'][$productService->id][$month]:0;
                                        $actualAmount=$incomeArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                    
                                    @endphp
                                    
                                    <td class="income_data {{$month}}_income">{{!empty (\Auth::user()->priceFormat($budget['income_data'][$productService->id][$month]))?\Auth::user()->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($incomeArr[$productService->id][$month])}}
                                        <p>{{($budget['income_data'][$productService->id][$month] !=0)? 
                                            (\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ? '('.(\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} 
                                            {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">
                                            {{($budget['income_data'][$productService->id][$month] !=0)? 
                                            '('.(\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],
                                            $overBudgetAmount).'%)') :''}}</p>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        @php
                            $overBudgetTotalArr = array();
                              foreach($overBudgetTotal as $overBudget)
                              {
                                  foreach($overBudget as $k => $value)
                                  {
                                     
                                      $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                  }
                              }
                        @endphp
                        
                        <!------------ EXPENSE Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>

                        @php
                            $overExpenseBudgetTotal=[];
                        @endphp

                        @foreach ($expenseproduct as $productService)
                        
                        {{-- <td class="text-dark">{{$productService->name}}</td> --}}
                            <tr>
                                <td> </td>
                                @foreach($quarterly_monthlist as $month)
                               
                                    @php
                                    
                                        $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                        $actualAmount=$expenseArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;

                                    @endphp
                                    <td class="expense_data {{$month}}_expense">{{\Auth::user()->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?\Auth::user()->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($expenseArr[$productService->id][$month])}}
                                        <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]) !=0) ?'('.(\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}} ">{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)!=0) ? '('.(\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)
                                        .'%)') :'':''}}</p>
                                    </td>
                                @endforeach

                                @php
                                    $overExpenseBudgetTotalArr = array();
                                      foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                      {
                                          foreach($overExpenseBudget as $k => $value)
                                          {
                                              $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                          }
                                      }
                                @endphp
                            </tr>
                        @endforeach

                       

                        <td></td>

                        <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @php
                                    // NET PROFIT OF OVER BUDGET
                                     $overbudgetprofit = [];
                                     $keys   = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                     foreach($keys as $v)
                                     {
                                         $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                     }
                                     $data['overbudgetprofit']              = $overbudgetprofit;
                                @endphp
    
                                @foreach($quarterly_monthlist as $month)
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($budgetprofit[$month])}}</strong></td>
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($actualprofit[$month]) }}</strong></td>
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($overbudgetprofit[$month]) }}</strong>
                                        <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ? '('.(\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
    
                                    </td>
                                @endforeach
    
                            </tr>
                            </tfoot>
    

                    </table>

                {{--  Half -Yearly Budget--}}
                @elseif($budget->period == 'half-yearly')
                    <table class="table table-bordered table-item data">
                        <thead>
                        <tr>
                            <td rowspan="2"></td> <!-- merge two rows -->
                            @foreach($half_yearly_monthlist as $month)
                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($half_yearly_monthlist as $month)
                                <th scope="col" class="br-1px">Budget</th>
                                <th scope="col" class="br-1px">Actual</th>
                                <th scope="col" class="br-1px">Over Budget</th>
                            @endforeach
                        </tr>
                        </thead>

                        <!----INCOME Category ---------------------->
                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span> {{ __('Income :')}} </span></th>
                        </tr>
                        @php
                            $overBudgetTotal=[];
                        @endphp
                      
                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td></td>
                                @foreach($half_yearly_monthlist as $month)
								
                                    @php
								
                                        $budgetAmount= (@$budget['income_data'][$productService->id][$month] ? @$budget['income_data'][$productService->id][$month]:0)  ;
							
                                        $actualAmount=$incomeArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;
                                      
                                    @endphp
 
                                    <td class="income_data {{$month}}_income">{{!empty (\Auth::user()->priceFormat(@$budget['income_data'][$productService->id][$month]))?\Auth::user()->priceFormat(@$budget['income_data'][$productService->id][$month]):0}}</td>
                                 
                                    <td>{{\Auth::user()->priceFormat($incomeArr[$productService->id][$month])}}
                                        <p>{{(@$budget['income_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month])!=0) ?'('.(\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],$incomeArr[$productService->id][$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
        
                                        <p class="{{(@$budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 
                                        'green-text':''}} {{(@$budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 
                                        'red-text':''}}">{{(@$budget['income_data'][$productService->id][$month] !=0)? 
                                        (\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],
                                        $overBudgetAmount)!=0) ?'
                                        ('.(\App\Models\Budget::percentage(@$budget['income_data'][$productService->id][$month],
                                        $overBudgetAmount).'%)') :'':''}}</p>
                                       
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        @php
                        $overBudgetTotalArr = array();
                          foreach($overBudgetTotal as $overBudget)
                          {
                              foreach($overBudget as $k => $value)
                              {
                                 
                                  $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                              }
                          }
                    @endphp
                        <!------------ EXPENSE Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>
                       
                        @foreach ($expenseproduct as $expense)
                            <tr>
                                <td class="text-dark">{{$expense->name}}</td>
                                @foreach($half_yearly_monthlist as $month)
                                    @php 
                                        $budgetAmount= ($budget['expense_data'][$expense->id][$month])? $budget['expense_data'][$expense->id][$month]:0;
                                        $actualAmount=$expenseArr[$expense->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overExpenseBudgetTotal[$expense->id][$month]=$overBudgetAmount;

                                    @endphp
                                    <td class="expense_data {{$month}}_expense">{{\Auth::user()->priceFormat(!empty($budget['expense_data']
                                    [$expense->id][$month]))?\Auth::user()->priceFormat($budget['expense_data'][$expense->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($expenseArr[$expense->id][$month])}}
                                        <p>{{($budget['expense_data'][$expense->id][$month] !=0)?
                                         (\App\Models\Budget::percentage($budget['expense_data'][$expense->id][$month],
                                         $expenseArr[$expense->id][$month])!=0) ?
                                         '('.(\App\Models\Budget::percentage($budget['expense_data'][$expense->id][$month],
                                         $expenseArr[$expense->id][$month]).'%)') :'':''}}</p>
                                    </td>
                                   
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['expense_data'][$expense->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$expense->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['expense_data'][$expense->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$expense->id][$month],$overBudgetAmount)!=0)?'('.(\App\Models\Budget::percentage
                                        ($budget['expense_data'][$expense->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                    </td>
                                @endforeach

                                @php
                                $overExpenseBudgetTotalArr = array();
                                  foreach($overExpenseBudgetTotal as $overExpenseBudget)
                                  {
                                      foreach($overExpenseBudget as $k => $value)
                                      {
                                          $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                      }
                                  }
                            @endphp

                            </tr>
                        @endforeach
                        <td></td>
                        <tfoot>
                            <tr class="total">
                                <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                                @php
                                    // NET PROFIT OF OVER BUDGET
                                     $overbudgetprofit = [];
                                     $keys   = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                     foreach($keys as $v)
                                     {
                                         $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                     }
                                     $data['overbudgetprofit']              = $overbudgetprofit;
                                @endphp
    
                                @foreach($half_yearly_monthlist as $month)
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($budgetprofit[$month])}}</strong></td>
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($actualprofit[$month]) }}</strong>
                                        <p>{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ? '('.(\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>
                                    </td>
                                    <td class="text-dark"><strong>{{\Auth::user()->priceFormat($overbudgetprofit[$month]) }}</strong>
                                        <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ?'('.(\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                    </td>
                                @endforeach
    
                            </tr>
                            </tfoot>
                    </table>

            {{-- Yearly Budget--}}
                @else
                    <table class="table table-bordered table-item data">
                        <thead>
                        <tr>
                            <td rowspan="2"></td> <!-- merge two rows -->
                            @foreach($yearly_monthlist as $month)
                                <th colspan="3" scope="colgroup" class="text-center br-1px">{{$month}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($yearly_monthlist as $month)
                                <th scope="col" class="br-1px">Budget</th>
                                <th scope="col" class="br-1px">Actual</th>
                                <th scope="col" class="br-1px">Over Budget</th>
                            @endforeach
                        </tr>
                        </thead>

                        <!----INCOME Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Income :')}}</span></th>
                        </tr>

                        @php
                            $overBudgetTotal=[];
                        @endphp

                        @foreach ($incomeproduct as $productService)
                            <tr>
                                <td class="text-dark">{{$productService->name}}</td>
                                @foreach($yearly_monthlist as $month)
                                    @php
                                        $budgetAmount= ($budget['income_data'][$productService->id][$month]) ? $budget['income_data'][$productService->id][$month]:0;
                                        $actualAmount=$incomeArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overBudgetTotal[$productService->id][$month]=$overBudgetAmount;

                                    @endphp

                                   <td class="income_data {{$month}}_income">{{!empty (\Auth::user()->priceFormat($budget['income_data'][$productService->id][$month]))?\Auth::user()->priceFormat($budget['income_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($incomeArr[$productService->id][$month])}}
                                        <p>{{($budget['income_data'][$productService->id][$month] !=0)?
                                         (\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],
                                         $incomeArr[$productService->id][$month])!=0) ?
                                         '('.(\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],
                                         $incomeArr[$productService->id][$month]).'%)') :'':''}}</p>

                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['income_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}}
                                         {{($budget['income_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">
                                         {{($budget['income_data'][$productService->id][$month] !=0)? 
                                         (\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],$overBudgetAmount)!=0) 
                                         ?'('.(\App\Models\Budget::percentage($budget['income_data'][$productService->id][$month],
                                        $overBudgetAmount).'%)') :'':''}}</p>

                                    </td>

                                @endforeach

                            </tr>
                        @endforeach
                        @php
                            $overBudgetTotalArr = array();
                              foreach($overBudgetTotal as $overBudget)
                              {
                                  foreach($overBudget as $k => $value)
                                  {
                                      $overBudgetTotalArr[$k] = (isset($overBudgetTotalArr[$k]) ? $overBudgetTotalArr[$k] + $value : $value);
                                  }
                              }
                        @endphp

                        <!------------ EXPENSE Category ---------------------->

                        <tr>
                            <th colspan="37" class="text-dark light_blue"><span>{{__('Expense :')}}</span></th>
                        </tr>
                        @php
                            $overExpenseBudgetTotal=[];
                        @endphp

                        @foreach ($expenseproduct as $productService)
                            <tr>
                                <td class="text-dark">{{$productService->name}}</td>
                                @foreach($yearly_monthlist as $month)
                                    @php

                                        $budgetAmount= ($budget['expense_data'][$productService->id][$month])?$budget['expense_data'][$productService->id][$month]:0;
                                        $actualAmount=$expenseArr[$productService->id][$month];
                                        $overBudgetAmount=$actualAmount-$budgetAmount;
                                        $overExpenseBudgetTotal[$productService->id][$month]=$overBudgetAmount;

                                    @endphp
                                    <td class="expense_data {{$month}}_expense">{{\Auth::user()->priceFormat(!empty($budget['expense_data'][$productService->id][$month]))?\Auth::user()->priceFormat($budget['expense_data'][$productService->id][$month]):0}}</td>
                                    <td>{{\Auth::user()->priceFormat($expenseArr[$productService->id][$month])}}
                                        <p>{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month])!=0) ?'('.(\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$expenseArr[$productService->id][$month]).'%)') :'':''}}</p>


                                    </td>
                                    <td>{{\Auth::user()->priceFormat($overBudgetAmount)}}
                                        <p class="{{($budget['expense_data'][$productService->id][$month] < $overBudgetAmount)? 'green-text':''}} {{($budget['expense_data'][$productService->id][$month] > $overBudgetAmount)? 'red-text':''}}">{{($budget['expense_data'][$productService->id][$month] !=0)? (\App\Models\Budget::percentage($budget['expense_data'][$productService->id][$month],$overBudgetAmount)!=0) ?'('.(\App\Models\Budget::percentage
                                        ($budget['expense_data'][$productService->id][$month],$overBudgetAmount).'%)') :'':''}}</p>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach

                        @php
                            $overExpenseBudgetTotalArr = array();
                              foreach($overExpenseBudgetTotal as $overExpenseBudget)
                              {
                                  foreach($overExpenseBudget as $k => $value)
                                  {
                                      $overExpenseBudgetTotalArr[$k] = (isset($overExpenseBudgetTotalArr[$k]) ? $overExpenseBudgetTotalArr[$k] + $value : $value);
                                  }
                              }
                        @endphp

                        <td></td>
                        <tfoot>
                        <tr class="total">
                            <td class="text-dark"><span></span><strong>{{__('NET PROFIT :')}}</strong></td>
                            @php
                                // NET PROFIT OF OVER BUDGET
                                 $overbudgetprofit = [];
                                 $keys   = array_keys($overBudgetTotalArr + $overExpenseBudgetTotalArr);
                                 foreach($keys as $v)
                                 {
                                     $overbudgetprofit[$v] = (empty($overBudgetTotalArr[$v]) ? 0 : $overBudgetTotalArr[$v]) - (empty($overExpenseBudgetTotalArr[$v]) ? 0 : $overExpenseBudgetTotalArr[$v]);
                                 }
                                 $data['overbudgetprofit']              = $overbudgetprofit;
                            @endphp

                            @foreach($yearly_monthlist as $month)
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($budgetprofit[$month])}}</strong></td>
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($actualprofit[$month]) }}</strong>
                                    <p>{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month])!=0) ?'('.(\App\Models\Budget::percentage($budgetprofit[$month],$actualprofit[$month]).'%)') :'':''}}</p>

                                </td>
                                <td class="text-dark"><strong>{{\Auth::user()->priceFormat($overbudgetprofit[$month]) }}</strong>
                                    <p class="{{($budgetprofit[$month] < $overbudgetprofit[$month])? 'green-text':''}} {{($budgetprofit[$month] > $overbudgetprofit[$month])? 'red-text':''}}">{{($budgetprofit[$month] !=0)? (\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month])!=0) ?'('.(\App\Models\Budget::percentage($budgetprofit[$month],$overbudgetprofit[$month]).'%)') :'':''}}</p>
                                </td>
                            @endforeach

                        </tr>
                        </tfoot>

                    </table>

                @endif

            </div>
        </div>
    </div>





@endsection
