<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use App\Exports\CreditNotesExport;
use Maatwebsite\Excel\Facades\Excel;

class CreditNoteController extends Controller
{
    public function index()
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'company')
            {
                $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->with('clients')->get();
            }
            else
            {
                $invoices = Invoice::where('client', \Auth::user()->id)->with('clients')->get();
            }

            return view('creditNote.index', compact('invoices'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function create()
    {
        $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->get()->pluck('invoice_id', 'id');

        return view('creditNote.create', compact('invoices'));
    }

    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric',
                                   'date' => 'required',
                                   'invoice' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceDue = Invoice::where('id', $request->invoice)->first();
            if($request->amount > $invoiceDue->getDue())
            {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.');
            }
            $invoice             = Invoice::where('id', $request->invoice)->first();
            $credit              = new CreditNote();
        // dd($credit);
            $credit->invoice     = $request->invoice;
            $credit->client      = $invoice->client;
            $credit->date        = $request->date;
            $credit->amount      = $request->amount;
            $credit->description = $request->description;
            $credit->created_by = \Auth::User()->id;
            $credit->save();

            $client        = User::find($invoice->client);
            $creditnoteArr = [
                'invoice_id' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                'invoice_client' => $client->name,
                'credit_note_date' => \Auth::user()->dateFormat($request->date),
                'credit_amount' => \Auth::user()->priceFormat($request->amount),
                'credit_description' => $request->description,
            ];

            // Send Email
            $resp = Utility::sendEmailTemplate('new_credit_note', [$client->id => $client->email], $creditnoteArr);

            return redirect()->back()->with('success', __('Credit Note successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function show(CreditNote $creditNote)
    {
        //
    }


    public function edit(CreditNote $creditNote)
    {
        $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->get()->pluck('invoice_id', 'id');

        return view('creditNote.edit', compact('invoices', 'creditNote'));
    }


    public function update(Request $request, CreditNote $creditNote)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'amount' => 'required|numeric',
                                   'date' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceDue = Invoice::where('id', $creditNote->invoice)->first();

            if($request->amount > $invoiceDue->getDue())
            {
                return redirect()->back()->with('error', 'Maximum ' . \Auth::user()->priceFormat($invoiceDue->getDue()) . ' credit limit of this invoice.');
            }
            $invoice = Invoice::where('id', $creditNote->invoice)->first();

            $creditNote->client      = $invoice->client;
            $creditNote->date        = $request->date;
            $creditNote->amount      = $request->amount;
            $creditNote->description = $request->description;
            $creditNote->save();

            return redirect()->back()->with('success', __('Credit Note successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(CreditNote $creditNote)
    {
        $creditNote->delete();

        return redirect()->back()->with('success', __('CreditNote successfully deleted.'));
    }

    public function getinvoice(Request $request)
    {
        $invoice = Invoice::where('id', $request->id)->first();

        echo json_encode($invoice->getDue());
    }

    public function export()
    {
        $name = 'invoice' . date('Y-m-d i:h:s');
        $data = Excel::download(new CreditNotesExport(), $name . '.xlsx'); ob_end_clean();

        return $data;
    }
}
