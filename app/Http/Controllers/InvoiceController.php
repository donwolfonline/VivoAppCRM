<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use App\Models\Item;
use App\Models\Plan;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\UserDefualtView;
use App\Models\Utility;
use App\Models\Client;
use App\Models\StockReport;
use Illuminate\Support\Facades\Auth;
use App\Exports\InvoiceExport;
use App\Models\InvoiceBankTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            if (\Auth::user()->type == 'company') {
                $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->with(['clients','payments']);
            } else {
                $invoices = Invoice::where('client', \Auth::user()->id)->with(['clients','payments']);
            }

            if (!empty($request->status)) {
                $invoices->where('status', $request->status);
            }

            if (!empty($request->start_date)) {
                $invoices->where('due_date', '>=', $request->start_date);
            }

            if (!empty($request->end_date)) {
                $invoices->where('due_date', '<=', $request->end_date);
            }

            $invoices = $invoices->get();

            $status = [
                __('Draft'),
                __('Open'),
                __('Sent'),
                __('Unpaid'),
                __('Partialy Paid'),
                __('Paid'),
            ];

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'invoice';
            $defualtView->view   = 'list';
            User::userDefualtView($defualtView);

            return view('invoice.index', compact('invoices', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $clients = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');
        $taxes = TaxRate::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        return view('invoice.create', compact('clients', 'taxes'));
    }

    public function store(Request $request)
    {

        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'issue_date' => 'required',
                    'due_date' => 'required',
                    'client' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice              = new Invoice();
            $invoice->invoice_id  = $this->invoiceNumber();
            $invoice->issue_date  = $request->issue_date;
            $invoice->due_date    = $request->due_date;
            $invoice->client      = $request->client;
            $invoice->project     = ($request->type == 'Project') ? $request->project : 0;
            $invoice->tax         = ($request->type == 'Project') ? !empty($request->tax) ? implode(',', $request->tax) : '' : '';

            $invoice->type        = $request->type;
            $invoice->status      = 0;
            $invoice->description = $request->description;
            $invoice->created_by  = \Auth::user()->creatorId();
            $invoice->save();


            $settings  = Utility::settings();

            if (isset($settings['invoice_create_notification']) && $settings['invoice_create_notification'] == 1) {
                $uArr = [
                  'user_name' => \Auth::user()->name,
                  'invoice_number'=> \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                 ];
                //  dd($uArr);
                Utility::send_slack_msg('new_invoice', $uArr);
                }

            if (isset($settings['telegram_invoice_create_notification']) && $settings['telegram_invoice_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'invoice_number'=> \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    ];
                //  dd($uArr);
                Utility::send_telegram_msg('new_invoice', $uArr);
                }

            if (isset($settings['twilio_invoice_create_notification']) && $settings['twilio_invoice_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'invoice_number'=> \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    ];
                //  dd($uArr);
                Utility::send_twilio_msg('new_invoice', $uArr);
                }

            //webhook
            $module = "New invoice";
            $webhook = Utility::webhookSetting($module);
            if($webhook)
            {
                $parameter = json_encode($invoice);

                // 1 parameter is URL , 2  (invoice Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if($status == true)
                {
                    return redirect()->back()->with('success', __('Invoice Successfully Created.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Invoice Call Failed.'));
                }
            }
            //end webhook
            return redirect()->route('invoice.show', \Crypt::encrypt($invoice->id))->with('success', 'Invoice successfully created.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        try {
            $ids      = \Crypt::decrypt($id);

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }
        $ids      = \Crypt::decrypt($id);
        $invoice  = Invoice::where('id',$ids)->with('creditNotes')->first();
        // $invoice->invoiceCreditNoteTotal  = $invoice->invoiceCreditNote();
        $settings = Utility::settings();

        $banktransfer = InvoiceBankTransfer::where('created_by', \Auth::user()->creatorId())->where('invoice_id',$ids)->get();

        $items          = [];
        $totalTaxPrice  = 0;
        $totalQuantity  = 0;
        $totalRate      = 0;
        $totalDiscount  = 0;
        $taxesData      = [];
        $getTaxData     = Utility::getTaxData();
        // dd($getTaxData);
        foreach ($invoice->items as $item) {
            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;
            // $taxes         = \Utility::tax($item->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach (explode(',', $item->tax) as $tax) {
                    $taxPrice         = \Utility::taxRate($getTaxData[$tax]['rate'], $item->price, $item->quantity);
                    $totalTaxPrice    += $taxPrice;
                    $itemTax['name']  = $getTaxData[$tax]['name'];
                    $itemTax['rate']  = $getTaxData[$tax]['rate'] . '%';
                    $itemTax['price'] = \Auth::user()->priceFormat($taxPrice);

                    $itemTaxes[] = $itemTax;
                    if (array_key_exists($getTaxData[$tax]['name'], $taxesData)) {
                        $taxesData[$getTaxData[$tax]['name']] = $taxesData[$getTaxData[$tax]['name']] + $taxPrice;
                    } else {
                        $taxesData[$getTaxData[$tax]['name']] = $taxPrice;
                    }
                }
                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }

            $items[] = $item;
        }
        $invoice->items         = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate     = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData     = $taxesData;
        $status                 = [
            __('Draft'),
            __('Open'),
            __('Sent'),
            __('Unpaid'),
            __('Partialy Paid'),
            __('Paid'),
        ];
        $company_payment_setting = Utility::getCompanyPaymentSetting();

        //Credit note
        if(\Auth::user()->type == 'company')
        {
            // $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->where('invoice_id',$ids)->get();
            $invoices = Invoice::where('created_by', \Auth::user()->creatorId())->where('id',$ids)->get();
        }
        else
        {
            $invoices = Invoice::where('client', \Auth::user()->id)->where('invoice_id',$ids)->get();
        }

        $user = \Auth::user();

        if (Auth::user() != null) {
            $objUser         = $user->creatorId();
        } else {
            $objUser         = Invoice::where('created_by', $user->creatorId())->first();
        }
        $company_setting = Utility::settingsById($invoice->created_by);

        $user_receipt   = User::find($objUser);
        $user_storage = $user_receipt->storage_limit;
        $plan   = Plan::find($user_receipt->plan);
        $plan_storage = $plan->storage_limit;

        return view('invoice.view', compact('invoice','invoices','banktransfer', 'settings', 'status', 'company_setting','company_payment_setting','user_storage','plan_storage'));
    }

    public function edit(Invoice $invoice)
    {
        $clients = User::where('created_by', \Auth::user()->creatorId())->where('type', 'client')->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');
        $taxes        = TaxRate::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $invoice->tax = explode(',', $invoice->tax);

        return view('invoice.edit', compact('clients', 'taxes', 'invoice'));
    }


    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'issue_date' => 'required',
                    'due_date' => 'required',

                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice->issue_date  = $request->issue_date;
            $invoice->due_date    = $request->due_date;
            $invoice->description = $request->description;
            $invoice->save();

            //Product Stock Report
            // $type='invoice';
            // $type_id = $invoice->id;
            // StockReport::where('type','=','invoice')->where('type_id' ,'=', $invoice->id)->delete();
            // $description=$invoiceProduct->quantity.'  '.__(' quantity sold in invoice').' '. \Auth::user()->invoiceNumberFormat($invoice->invoice_id);
            // Utility::addProductStock( $invoiceProduct->product_id,$invoiceProduct->quantity,$type,$description,$type_id);

            return redirect()->route('invoice.index')->with('success', 'Invoice successfully updated.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Invoice $invoice)
    {

        $invoice->delete();
        InvoiceProduct::where('invoice', '=', $invoice->id)->delete();

        return redirect()->back()->with('success', __('Invoice successfully deleted.'));
    }

    public function getClientProject(Request $request)
    {
        $projects = Project::where('client', $request->client_id)->get()->pluck('title', 'id');

        return response()->json($projects);
    }
    function transactionNumber($user)
    {
        $latest = InvoicePayment::select('invoice_payments.*')->join('invoices', 'invoice_payments.invoice', '=', 'invoices.id')->where('invoices.created_by', '=', $user)->latest()->first();
      //  dd($latest);
        if($latest)
        {
            return $latest->transaction_id + 1;
        }
        return 1;
    }

    function invoiceNumber()
    {
        $latest = Invoice::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->invoice_id + 1;
    }

    public function createItem($invoice_id)
    {
        $items = Item::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $items->prepend('Select Item', '');

        $invoice = Invoice::find($invoice_id);
        // dd($invoice);

        if ($invoice->type == 'Project') {
            $milestons = !empty($invoice->projects) ? $invoice->projects->milestones->pluck('title', 'title') : '';
            $tasks     = !empty($invoice->projects) ? $invoice->projects->tasks->pluck('title', 'title') : '';
            $taxes     = \Utility::tax($invoice->tax);
        } else {
            $tasks = $milestons = $taxes = [];
        }

        return view('invoice.createItem', compact('invoice', 'items', 'milestons', 'tasks', 'taxes'));
    }

    public function storeProduct(Request $request, $invoice_id)
    {

        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'item' => 'required',
                    'quantity' => 'required',
                    'price' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceProduct              = new InvoiceProduct();
            $invoiceProduct->invoice     = $invoice_id;
            $invoiceProduct->item        = $request->item;
            $invoiceProduct->quantity    = $request->quantity;
            $invoiceProduct->price       = $request->price;
            $invoiceProduct->discount    = $request->discount;
            $invoiceProduct->type        = __('product');
            $invoiceProduct->tax         = $request->tax;
            $invoiceProduct->description = $request->description;

            $invoiceProduct->save();

            Utility::total_quantity('minus', $invoiceProduct->quantity, $request->item);

            //Product Stock Report
            $type = 'invoice';
            $type_id = $invoiceProduct->item;

            StockReport::where('type', '=', 'invoice')->where('type_id', '=', $invoiceProduct->item)->delete();
            $description = $invoiceProduct->quantity . '  ' . __(' quantity sold in invoice') . ' ' . \Auth::user()->invoiceNumberFormat($invoice_id);
            Utility::addProductStock($invoiceProduct->item, $invoiceProduct->quantity, $type, $description, $type_id);

            return redirect()->back()->with('success', 'Invoice product successfully created.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function storeProject(Request $request, $invoice_id)
    {
        // dd($request);
        if (\Auth::user()->type == 'company') {
            if ($request->type == 'milestone') {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'milestone' => 'required',
                        'task' => 'required',
                        'price' => 'required',
                    ]
                );
            } else {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'title' => 'required',
                        'price' => 'required',
                    ]
                );
            }

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoiceProduct              = new InvoiceProduct();
            $invoiceProduct->invoice     = $invoice_id;
            $invoiceProduct->price       = $request->price;
            $invoiceProduct->discount    = !empty($request->discount) ? $request->discount : 0;
            $invoiceProduct->tax         = $request->tax;
            $invoiceProduct->quantity    = 1;
            $invoiceProduct->description = $request->description;
            if ($request->type == 'milestone') {
                $invoiceProduct->item = $request->task . '-' . $request->milestone;
            } else {
                $invoiceProduct->item = $request->title;
            }
            $invoiceProduct->save();

            return redirect()->back()->with('success', 'Invoice project successfully created.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function items(Request $request)
    {
        $items        = Item::where('id', $request->item_id)->first();
        $items->taxes = $items->tax($items->tax);

        return json_encode($items);
    }

    public function itemDelete($id, $item_id)
    {
        if (\Auth::user()->type == 'company') {
            $invoice        = Invoice::find($id);
            $invoiceProduct = InvoiceProduct::find($item_id);
            $invoiceProduct->delete();

            //    if($invoice->getDue() <= 0.0)
            //    {
            //        Invoice::change_status($invoice->id, 3);
            //    }

            return redirect()->back()->with('success', __('Item successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function send($id)
    {
        $invoice = Invoice::find($id);
        if ($invoice->status == 0) {

            $invoice->send_date = date('Y-m-d');
            $invoice->status    = 1;
            $invoice->save();
            $settings  = Utility::settings();

            if (isset($settings['invoice_status_updated_notification']) && $settings['invoice_status_updated_notification'] == 1) {
                $uArr = [
                    'invoice' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    'old_status' => $invoice->status,
                    'status' => $invoice->status
                    ];
                Utility::send_slack_msg('invoice_status', $uArr);
                }
            if (isset($settings['telegram_invoice_status_updated_notification']) && $settings['telegram_invoice_status_updated_notification'] == 1) {
                $uArr = [
                    'invoice' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    'old_status' => $invoice->status,
                    'status' => $invoice->status,
                    ];
                //  dd($uArr);
                Utility::send_telegram_msg('invoice_status', $uArr);
                }
        }

        $client           = User::where('id', $invoice->client)->first();
        $invoice->name    = !empty($client) ? $client->name : '';
        $invoice->invoice = \Auth::user()->invoiceNumberFormat($invoice->invoice);
        $invoiceId    = \Crypt::encrypt($invoice->id);
        $invoice->url = route('invoice.pdf', $invoiceId);

        $invoiceArr = [
            'invoice_id' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
            'invoice_client' => $client->name,
            'invoice_issue_date' => \Auth::user()->dateFormat($invoice->issue_date),
            'invoice_due_date' => \Auth::user()->dateFormat($invoice->expiry_date),
            'invoice_total' => \Auth::user()->priceFormat($invoice->getTotal()),
            'invoice_sub_total' => \Auth::user()->priceFormat($invoice->getSubTotal()),
            'invoice_due_amount' => \Auth::user()->priceFormat($invoice->getDue()),
            'invoice_status' => Invoice::$statues[$invoice->status],
        ];
        // Send Email
        $resp = Utility::sendEmailTemplate('invoice_sent', [$client->id => $client->email], $invoiceArr);
        return redirect()->back()->with('success', __('successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

    public function createReceipt($invoice_id)
    {
        $invoice        = Invoice::find($invoice_id);
        $paymentMethods = PaymentMethod::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

        return view('invoice.createReciept', compact('invoice', 'paymentMethods'));
    }

    public function storeReceipt(Request $request, $invoice_id)
    {
        if (\Auth::user()->type == 'company') {
            $validator = \Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric|min:1',
                    'date' => 'required',
                    'payment_method' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $transactionId = strtoupper(str_replace('.', '', uniqid('', true)));


            $invoicePayment = new InvoicePayment;
            $invoicePayment->transaction = $transactionId;
            $invoicePayment->invoice     = $invoice_id;
            $invoicePayment->amount      = $request->amount;
            $invoicePayment->date        = $request->date;
            $invoicePayment->payment_method = $request->payment_method;
            $invoicePayment->payment_type   = __('Manually');
            $invoicePayment->notes        = $request->notes;
            if ($request->receipt) {

                    $filenameWithExt = $request->file('receipt')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('receipt')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . date('m') . '.' . $extension;
                    $settings = Utility::getStorageSetting();

                    $dir        = 'uploads/attachment/';
                    $url = '';
                    $path = Utility::upload_file($request, 'receipt', $fileNameToStore, $dir, []);

                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                        $invoicePayment->receipt = $url;
                        $invoicePayment->save();
                    } else {
                        return redirect()->back()->with('error', __($path['msg']));
                    }

            }
                $invoicePayment->save();

            $invoice = Invoice::find($invoice_id);

            if ($invoice->getDue() <= 0.0) {
                Invoice::change_status($invoice->id, 5);
            } elseif ($invoice->getDue() > 0) {
                Invoice::change_status($invoice->id, 4);
            } else {
                Invoice::change_status($invoice->id, 3);
            }

            $client     = User::find($invoice->client);
            $invoiceArr = [
                'invoice_id' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                'invoice_client' => $client->name,
                'invoice_issue_date' => \Auth::user()->dateFormat($invoice->issue_date),
                'invoice_due_date' => \Auth::user()->dateFormat($invoice->expiry_date),
                'invoice_total' => $invoice->getTotal(),
                'invoice_sub_total' => $invoice->getSubTotal(),
                'invoice_due_amount' => $invoice->getDue(),
                'payment_total' => $request->amount,
                'payment_date' => \Auth::user()->dateFormat($request->date),
                'invoice_status' => Invoice::$statues[$invoice->status],
            ];

            // Send Email
            $resp = Utility::sendEmailTemplate('invoice_payment_recorded', [$client->id => $client->email], $invoiceArr);

            return redirect()->back()->with('success', __('Payment successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function paymentDelete($id, $payment_id)
    {
        if (\Auth::user()->type == 'company') {
            $invoicePayment = InvoicePayment::find($payment_id);
            $invoicePayment->delete();

            $invoice = Invoice::find($id);

            if ($invoice->getDue() <= 0.0) {
                Invoice::change_status($invoice->id, 5);
            } elseif ($invoice->getDue() > 0) {
                Invoice::change_status($invoice->id, 4);
            } else {
                Invoice::change_status($invoice->id, 3);
            }

            return redirect()->back()->with('success', __('Invoice payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bankpaymentDelete($id,$bankpayment_id)
    {
        if (\Auth::user()->type == 'company') {
             $banktransfer =InvoiceBankTransfer::find($bankpayment_id);
            $banktransfer->delete();
        }
        return redirect()->back()->with('success', __('Invoice BankTransfer successfully deleted.'));
    }

    public function statusChange(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $status          = $request->status;
            $invoice         = Invoice::find($request->invoice_id);
            $old_status      = $invoice->status;
            $invoice->status = $status;
            $invoice->save();
            $new_status      = $invoice->status;
            $settings  = Utility::settings();
            // if (isset($settings['invoice_status_updated_notification']) && $settings['invoice_status_updated_notification'] == 1) {
            //     $msg = __('Invoice ') . \Auth::user()->invoiceNumberFormat($invoice->invoice_id) . ' ' . __('status changed from ') . __(\App\Models\Invoice::$statues[$old_status]) . __(' to ') . __(\App\Models\Invoice::$statues[$new_status]) . '.';
            //     Utility::send_slack_msg($msg);
            // }

            if (isset($settings['invoice_status_updated_notification']) && $settings['invoice_status_updated_notification'] == 1) {
                $uArr = [
                    'invoice' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                    'old_status' => $invoice->status,
                    'status' => $status,
                    ];
                //  dd($uArr);
                Utility::send_slack_msg('invoice_status', $uArr);
                }
                if (isset($settings['telegram_invoice_status_updated_notification']) && $settings['telegram_invoice_status_updated_notification'] == 1) {
                    $uArr = [
                        'invoice' => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                        'old_status' => $invoice->status,
                        'status' => $status,
                        ];
                    //  dd($uArr);
                    Utility::send_telegram_msg('invoice_status', $uArr);
                    }

            // if (isset($settings['telegram_invoice_status_updated_notification']) && $settings['telegram_invoice_status_updated_notification'] == 1) {
            //     $resp = __('Invoice ') . \Auth::user()->invoiceNumberFormat($invoice->invoice_id) . ' ' . __('status changed from ') . __(\App\Models\Invoice::$statues[$old_status]) . __(' to ') . __(\App\Models\Invoice::$statues[$new_status]) . '.';
            //     Utility::send_telegram_msg($resp);
            // }
                  //webhook
                  $module = "New invoice status";
                  $webhook = Utility::webhookSetting($module);
                  if($webhook)
                  {
                      $parameter = json_encode($status);

                      // 1 parameter is URL , 2  (invoice status Data) parameter is data , 3 parameter is method
                      $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                      if($status == true)
                      {
                          return redirect()->back()->with('success', __('Invoice status Successfully Created.'));
                      }
                      else
                      {
                          return redirect()->back()->with('error', __('Invoice status Call Failed.'));
                      }
                  }
                  //end webhook

            return redirect()->back()->with('success', __('Invoice status changed successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function pdf($id)
    {
        $settings = Utility::settings();

        $invoiceId        = Crypt::decrypt($id);
        $invoice          = Invoice::where('id', $invoiceId)->first();
        $invoice->invoice = $invoice->invoice_id;
        $data             = \DB::table('settings');
        $data             = $data->where('created_by', '=', $invoice->created_by);
        $data1            = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $client = $invoice->clients;

        $client->company_name = !empty($invoice->clientDetail) ? $invoice->clientDetail->company_name : '';
        $client->mobile       = !empty($invoice->clientDetail) ? $invoice->clientDetail->mobile : '';
        $client->address      = !empty($invoice->clientDetail) ? $invoice->clientDetail->address_1 : '';
        $client->zip          = ($invoice->clientDetail) ? $invoice->clientDetail->zip_code : '';
        $client->city         = !empty($invoice->clientDetail) ? $invoice->clientDetail->city : '';
        $client->state        = !empty($invoice->clientDetail) ? $invoice->clientDetail->state : '';
        $client->country      = !empty($invoice->clientDetail) ? $invoice->clientDetail->country : '';

        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];

        foreach ($invoice->items as $product) {
            $itemName = Item::where('id', $product->item)->first();

            $item              = new \stdClass();
            $item->name        = $itemName->name;
            $item->quantity    = $product->quantity;
            $item->tax         = $product->tax;
            $item->discount    = $product->discount;
            $item->price       = $product->price;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes = \Utility::tax($item->tax);

            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice      = \Utility::taxRate($tax->rate, $item->price, $item->quantity);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name']  = $tax->name;
                    $itemTax['rate']  = $tax->rate . '%';
                    $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                    $itemTaxes[]      = $itemTax;


                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }
            } else {
                $item->itemTax = [];
            }

            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->items         = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate     = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData     = $taxesData;
        $company_setting = Utility::settingsById($invoice->created_by);
        //Set your logo
         $logo         =  \App\Models\Utility::get_file('/');
        $settings = Utility::settings();
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' . (isset($settings['invoice_logo']) && !empty( $settings['invoice_logo']) ?   $settings['invoice_logo'] : 'logo.png'));

        // dd($invoice->invoiceCreditNote());
        if ($invoice) {
            $color      = '#' . $company_setting['invoice_color'];
            $font_color = Utility::getFontColor($color);
            $code      =  $company_setting['invoice_qrcode'];
            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'color', 'settings', 'client', 'font_color','img','code'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function previewInvoice($template, $color, $code)
    {
        // dd($template, $color);
        $objUser  = \Auth::user();
        $user = User::where('id', $objUser->id)->first();
         $settings = Utility::settings();
        $invoice  = new Invoice();

        $client               = new \stdClass();
        $client->company_name = '<Company Name>';
        $client->name         = '<Name>';
        $client->email        = '<Email>';
        $client->mobile       = '<Phone>';
        $client->address      = '<Address>';
        $client->country      = '<Country>';
        $client->state        = '<State>';
        $client->city         = '<City>';
        $invoice->invoice_id  = 'INV001';

        $totalTaxPrice = 0;
        $taxesData     = [];

        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item              = new \stdClass();
            $item->name        = 'Item ' . $i;
            $item->quantity    = 1;
            $item->tax         = 5;
            $item->discount    = 50;
            $item->price       = 100;
            $item->description = 'Item description';

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[]      = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->invoice_id = 1;
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date   = date('Y-m-d H:i:s');
        $invoice->items      = $items;

        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 3;
        $invoice->totalRate     = 300;
        $invoice->totalDiscount = 10;
        $invoice->taxesData     = $taxesData;
        $invoice->created_by = $user->creatorId();


        $preview    = 1;
        $color      = '#' . $color;
        $font_color = Utility::getFontColor($color);

      //Invoice logo perivew
      $logo = \App\Models\Utility::get_file('uploads/logo/');
      $logo_invoice=\App\Models\Utility::get_file('/');
      $dark_logo = Utility::getValByName('company_logo_dark');
      $invoice_logo = Utility::getValByName('invoice_logo');
      if(isset($invoice_logo) && !empty($invoice_logo))
      {
          $img = $logo_invoice.$invoice_logo;
      }
      else
      {
          $img = asset($logo. (isset($dark_logo) && !empty($dark_logo) ? $dark_logo : 'logo-dark.png'));
      }

        return view('invoice.templates.' . $template, compact('invoice', 'user', 'preview', 'color', 'code', 'img', 'settings', 'client', 'font_color'));
    }

    public function saveInvoiceTemplateSettings(Request $request)
    {
        $post = $request->all();
        unset($post['_token']);

        if(isset($post['invoice_template']) && (!isset($post['invoice_color']) || empty($post['invoice_color'])))
        {
            $post['invoice_color'] = "ffffff";
        }
        if(!isset($post['invoice_qrcode']) || empty($post['invoice_qrcode']))
        {
            $post['invoice_qrcode'] = "off";
        }
        // For Logo
        if ($request->invoice_logo) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'invoice_logo' => 'image',
                    ]
                );

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
            }

            $user = \Auth::user();
            $invoice_logo         = "$user->id" . '_invoice_logo.png';
            $dir = 'uploads/invoice_logo/';

            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];

            $path = Utility::upload_file($request, 'invoice_logo', $invoice_logo, $dir, $validation);
            if ($path['flag'] == 1) {
                $invoice_logo = $path['url'];
            } else {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['invoice_logo'] = $invoice_logo;
        }

        foreach ($post as $key => $data)
        {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('Invoice Setting successfully updated.'));
    }

    public function grid(Request $request)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            if (\Auth::user()->type == 'company') {
                $invoices = Invoice::where('created_by', \Auth::user()->creatorId());
            } else {
                $invoices = Invoice::where('client', \Auth::user()->id);
            }

            if (!empty($request->status)) {
                $invoices->where('status', $request->status);
            }

            if (!empty($request->start_date)) {
                $invoices->where('due_date', '>=', $request->start_date);
            }

            if (!empty($request->end_date)) {
                $invoices->where('due_date', '<=', $request->end_date);
            }

            $invoices = $invoices->get();

            $status = [
                __('Draft'),
                __('Open'),
                __('Sent'),
                __('Unpaid'),
                __('Partialy Paid'),
                __('Paid'),
            ];

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'invoice';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);

            return view('invoice.grid', compact('invoices', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function payinvoice($invoice_id)
    {
        try {
            $id =  Crypt::decrypt($invoice_id);

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Invoice Not Found.'));
        }
        if (!empty($invoice_id)) {


            $id =  Crypt::decrypt($invoice_id);
            $invoice = Invoice::where('id', $id)->first();
            $banktransfer = InvoiceBankTransfer::where('created_by',$invoice->created_by)->where('invoice_id',$id)->get();

            if (!is_null($invoice)) {

                $settings = Utility::settings();

                $items         = [];
                $totalTaxPrice = 0;
                $totalQuantity = 0;
                $totalRate     = 0;
                $totalDiscount = 0;
                $taxesData     = [];


                foreach ($invoice->items as $item) {
                    $totalQuantity += $item->quantity;
                    $totalRate     += $item->price;
                    $totalDiscount += $item->discount;
                    $taxes         = Utility::tax($item->tax);

                    $itemTaxes = [];
                    foreach ($taxes as $tax) {
                        if (!empty($tax)) {
                            $taxPrice            = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = $tax->tax_name;
                            $itemTax['tax']      = $tax->tax . '%';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if (array_key_exists($tax->name, $taxesData)) {
                                $taxesData[$itemTax['tax_name']] = $taxesData[$tax->tax_name] + $taxPrice;
                            } else {
                                $taxesData[$tax->tax_name] = $taxPrice;
                            }
                        } else {

                            $taxPrice            = Utility::taxRate(0, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = 'No Tax';
                            $itemTax['tax']      = '';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if (array_key_exists('No Tax', $taxesData)) {

                                $taxesData[$tax] = $taxesData['No Tax'] + $taxPrice;
                            } else {
                                $taxesData['No Tax'] = $taxPrice;
                            }
                        }
                    }
                    $item->itemTax = $itemTaxes;
                    $items[]       = $item;
                }
                $invoice->items         = $items;
                $invoice->totalTaxPrice = $totalTaxPrice;
                $invoice->totalQuantity = $totalQuantity;
                $invoice->totalRate     = $totalRate;
                $invoice->totalDiscount = $totalDiscount;
                $invoice->taxesData     = $taxesData;
                $company_setting = Utility::settingsById($invoice->created_by);
                $ownerId = Utility::ownerIdforInvoice($invoice->created_by);

                $payment_setting = Utility::invoice_payment_settings($ownerId);

                $site_setting = Utility::settingsById($ownerId);

                $users = User::where('id', $invoice->created_by)->first();

                if (!is_null($users)) {
                    \App::setLocale($users->lang);
                } else {
                    $users = User::where('type', 'company')->first();
                    \App::setLocale($users->lang);
                }
                $user = \Auth::user();

                if (Auth::user() != null) {
                    $objUser         = $user->creatorId();
                } else {
                    // $objUser         = Invoice::where('created_by', $invoice->created_by)->first();
                    $objUser         = $invoice->created_by;
                }
                $user_receipt = User::find($objUser);
                $user_storage = $user_receipt->storage_limit;
                $plan         = Plan::find($user_receipt->plan);
                $plan_storage = $plan->storage_limit;
            return view('invoice.invoicepay', compact('invoice','banktransfer', 'company_setting', 'users', 'payment_setting','site_setting','user_storage','plan_storage'));
            } else {
                return abort('404', 'The Link You Followed Has Expired');
            }
        } else {
            return abort('404', 'The Link You Followed Has Expired');
        }
    }

    public function pdffrominvoice($id)
    {
        $settings = Utility::settings();

        $invoiceId = Crypt::decrypt($id);
        $invoice   = Invoice::where('id', $invoiceId)->first();

        $data  = \DB::table('settings');
        $data  = $data->where('created_by', '=', $invoice->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $user         = new User();
        $user->name   = $invoice->name;
        $user->email  = $invoice->contacts;
        $user->mobile = $invoice->contacts;

        $user->bill_address = $invoice->billing_address;
        $user->bill_zip     = $invoice->billing_postalcode;
        $user->bill_city    = $invoice->billing_city;
        $user->bill_country = $invoice->billing_country;
        $user->bill_state   = $invoice->billing_state;

        $user->address = $invoice->shipping_address;
        $user->zip     = $invoice->shipping_postalcode;
        $user->city    = $invoice->shipping_city;
        $user->country = $invoice->shipping_country;
        $user->state   = $invoice->shipping_state;


        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];

        foreach ($invoice->items as $product) {
            $item           = new \stdClass();
            $item->name     = $product->item;
            $item->quantity = $product->quantity;
            $item->tax      = !empty($product->taxs) ? $product->taxs->rate : '';
            $item->discount = $product->discount;
            $item->price    = $product->price;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes     = \Utility::tax($product->tax);
            $itemTaxes = [];
            foreach ($taxes as $tax) {
                $taxPrice      = \Utility::taxRate($tax->rate, $item->price, $item->quantity);
                $totalTaxPrice += $taxPrice;

                $itemTax['name']  = $tax->tax_name;
                $itemTax['rate']  = $tax->rate . '%';
                $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                $itemTaxes[]      = $itemTax;


                if (array_key_exists($tax->tax_name, $taxesData)) {
                    $taxesData[$tax->tax_name] = $taxesData[$tax->tax_name] + $taxPrice;
                } else {
                    $taxesData[$tax->tax_name] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->items         = $items;
        $invoice->totalTaxPrice = $totalTaxPrice;
        $invoice->totalQuantity = $totalQuantity;
        $invoice->totalRate     = $totalRate;
        $invoice->totalDiscount = $totalDiscount;
        $invoice->taxesData     = $taxesData;

        //Set your logo
        $logo         =  \App\Models\Utility::get_file('uploads/logo/' . $logo);
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo.png'));

        if ($invoice) {
            $color      = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);
            $code       =  $settings['invoice_qrcode'];

            return view('invoice.templates.' . $settings['invoice_template'], compact('invoice', 'user', 'color', 'settings', 'img', 'font_color','code'));
        } else {
            return redirect()->route('pay.invoice', \Illuminate\Support\Facades\Crypt::encrypt($invoiceId))->with('error', __('Permission denied.'));
        }
    }


    public function export()
    {
        $name = 'invoice' . date('Y-m-d i:h:s');
        $data = Excel::download(new InvoiceExport(), $name . '.xlsx');
        ob_end_clean();

        return $data;
    }

    public function download($image, $extension)
    {

        return Storage::download('uploads/attachment/' . $image . '.' . $extension);
    }

    // public function productDestroy($id, $product_id)
    // {
    //     if(\Auth::user()->type == 'company')
    //     {
    //         $lead     = InvoiceProduct::find($id);
    //         dd($lead);
    //         Utility::total_quantity('plus',$products[$i]['quantity'],$invoiceProduct->product_id);
    //         return redirect()->back()->with('success', __('Products successfully deleted.'))->with('status', 'products');
    //     }
    //     else
    //     {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }


}
