<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'goal_type',
        'start_date',
        'end_date',
        'display',
        'description',
        'created_by',
    ];

    public static $goalType = [
        'Invoice',
        'Payment',
        'Expense',
    ];

    public $type;
    public $userId;


    public function target($type, $from, $to, $amount)
    {

        $getInvoiceProductsData        = Utility::getInvoiceProductsData();

        $total    = 0;
        $fromDate = $from . '-01';
        $toDate   = $to . '-01';

        if (\App\Models\Goal::$goalType[$type] == 'Invoice') {

            $invoices = Invoice::where('created_by', \Auth::user()->creatorId())
                ->where('issue_date', '>=', $fromDate)
                ->where('issue_date', '<=', $toDate)
                ->get();

            $total =   $invoices->sum(function ($invoice) use ($getInvoiceProductsData){
                return ( isset($getInvoiceProductsData[$invoice->invoice_id]) ? $getInvoiceProductsData[$invoice->invoice_id]->total : 0);
            });
    
        } elseif (\App\Models\Goal::$goalType[$type] == 'Payment') {
            $total = Payment::where('created_by', \Auth::user()->creatorId())
                ->where('date', '>=', $fromDate)
                ->where('date', '<=', $toDate)
                ->sum('amount');
    
        } elseif (\App\Models\Goal::$goalType[$type] == 'Expense') {
            $total = Expense::where('created_by', \Auth::user()->creatorId())
                ->where('date', '>=', $fromDate)
                ->where('date', '<=', $toDate)
                ->sum('amount');
        }

        $data['percentage'] = ($total * 100) / $amount;
        $data['total']      = $total;

        return $data;
    }

}
