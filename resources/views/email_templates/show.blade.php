
@extends('layouts.admin')
@section('page-title')
    {{ $emailTemplate->name }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ $emailTemplate->name }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Email Template') }}</li>
@endsection

@push('pre-purpose-css-page')
<link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush

@push('script-page')
<script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>

@endpush

@section('action-btn')

    <div class="col-lg-12">
            <div class="text-end ">
                <div class="d-flex justify-content-end drp-languages">
                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            id="dropdownLanguage">
                                <span class="drp-text hide-mob text-primary">{{ ucfirst($currEmailTempLang->lang) }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end"
                                aria-labelledby="dropdownLanguage">
                                @foreach ($languages as $code => $lang)

                                    <a href="{{ route('manage.email.language', [$emailTemplate->id, $code]) }}"
                                    class="dropdown-item {{ $currEmailTempLang->lang == $lang ? 'text-primary' : '' }}">{{ Str::upper($lang) }}</a>
                                @endforeach
                            </div>
                        </li>
                    </ul>

                    <ul class="list-unstyled mb-0 m-2">
                        <li class="dropdown dash-h-item drp-language">
                            <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false"
                            id="dropdownLanguage">
                                <span
                                    class="drp-text hide-mob text-primary">{{ __('Template: ') }}{{ $emailTemplate->name }}</span>
                                <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                            </a>
                            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end" aria-labelledby="dropdownLanguage">
                                @foreach ($EmailTemplates as $EmailTemplate)
                                    <a href="{{ route('manage.email.language', [$EmailTemplate->id,(Request::segment(3)?Request::segment(3):\Auth::user()->lang)]) }}"
                                    class="dropdown-item {{$emailTemplate->name == $EmailTemplate->name ? 'text-primary' : '' }}">{{ $EmailTemplate->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body ">
                    <div>
                      <h6>{{ __('Place Holders') }}</h6>&nbsp;
                    </div>
                     <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="card">
                                    <div class="card-header card-body">

                                        <div class="row text-xs">
                                            @if($emailTemplate->slug=='new_user')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Create User')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Email')}} : <span class="pull-right text-primary">{email}</span></p>
                                                    <p class="col-4">{{__('Password')}} : <span class="pull-right text-primary">{password}</span></p>
                                                </div>
                                                @elseif($emailTemplate->slug=='lead_assigned')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Lead Assign')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Lead Name')}} : <span class="pull-right text-primary">{lead_name}</span></p>
                                                    <p class="col-4">{{__('Lead Email')}} : <span class="pull-right text-primary">{lead_email}</span></p>
                                                    <p class="col-4">{{__('Lead Subject')}} : <span class="pull-right text-primary">{lead_subject}</span></p>
                                                    <p class="col-4">{{__('Lead Pipeline')}} : <span class="pull-right text-primary">{lead_pipeline}</span></p>
                                                    <p class="col-4">{{__('Lead Stage')}} : <span class="pull-right text-primary">{lead_stage}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='deal_assigned')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Deal Assign')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Deal Name')}} : <span class="pull-right text-primary">{deal_name}</span></p>
                                                    <p class="col-4">{{__('Deal Pipeline')}} : <span class="pull-right text-primary">{deal_pipeline}</span></p>
                                                    <p class="col-4">{{__('Deal Stage')}} : <span class="pull-right text-primary">{deal_stage}</span></p>
                                                    <p class="col-4">{{__('Deal Status')}} : <span class="pull-right text-primary">{deal_status}</span></p>
                                                    <p class="col-4">{{__('Deal Price')}} : <span class="pull-right text-primary">{deal_price}</span></p>
                                                    <p class="col-4">{{__('Deal Stage')}} : <span class="pull-right text-primary">{deal_stage}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='estimation_sent')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Send Estimation')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Estimation Name')}} : <span class="pull-right text-primary">{estimation_id}</span></p>
                                                    <p class="col-4">{{__('Estimation Client')}} : <span class="pull-right text-primary">{estimation_client}</span></p>
                                                    <p class="col-4">{{__('Estimation Category')}} : <span class="pull-right text-primary">{estimation_category}</span></p>
                                                    <p class="col-4">{{__('Estimation Issue Date')}} : <span class="pull-right text-primary">{estimation_issue_date}</span></p>
                                                    <p class="col-4">{{__('Estimation Expiry Date')}} : <span class="pull-right text-primary">{estimation_expiry_date}</span></p>
                                                    <p class="col-4">{{__('Estimation Status')}} : <span class="pull-right text-primary">{estimation_status}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='new_project')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Create Project')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Project Title')}} : <span class="pull-right text-primary">{project_title}</span></p>
                                                    <p class="col-4">{{__('Project Category')}} : <span class="pull-right text-primary">{project_category}</span></p>
                                                    <p class="col-4">{{__('Project Price')}} : <span class="pull-right text-primary">{project_price}</span></p>
                                                    <p class="col-4">{{__('Project Client')}} : <span class="pull-right text-primary">{project_client}</span></p>
                                                    <p class="col-4">{{__('Project Assign User')}} : <span class="pull-right text-primary">{project_assign_user}</span></p>
                                                    <p class="col-4">{{__('Project Start Date')}} : <span class="pull-right text-primary">{project_start_date}</span></p>
                                                    <p class="col-4">{{__('Project Due Date')}} : <span class="pull-right text-primary">{project_due_date}</span></p>
                                                    <p class="col-4">{{__('Project Lead')}} : <span class="pull-right text-primary">{project_lead}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='project_assigned')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Project Assign')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Project Assign User')}} : <span class="pull-right text-primary">{project_assign_user}</span></p>
                                                    <p class="col-4">{{__('Project Start Date')}} : <span class="pull-right text-primary">{project_start_date}</span></p>
                                                    <p class="col-4">{{__('Project Due Date')}} : <span class="pull-right text-primary">{project_due_date}</span></p>

                                                </div>
                                            @elseif($emailTemplate->slug=='project_finished')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Project Finished')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Project')}} : <span class="pull-right text-primary">{project}</span></p>
                                                    <p class="col-4">{{__('Project Client')}} : <span class="pull-right text-primary">{project_client}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='task_assigned')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Task Assign')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Project')}} : <span class="pull-right text-primary">{project}</span></p>
                                                    <p class="col-4">{{__('Task Title')}} : <span class="pull-right text-primary">{task_title}</span></p>
                                                    <p class="col-4">{{__('Task Priority')}} : <span class="pull-right text-primary">{task_priority}</span></p>
                                                    <p class="col-4">{{__('Task Start Date')}} : <span class="pull-right text-primary">{task_start_date}</span></p>
                                                    <p class="col-4">{{__('Task Due Date')}} : <span class="pull-right text-primary">{task_due_date}</span></p>
                                                    <p class="col-4">{{__('Task Stage')}} : <span class="pull-right text-primary">{task_stage}</span></p>
                                                    <p class="col-4">{{__('Task Assign User')}} : <span class="pull-right text-primary">{task_assign_user}</span></p>
                                                    <p class="col-4">{{__('Task Description')}} : <span class="pull-right text-primary">{task_description}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='invoice_sent' || $emailTemplate->slug=='invoice_payment_recorded')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Send Invoice')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Invoice Number')}} : <span class="pull-right text-primary">{invoice_id}</span></p>
                                                    <p class="col-4">{{__('Invoice Client')}} : <span class="pull-right text-primary">{invoice_client}</span></p>
                                                    <p class="col-4">{{__('Invoice Issue Date')}} : <span class="pull-right text-primary">{invoice_issue_date}</span></p>
                                                    <p class="col-4">{{__('Invoice Due Date')}} : <span class="pull-right text-primary">{invoice_due_date}</span></p>
                                                    <p class="col-4">{{__('Invoice Status')}} : <span class="pull-right text-primary">{invoice_status}</span></p>
                                                    <p class="col-4">{{__('Invoice Total')}} : <span class="pull-right text-primary">{invoice_total}</span></p>
                                                    <p class="col-4">{{__('Invoice Sub Total')}} : <span class="pull-right text-primary">{invoice_sub_total}</span></p>
                                                    <p class="col-4">{{__('Invoice Due Amount')}} : <span class="pull-right text-primary">{invoice_due_amount}</span></p>
                                                    <p class="col-4">{{__('Invoice Status')}} : <span class="pull-right text-primary">{invoice_status}</span></p>
                                                    <p class="col-4">{{__('Invoice Payment Recorded Total')}} : <span class="pull-right text-primary">{payment_total}</span></p>
                                                    <p class="col-4">{{__('Invoice Payment Recorded Date')}} : <span class="pull-right text-primary">{payment_date}</span></p>

                                                </div>
                                            @elseif($emailTemplate->slug=='new_credit_note')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Credit Note')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Invoice Number')}} : <span class="pull-right text-primary">{invoice_id}</span></p>
                                                    <p class="col-4">{{__('Date')}} : <span class="pull-right text-primary">{credit_note_date}</span></p>
                                                    <p class="col-4">{{__('Invoice Client')}} : <span class="pull-right text-primary">{invoice_client}</span></p>
                                                    <p class="col-4">{{__('Amount')}} : <span class="pull-right text-primary">{credit_amount}</span></p>
                                                    <p class="col-4">{{__('Description')}} : <span class="pull-right text-primary">{credit_description}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='new_support_ticket')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Create Support')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Ticket Title')}} : <span class="pull-right text-primary">{support_title}</span></p>
                                                    <p class="col-4">{{__('Ticket Assign User')}} : <span class="pull-right text-primary">{assign_user}</span></p>
                                                    <p class="col-4">{{__('Ticket Priority')}} : <span class="pull-right text-primary">{support_priority}</span></p>
                                                    <p class="col-4">{{__('Ticket End Date')}} : <span class="pull-right text-primary">{support_end_date}</span></p>
                                                    <p class="col-4">{{__('Ticket Description')}} : <span class="pull-right text-primary">{support_description}</span></p>
                                                </div>
                                            @elseif($emailTemplate->slug=='new_contract')
                                                <div class="row">
                                                    <h6 class="font-weight-bold pb-3">{{__('Create Contract')}}</h6>
                                                    <p class="col-4">{{__('App Name')}} : <span class="pull-end text-primary">{app_name}</span></p>
                                                    <p class="col-4">{{__('Company Name')}} : <span class="pull-right text-primary">{company_name}</span></p>
                                                    <p class="col-4">{{__('App Url')}} : <span class="pull-right text-primary">{app_url}</span></p>
                                                    <p class="col-4">{{__('Contract Subject')}} : <span class="pull-right text-primary">{contract_subject}</span></p>
                                                    <p class="col-4">{{__('Contract Client')}} : <span class="pull-right text-primary">{contract_client}</span></p>
                                                    <p class="col-4">{{__('Contract Project')}} : <span class="pull-right text-primary">{contract_project}</span></p>
                                                    <p class="col-4">{{__('Contract Start Date')}} : <span class="pull-right text-primary">{contract_start_date}</span></p>
                                                    <p class="col-4">{{__('Contract End Date')}} : <span class="pull-right text-primary">{contract_end_date}</span></p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                        </div>
                    {{Form::model($currEmailTempLang, array('route' => array('email_template.update', $currEmailTempLang->parent_id), 'method' => 'PUT')) }}
                        <div class="row">
                            <div class="form-group col-6">
                                {{ Form::label('subject', __('Subject'), ['class' => 'col-form-label text-dark']) }}
                                {{ Form::text('subject', null, ['class' => 'form-control font-style', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-md-6">
                                {{ Form::label('from', __('From'), ['class' => 'col-form-label text-dark']) }}
                                {{ Form::text('from', $emailTemplate->from, ['class' => 'form-control font-style', 'required' => 'required']) }}
                            </div>
                            <div class="form-group col-12">
                                {{Form::label('content',__('Email Message'),['class'=>'form-label text-dark'])}}
                                {{Form::textarea('content',$currEmailTempLang->content,array('class'=>'summernote-simple','required'=>'required'))}}
                            </div>
                        </div>
                        <hr>
                        <div class="col-md-12 text-end">
                            {{Form::hidden('lang',null)}}
                            <input type="submit" value="{{__('Save Changes')}}" class="btn btn-print-invoice  btn-primary m-r-10">
                        </div>

                    {{ Form::close() }}

                </div>
            </div>
        </div>
    </div>

@endsection
