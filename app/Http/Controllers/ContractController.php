<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Contract_attachment;
use App\Models\ContractComment;
use App\Models\User;
use App\Models\Item;
use App\Models\Project;
use App\Models\ContractNotes;
use App\Models\UserDefualtView;
use App\Models\Utility;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth'
            ]
        );
    }
    public function index()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            if (\Auth::user()->type == 'company') {

                $contracts   = Contract::where('created_by', '=', \Auth::user()->creatorId())->with(['types','clients','projects'])->get();
                $curr_month  = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereBetween(
                    'start_date',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = \App\Models\Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = \App\Models\Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = \App\Models\Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = \App\Models\Contract::getContractSummary($last_30days);

                return view('contract.index', compact('contracts', 'cnt_contract'));
            } elseif (\Auth::user()->type == 'client') {
                $contracts   = Contract::where('client', '=', \Auth::user()->id)->with(['types','clients','projects'])->get();
                $curr_month  = Contract::where('client', '=', \Auth::user()->id)->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('client', '=', \Auth::user()->id)->whereBetween(
                    'start_date',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Contract::where('client', '=', \Auth::user()->creatorId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = \App\Models\Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = \App\Models\Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = \App\Models\Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = \App\Models\Contract::getContractSummary($last_30days);

                return view('contract.index', compact('contracts', 'cnt_contract'));
            }

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'contract';
            $defualtView->view   = 'list';
            User::userDefualtView($defualtView);
            return view('contract.index', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {

        $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $clients       = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $clients->prepend(__('Select Client'), 0);
        $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        return view('contract.create', compact('contractTypes', 'clients', 'project'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $rules = [
                'client' => 'required',
                'subject' => 'required',
                'type' => 'required',
                'value' => 'required',
                'status' => 'Pending',
                'start_date' => 'required',
                'end_date' => 'required|date|after_or_equal:start_date',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            $contract              = new Contract();
            $contract->client      = $request->client;
            $contract->subject     = $request->subject;
            $contract->project_id  = $request->project_id;
            $contract->type        = $request->type;
            $contract->value       = $request->value;
            $contract->start_date  = $request->start_date;
            $contract->end_date    = $request->end_date;
            $contract->description = $request->description;
            $contract->created_by  = \Auth::user()->creatorId();
            $contract->save();
            $client      = User::find($request->client);
            $contractArr = [
                'contract_subject' => $request->subject,
                'contract_client' => !empty($client->name) ? $client->name : '',
                'contract_value' => \Auth::user()->priceFormat($request->value),
                'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                'contract_end_date' => \Auth::user()->dateFormat($request->end_date),
                'contract_description' => $request->description,
            ];

            // Send Email
            $resp = Utility::sendEmailTemplate('new_contract', [$client->id => $client->email], $contractArr);

            //slack
            $settings  = Utility::settings();
            if (isset($settings['contract_create_notification']) && $settings['contract_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'contract_subject' => $request->subject,
                    'contract_client' => !empty($client->name) ? $client->name : ''
                ];
                Utility::send_slack_msg('new_contract', $uArr);
            }
            //Telegram
            if (isset($settings['telegram_contract_create_notification']) && $settings['telegram_contract_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'contract_subject' => $request->subject,
                    'contract_client' => !empty($client->name) ? $client->name : ''
                ];
                Utility::send_telegram_msg('new_contract', $uArr);
            }
            //Twilio
            if (isset($settings['twilio_contract_create_notification']) && $settings['twilio_contract_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'contract_subject' => $request->subject,
                    'contract_client' => !empty($client->name) ? $client->name : ''
                ];
                Utility::send_twilio_msg('new_contract', $uArr);
            }

            // return redirect()->route('contract.index')->with('success', __('Contract successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            //webhook
            $module = "New contract";
            $webhook = Utility::webhookSetting($module);
            if ($webhook) {
                $parameter = json_encode($contract);

                // 1 parameter is URL , 2  (contract Data) parameter is data , 3 parameter is method
                $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                if ($status == true) {
                    return redirect()->back()->with('success', __('contract Successfully Created.'));
                } else {
                    return redirect()->back()->with('error', __('contract Call Failed.'));
                }
            }
            //end webhook
            return redirect()->route('contract.index')->with('success', __('Contract successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($id)
    {
        $contract = Contract::find($id);

        if ($contract->created_by == \Auth::user()->creatorId()) {
            $client   = $contract->client;
            return view('contract.show', compact('contract', 'client'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(Contract $contract)
    {
        $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $clients       = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');

        return view('contract.edit', compact('contractTypes', 'clients', 'contract', 'project'));
    }


    public function update(Request $request, Contract $contract)
    {

        if (\Auth::user()->type == 'company') {
            $rules = [
                'client' => 'required',
                'subject' => 'required',
                'type' => 'required',
                'value' => 'required',
                'status' => 'Pending',
                'start_date' => 'required',
                'end_date' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }

            $contract->client      = $request->client;
            $contract->subject     = $request->subject;
            $contract->project_id  = $request->project_id;
            $contract->type        = $request->type;
            $contract->value       = $request->value;
            $contract->start_date  = $request->start_date;
            $contract->end_date    = $request->end_date;
            $contract->description = $request->description;

            $contract->save();


            return redirect()->route('contract.index')->with('success', __('Contract successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        $contract = Contract::find($id);
        if ($contract->created_by == \Auth::user()->creatorId()) {
            $attechments = $contract->ContractAttechment()->get()->each;

            foreach ($attechments->items as $attechment) {
                if (\Storage::exists('contract_attachment/' . $attechment->files)) {
                    unlink('storage/contract_attachment/' . $attechment->files);
                }
                $attechment->delete();
            }

            $contract->ContractComment()->get()->each->delete();
            $contract->ContractNote()->get()->each->delete();
            $contract->delete();

            return redirect()->route('contract.index')->with('success', __('Contract successfully deleted!'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function description($id)
    {
        $contract = Contract::find($id);

        return view('contract.description', compact('contract'));
    }

    public function grid()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            if (\Auth::user()->type == 'company') {
                $contracts = Contract::where('created_by', '=', \Auth::user()->creatorId())->with(['types','clients'])->get();
            } else {
                $contracts = Contract::where('client', '=', \Auth::user()->id)->with(['types','clients'])->get();
            }

            $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'contract';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);
            return view('contract.grid', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    function contractNumber()
    {
        $latest = Contract::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }
    public function contract_descriptionStore($id, Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $contract        = Contract::find($id);
            $contract->contract_description = $request->contract_description;
            $contract->save();

            return redirect()->back()->with('success', __('Contact Description successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }

    public function fileUpload($id, Request $request)
    {

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client') {
            $contract = Contract::find($id);
            $request->validate(['file' => 'required']);
            $file_path = 'contract_attechment/' . $contract->file;
            $image_size = $request->file('file')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
            if ($result == 1) {
                Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);
                $files = $id . $request->file->getClientOriginalName();
                $dir = 'contract_attechment/';
                // $files = $request->file->getClientOriginalName();
                $path = Utility::upload_file($request, 'file', $files, $dir, []);
                if ($path['flag'] == 1) {
                    $file = $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

                // $request->file->storeAs('contract_attechment', $files);
                $file                 = Contract_attachment::create(
                    [
                        'contract_id' => $request->contract_id,
                        'user_id' => \Auth::user()->id,
                        'files' => $files,
                    ]
                );
            }


            $return               = [];
            $return['is_success'] = true;
            $return['download']   = route(
                'contracts.file.download',
                [
                    $contract->id,
                    $file->id,
                ]
            );

            $return['delete']     = route(
                'contracts.file.delete',
                [
                    $contract->id,
                    $file->id,
                ]
            );

            return response()->json($return);
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function fileDownload($id, $file_id)
    {

        $contract        = Contract::find($id);
        if (\Auth::user()->type == 'company') {
            $file = Contract_attachment::find($file_id);
            if ($file) {
                $file_path = storage_path('contract_attechment/' . $file->files);


                return \Response::download(
                    $file_path,
                    $file->files,
                    [
                        'Content-Length: ' . filesize($file_path),
                    ]
                );
            } else {
                return redirect()->back()->with('error', __('File is not exist.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        $contract = Contract::find($id);

        $file =  Contract_attachment::find($file_id);
        if ($file) {
            $path = storage_path('contract_attechment/' . $file->files);
            if (file_exists($path)) {
                \File::delete($path);
            }
            $file->delete();

            return redirect()->back()->with('success', __('contract file successfully deleted.'));
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('File is not exist.'),
                ],
                200
            );
        }
    }

    public function commentStore(Request $request, $id)
    {
        $contract              = new ContractComment();
        $contract->comment     = $request->comment;
        $contract->contract_id = $request->id;
        $contract->user_id     = \Auth::user()->id;
        $contract->save();



        return redirect()->back()->with('success', __('comments successfully created!') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''))->with('status', 'comments');
    }

    public function commentDestroy($id)
    {
        $contract = ContractComment::find($id);

        $contract->delete();

        return redirect()->back()->with('success', __('Comment successfully deleted!'));
    }

    public function noteStore($id, Request $request)
    {
        $contract              = Contract::find($id);
        $notes                 = new ContractNotes();
        $notes->contract_id    = $contract->id;
        $notes->notes           = $request->notes;
        $notes->user_id        = \Auth::user()->id;
        $notes->save();
        return redirect()->back()->with('success', __('Note successfully saved.'));
    }
    public function noteDestroy($id)
    {
        $contract = ContractNotes::find($id);
        $contract->delete();

        return redirect()->back()->with('success', __('Note successfully deleted!'));
    }
    public function clientwiseproject($id)

    {

        $projects = Project::where('client', $id)->get();

        $users = [];
        foreach ($projects as $key => $value) {
            $users[] = [
                'id' => $value->id,
                'name' => $value->title,
            ];
        }


        return \Response::json($users);
    }

    public function copycontract($id)
    {
        $contract = Contract::find($id);
        $clients       = User::where('type', '=', 'Client')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('title', 'id');
        $date         = $contract->start_date . ' to ' . $contract->end_date;
        $contract->setAttribute('date', $date);

        return view('contract.copy', compact('contract', 'contractTypes', 'clients', 'project'));
    }


    public function copycontractstore(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            $rules = [
                'client' => 'required',
                'subject' => 'required',
                'project_id' => 'required',
                'type' => 'required',
                'value' => 'required',
                'status' => 'Pending',
                'start_date' => 'required',
                'end_date' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            // $date = explode(' to ', $request->date);
            $contract              = new Contract();
            $contract->client      = $request->client;
            $contract->subject     = $request->subject;
            $contract->project_id  = implode(',', $request->project_id);
            $contract->type        = $request->type;
            $contract->value       = $request->value;
            $contract->start_date  = $request->start_date;
            $contract->end_date    = $request->end_date;
            $contract->description = $request->description;
            $contract->created_by  = \Auth::user()->creatorId();
            $contract->save();
            $client      = User::find($request->client);
            $contractArr = [
                'contract_subject' => $request->subject,
                'contract_client' => $client->name,
                'contract_value' => \Auth::user()->priceFormat($request->value),
                'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                'contract_end_date' => \Auth::user()->dateFormat($request->end_date),
                'contract_description' => $request->description,
            ];


            // Send Email
            $resp = Utility::sendEmailTemplate('new_contract', [$client->id => $client->email], $contractArr);

            $settings  = Utility::settings();
            // if (isset($settings['contract_create_notification']) && $settings['contract_create_notification'] == 1) {
            //     $msg = $request->subject . __(' created by ') . \Auth::user()->name . '.';
            //     Utility::send_slack_msg($msg);
            // }
            if (isset($settings['contract_create_notification']) && $settings['contract_create_notification'] == 1) {
                $uArr = [
                    'user_name' => \Auth::user()->name,
                    'contract_subject' => $contract->subject,
                    'contract_client' => $contract->client,
                    'contract_value' => $contract->value,
                    'contract_start_date' => $contract->start_date,
                    'contract_end_date' => $contract->end_date,
                ];
                Utility::send_slack_msg('new_contract', $uArr);
            }
            if (isset($settings['telegram_contract_create_notification']) && $settings['telegram_contract_create_notification'] == 1) {
                $response = $request->subject . __(' created by ') . \Auth::user()->name . '.';
                Utility::send_telegram_msg($response);
            }
            $client_namee = Client::where('user_id', $request->client)->first();
            if (isset($settings['twilio_contract_create_notification']) && $settings['twilio_contract_create_notification'] == 1) {
                $message = $request->subject . __(' created by ') . \Auth::user()->name . '.';

                Utility::send_twilio_msg($client_namee->mobile, $message);
            }
            return redirect()->route('contract.index')->with('success', __('Contract successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function preview($id)
    {
        $contract = Contract::find($id);
        return view('contract.preview', compact('contract'));
    }
    public function printContract($id)
    {
        $contract  = Contract::findOrFail($id);
        $settings = Utility::settings();

        // $client   = $contract->clients->first();
        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));


        if ($contract) {
            $color      = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);

            return view('contract.preview', compact('contract', 'color', 'img', 'settings', 'font_color'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function pdffromcontract($contract_id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($contract_id);

        $contract  = Contract::findOrFail($id);

        return view('contract.template', compact('contract'));
    }

    public function sendmailContract($id, Request $request)
    {

        $contract              = Contract::find($id);
        $contractArr = [
            'contract_id' => $contract->id,
        ];
        $client = User::find($contract->client);


        $estArr = [
            'email' => $client->email,
            'contract_subject' => $contract->subject,
            'contract_client' => $client->name,
            // 'contract_project' => $contract,
            'contract_start_date' => $contract->start_date,
            'contract_end_date' => $contract->end_date,
        ];
        // Send Email
        $resp = Utility::sendEmailTemplate('new_contract', [$client->id => $client->email], $estArr);
        return redirect()->route('contract.show', $contract->id)->with('success', __('Email Send successfully!') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

    public function signature($id)
    {
        $contract = Contract::find($id);
        return view('contract.signature', compact('contract'));
    }


    public function signatureStore(Request $request)
    {
        $contract              = Contract::find($request->contract_id);

        if (\Auth::user()->type == 'company') {
            $contract->company_signature       = $request->company_signature;
        }
        if (\Auth::user()->type == 'client') {
            $contract->client_signature       = $request->client_signature;
        }

        $contract->save();

        return response()->json(
            [
                'Success' => true,
                'message' => __('Contract Signed successfully'),
            ],
            200
        );
    }

    public function contract_status_edit(Request $request, $id)
    {

        $contract = Contract::find($id);
        $contract->status   = $request->status;
        $contract->save();
    }
}
