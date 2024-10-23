@extends('layouts.admin')
@push('script-page')
@endpush
@php
$profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    {{__('Dashboard')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Dashboard')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <!-- <li class="breadcrumb-item active" aria-current="page">{{__('Dashboard')}}</li> -->
@endsection
@section('content')
    @if(\Auth::user()->type=='company')
        <div class="row">
            @if($data['pipelines']<=0)
                <div class="col-3">
                    <div class="alert alert-danger">
                        {{__('Please add constant pipeline.')}} <a href="{{route('pipeline.index')}}"><b class="text-white">{{__('click here')}}</b></a>
                    </div>
                </div>
            @endif
            @if($data['leadStages']<=0)
                <div class="col-3">
                    <div class="alert alert-danger">
                        {{__('Please add constant lead stage.')}} <a href="{{route('leadStage.index')}}"><b class="text-white">{{__('click here')}}</b></a>
                    </div>
                </div>
            @endif
            @if($data['dealStages']<=0)
                <div class="col-3">
                    <div class="alert alert-danger">
                        {{__('Please add constant deal stage.')}} <a href="{{route('dealStage.index')}}"><b class="text-white">{{__('click here')}}</b></a>
                    </div>
                </div>
            @endif
            @if($data['projectStages']<=0)
                <div class="col-3">
                    <div class="alert alert-danger">
                        {{__('Please add constant project stage.')}} <a href="{{route('projectStage.index')}}"><b class="text-white">{{__('click here')}}</b></a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="row">
        <!-- [ sample-page ] start -->
        @if(\Auth::user()->type=='company')
            <div class="col-lg-4 col-md-6 dashboard-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-warning">
                                        <i class="ti ti-click"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Clients') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['totalClient']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='company')
                <div class="col-lg-4 col-md-6 dashboard-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mb-3 mb-sm-0">
                                    <div class="d-flex align-items-center">
                                        <div class="theme-avtar bg-success">
                                            <i class="ti ti-users"></i>
                                        </div>
                                        <div class="ms-3">
                                            <small class="text-muted">{{ __('Total') }}</small>
                                            <h6 class="m-0">{{ __('Employees') }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto text-end">
                                    <h4 class="m-0">{{$data['totalEmployee']}}</h4>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client' || \Auth::user()->type=='employee')
            <div class="col-lg-4 col-md-12 dashboard-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-danger">
                                        <i class="ti ti-list-check"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Projects') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['totalProject']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
            <div class="col-lg-4 col-md-12 dashboard-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-secondary">
                                        <i class="ti ti-layout-2"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Estimation') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['totalEstimation']}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
            <div class="col-lg-4 col-md-12 dashboard-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between">
                            <div class="col-auto mb-3 mb-sm-0">
                                <div class="d-flex align-items-center">
                                    <div class="theme-avtar bg-info">
                                        <i class="ti ti-file-invoice"></i>
                                    </div>
                                    <div class="ms-3">
                                        <small class="text-muted">{{ __('Total') }}</small>
                                        <h6 class="m-0">{{ __('Invoices') }}</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto text-end">
                                <h4 class="m-0">{{$data['totalInvoice']}}</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
        <div class="col-lg-4 col-md-12 dashboard-card">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mb-3 mb-sm-0">
                            <div class="d-flex align-items-center">
                                <div class="theme-avtar bg-dark">
                                    <i class="ti ti-report-money"></i>
                                </div>
                                <div class="ms-3">
                                    <small class="text-muted">{{ __('Total') }}</small>
                                    <h6 class="m-0">{{ __('Lead') }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <h4 class="m-0">{{$data['totalLead']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')
            <div class="col-lg-4 col-sm-6 dashboard-card">
                <div class="card card-fluid">
                    <div class="card-body">
                        <div class="col-lg-12 text-center">
                            <h5 class="mb-4">{{__('Estimation Overview')}}</h5>
                        </div>
                        <div class="progress">
                            @foreach($data['estimationOverview'] as $estimation)
                                <div class="progress-bar bg-{{$data['estimateOverviewColor'][$estimation['status']]}}" role="progressbar" style="width: {{$estimation['percentage']}}%" aria-valuenow="{{$estimation['percentage']}}" aria-valuemin="0" aria-valuemax="100"></div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            @forelse($data['estimationOverview'] as $estimation)
                                <div class="col-md-6">
                                    <span class="text-sm text-{{$data['estimateOverviewColor'][$estimation['status']]}}">●</span>
                                    <small>{{$estimation['total']}} {{__($estimation['status'])}} (<a href="#" class="text-sm text-muted">{{number_format($estimation['percentage'],'2')}}%</a>)</small>
                                </div>
                            @empty
                            <div class="col-md-12 text-center">
                                <div class="mt-3">
                                    <h6>{{__('Estimation record not found')}}</h6>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6 dashboard-card">
                <div class="card card-fluid">
                    <div class="card-body">
                        <div class="col-lg-12 text-center">
                            <h5 class="mb-4">{{__('Invoice Overview')}}</h5>
                        </div>
                        <div class="progress">
                            @foreach($data['invoiceOverview'] as $invoice)
                                <div class="progress-bar bg-{{$data['invoiceOverviewColor'][$invoice['status']]}}" role="progressbar" style="width: {{$invoice['percentage']}}%" aria-valuenow="{{$invoice['percentage']}}" aria-valuemin="0" aria-valuemax="100"></div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            @forelse($data['invoiceOverview'] as $invoice)
                                <div class="col-md-6">
                                    <span class="text-sm text-{{$data['invoiceOverviewColor'][$invoice['status']]}}">●</span>
                                    <small>{{$invoice['total']}} {{__($invoice['status'])}} (<a href="#" class="text-sm text-muted">{{number_format($invoice['percentage'],'2')}}%</a>)</small>
                                </div>
                            @empty
                            <div class="col-md-12 text-center">
                                <div class="mt-3">
                                    <h6>{{__('Invoice record not found')}}</h6>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client')

            <div class="{{(\Auth::user()->type=='employee')?'col-xl-6 col-md-6':'col-xl-4 col-md-6'}} dashboard-card">
                <div class="card card-fluid">
                    <div class="card-body">
                        <div class="col-lg-12 text-center">
                            <h5 class="mb-4">{{__('Project Overview')}}</h5>
                        </div>
                        <div class="progress">
                            @foreach($data['projects'] as $k=>$project)
                                <div class="progress-bar bg-{{$data['projectStatusColor'][$k]}}" role="progressbar" style="width: {{$project['percentage']}}%" aria-valuenow="{{$project['percentage']}}" aria-valuemin="0" aria-valuemax="100"></div>
                            @endforeach
                        </div>
                        <div class="row mt-3">
                            @forelse($data['projects'] as $k=>$project)
                                <div class="col-md-6">
                                    <span class="text-sm text-{{$data['projectStatusColor'][$k]}}">●</span>
                                    <small>{{__($project['status'])}} (<a href="#" class="text-sm text-muted">{{number_format($project['percentage'],'2')}}%</a>)</small>
                                </div>
                            @empty
                            <div class="col-md-12 text-center">
                                <div class="mt-3">
                                    <h6>{{__('Project record not found')}}</h6>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if(\Auth::user()->type=='employee')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('Mark Attendance') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6 float-right border-right">
                                {{Form::open(array('route'=>array('employee.attendance'),'method'=>'post'))}}
                                @if(empty($data['employeeAttendance']) || $data['employeeAttendance']->clock_out != '00:00:00')
                                    {{Form::submit(__('CLOCK IN'),array('class'=>'btn btn-success btn-sm','name'=>'in','value'=>'0','id'=>'clock_in'))}}
                                @else
                                    {{Form::submit(__('CLOCK IN'),array('class'=>'btn btn-success btn-sm disabled','disabled','name'=>'in','value'=>'0','id'=>'clock_in'))}}
                                @endif
                                {{Form::close()}}
                            </div>
                            <div class="col-md-6 float-left">
                                @if(!empty($data['employeeAttendance']) && $data['employeeAttendance']->clock_out == '00:00:00')
                                    {{Form::model($data['employeeAttendance'],array('route'=>array('attendance.update',$data['employeeAttendance']->id),'method' => 'PUT')) }}
                                    {{Form::submit(__('CLOCK OUT'),array('class'=>'btn btn-danger btn-sm','name'=>'out','value'=>'1','id'=>'clock_out'))}}
                                @else
                                    {{Form::submit(__('CLOCK OUT'),array('class'=>'btn btn-danger btn-sm disabled','name'=>'out','disabled','value'=>'1','id'=>'clock_out'))}}
                                @endif
                                {{Form::close()}}
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        @endif

        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client' )
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Top Due Payment')}}</h5>
                    </div>
                    <div class="card-body">

                            <div class="table-responsive">

                                    <table class="table">
                                        <tbody>
                                            @forelse($data['topDueInvoice'] as $invoice)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <a href="#" class="avatar rounded-circle">
                                                                <img alt="" @if(!empty($invoice->clients) && !empty($invoice->clients->avatar))
                                                                src="{{$profile.'/'.$invoice->clients->avatar}}" @else  avatar="{{!empty($invoice->clients)?$invoice->clients->name:''}}" @endif class="wid-25">
                                                            </a>


                                                            <div class="ms-3">
                                                                <small class="text-muted">{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</small>
                                                                <h6 class="m-0">{{__('Due Amount : ')}} {{\Auth::user()->priceFormat($invoice->getDue())}}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td width="15%">
                                                        <small class="text-muted">{{__('Due Date')}}</small>
                                                        <h6 class="m-0">{{\Auth::user()->dateFormat($invoice->due_date)}}</h6>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="action-btn bg-warning ms-2">
                                                            <a href="{{route('invoice.show',\Crypt::encrypt($invoice->id))}}" class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                                <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}" ></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <div class="col-md-12 text-center">
                                                    <div class="mt-3">
                                                        <h6>{{__('Payment record not found')}}</h6>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </tbody>
                                    </table>
                            </div>

                    </div>
                </div>
            </div>
        @endif

        @if(\Auth::user()->type=='company' || \Auth::user()->type=='client' || \Auth::user()->type=='employee')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Top Due Project')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @forelse($data['topDueProject'] as $project)
                                        <tr>
                                            <td width="40%">
                                                <div class="d-flex align-items-center">
                                                    <div class="ms-3">
                                                        <small class="text-muted">{{$project->dueTask()}} {{__('Task Remain')}}</small>
                                                        <h6 class="m-0">{{$project->title}} </h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{__('Due Date')}}</small>
                                                <h6 class="m-0">{{\Auth::user()->dateFormat($project->due_date)}}</h6>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{route('project.show',\Crypt::encrypt($project->id))}}" class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                        <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}" ></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <div class="col-md-12 text-center">
                                            <div class="mt-3">
                                                <h6>{{__('Project record not found')}}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('Top Due Task')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @forelse($data['topDueTask'] as $topDueTask)
                                        <tr>
                                            <td width="40%">
                                                <div class="d-flex align-items-center">

                                                    <div class="ms-3">
                                                        <h6 class="m-0">{{$topDueTask->title}}</h6>
                                                        <small class="text-muted">{{__('Assign to')}} {{!empty($topDueTask->taskUser)?$topDueTask->taskUser->name  :''}}</a></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{__('Project Title')}}</small>
                                                <h6 class="m-0">{{$topDueTask->project_id}}</h6>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{__('Due Date')}}</small>
                                                <h6 class="m-0">{{\Auth::user()->dateFormat($topDueTask->due_date)}}</h6>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{route('project.show',\Crypt::encrypt($topDueTask->project_id))}}" class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                        <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}" ></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <div class="col-md-12 text-center">
                                            <div class="mt-3">
                                                <h6>{{__('Task record not found')}}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(\Auth::user()->type=='company' || \Auth::user()->type == 'employee')
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Meeting Schedule')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @forelse($data['topMeeting'] as $meeting)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">

                                                <div class="ms-3">
                                                    <h6 class="m-0">{{$meeting->title}}</h6>
                                                    <small class="text-muted">{{$meeting->notes}}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">{{__('Meetign Date : ')}}</small>
                                            <h6 class="m-0">{{$meeting->date.' '.$meeting->time}}</h6>
                                        </td>
                                    </tr>
                                    @empty
                                    <div class="col-md-12 text-center">
                                        <div class="mt-3">
                                            <h6>{{__('Meeting schedule not found')}}</h6>
                                        </div>
                                    </div>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(\Auth::user()->type=='company' || \Auth::user()->type == 'employee')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('This Week Event')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @forelse($data['thisWeekEvent'] as $event)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ asset('assets/images/pages/flag.svg') }}" class="wid-25"
                                                        alt="images">
                                                    <div class="ms-3">
                                                        <h6 class="m-0">{{$event->name}}</h6>
                                                        <small class="text-muted">{{$event->description}}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{__('Start Date')}}</small>
                                                <h6 class="m-0">{{$event->start_date.' '.$event->start_time}}</h6>
                                            </td>
                                            <td class="text-end">
                                                <small class="text-muted">{{__('End Date')}}</small>
                                                <h6 class="m-0">{{$event->end_date.' '.$event->end_time}}</h6>
                                            </td>
                                        </tr>
                                        @empty
                                        <div class="col-md-12 text-center">
                                            <div class="mt-3">
                                                <h6>{{__('Event not found')}}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(\Auth::user()->type=='company')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__('New Support')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    @forelse($data['newTickets'] as $ticket)
                                        <tr>
                                            <td width="40%">
                                                <div class="d-flex align-items-center">
                                                    <img alt="" class="avatar rounded-circle wid-25"  @if(!empty($ticket->createdBy)) src="{{asset(Storage::url('uploads/avatar')).'/'.$ticket->createdBy->avatar}}" @else  avatar="{{!empty($ticket->createdBy)?$ticket->createdBy->name:''}}" @endif>

                                                    <div class="ms-3">
                                                        <h6 class="m-0">{{!empty($ticket->createdBy)?$ticket->createdBy->name:''}}</h6>
                                                        <small class="text-muted">{{$ticket->subject}}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{__('Support Date')}}</small>
                                                <h6 class="m-0">{{\Auth::user()->dateFormat($ticket->created_at)}}</h6>
                                            </td>
                                            <td width="20%">


                                                <small class="text-muted">{{__('Priority')}}</small>
                                                <h6 class="m-0">
                                                    @if($ticket->priority == 0)
                                                        <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize badge badge-primary rounded-pill badge-sm">   {{ __(\App\Models\Support::$priority[$ticket->priority]) }}</span>
                                                    @elseif($ticket->priority == 1)
                                                        <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize badge badge-info rounded-pill badge-sm">   {{ __(\App\Models\Support::$priority[$ticket->priority]) }}</span>
                                                    @elseif($ticket->priority == 2)
                                                        <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize badge badge-warning rounded-pill badge-sm">   {{ __(\App\Models\Support::$priority[$ticket->priority]) }}</span>
                                                    @elseif($ticket->priority == 3)
                                                        <span data-bs-toggle="tooltip" data-bs-original-title="{{__('Priority')}}" class="text-capitalize badge badge-danger rounded-pill badge-sm">   {{ __(\App\Models\Support::$priority[$ticket->priority]) }}</span>
                                                    @endif
                                                </h6>
                                            </td>
                                            <td>
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="{{ route('support.reply',\Crypt::encrypt($ticket->id)) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                        <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}" ></i>
                                                    </a>
                                                </div>
                                            </td>

                                        </tr>
                                        @empty
                                        <div class="col-md-12 text-center">
                                            <div class="mt-3">
                                                <h6>{{__('New support record not found')}}</h6>
                                            </div>
                                        </div>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @if(\Auth::user()->type=='company' || \Auth::user()->type =='client')
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('Contracts Expiring Soon')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @forelse ($data['contractExpirySoon'] as $contract)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img alt="" @if(!empty($contract->clients) && !empty($contract->clients->avatar)) src="{{$profile.'/'.$contract->clients->avatar}}" @else  avatar="{{!empty($contract->clients)?$contract->clients->name:''}}" @endif class="wid-25">
                                                <div class="ms-3">
                                                    <h6 class="m-0">{{!empty($contract->clients)?$contract->clients->name:'--'}}</h6>
                                                    <small class="text-muted">{{$contract->subject}}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{__('Type')}}</small>
                                            <h6 class="m-0">{{ !empty($contract->types)?$contract->types->name:'--' }}</h6>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{__('Value')}}</small>
                                            <h6 class="m-0">{{ \Auth::user()->priceFormat($contract->value) }}</h6>
                                        </td>

                                    </tr>
                                    @empty
                                    <div class="col-md-12 text-center">
                                        <div class="mt-3">
                                            <h6>{{__('Payment record not found')}}</h6>
                                        </div>
                                    </div>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(\Auth::user()->type=='company')
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>{{__('New Client')}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                @forelse($data['newClients'] as $client)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img alt="" @if(!empty($client->avatar) && !empty($client->avatar)) src="{{$profile.'/'.$client->avatar}}"
                                                @else  avatar="{{!empty($client->name)?$client->name:''}}" @endif class="wid-25">

                                                <div class="ms-3">
                                                    <h6 class="m-0">{{$client->name}}</h6>
                                                    <small class="text-muted">{{$client->email}}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <small class="text-muted">{{__('Created Date')}}</small>
                                            <h6 class="m-0">{{\Auth::user()->dateFormat($client->created_at)}}</h6>
                                        </td>
                                    </tr>
                                    @empty
                                        <div class="col-md-12 text-center">
                                            <div class="mt-3">
                                                <h6>{{__('Client record not found')}}</h6>
                                            </div>
                                        </div>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(\Auth::user()->type=='company')
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                     <h5 >{{ __('Storage Status') }} <small>({{ $users->storage_limit . 'MB' }} / {{ $plan->storage_limit . 'MB' }})</small></h5>
                </div>
                <div class="card shadow-none mb-0">
                    <div class="card-body border rounded  p-3">
                        <div id="device-chart"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

        <div class="card">
            <div class="card-header" style ="margin-left: -12px;">
                <h5>{{ __('Goals') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($data['goals'] as $goal)
                        @php
                            $data       = $goal->target($goal->goal_type,$goal->from,$goal->to,$goal->amount);
                            $total      = $data['total'];
                            $percentage = $data['percentage'];
                        @endphp
                        <div class="col-xl-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"> {{$goal->name}}</h6>
                                </div>
                                <div class="card-body ">
                                    <div class="flex-fill text-limit">
                                        <div class="row">
                                            <div class="col">
                                                <h6 class="progress-text mb-1 text-sm d-block text-limit">{{\Auth::user()->priceFormat($total).' of '. \Auth::user()->priceFormat($goal->amount)}}</h6>
                                            </div>
                                            <div class="col-auto text-end">
                                                {{ number_format($percentage, 2, '.', '')}}%
                                            </div>
                                        </div>
                                        <div class="progress progress-xs mb-0">
                                            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="{{number_format($percentage , 2, '.', '')}}" aria-valuemin="0" aria-valuemax="100" style="width: {{number_format($percentage , 2, '.', '')}}%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer py-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col-auto">
                                                    <span class="form-control-label">{{__('Type')}}:</span>
                                                </div>
                                                <div class="col text-end">
                                                    {{ __(\App\Models\Goal::$goalType[$goal->goal_type]) }}
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <div class="row align-items-center">
                                                <div class="col">
                                                    <span class="form-control-label">{{__('Duration')}}:</span>
                                                </div>
                                                <div class="col-auto text-end">
                                                    {{$goal->from .' To '.$goal->to}}
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- [ sample-page ] end -->
    </div>


@endsection

@push('script-page')
@if(\Auth::user()->type == 'company')

<script>
(function () {
    var options = {
        series: [{{ $storage_limit }}],
        chart: {
            height: 350,
            type: 'radialBar',
            offsetY: -20,
            sparkline: {
                enabled: true
            }
        },
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: "#e7e7e7",
                    strokeWidth: '97%',
                    margin: 5, // margin is in pixels
                },
                dataLabels: {
                    name: {
                        show: true
                    },
                    value: {
                        offsetY: -50,
                        fontSize: '20px'
                    }
                }
            }
        },
        grid: {
            padding: {
                top: -10
            }
        },
        colors: ["#6FD943"],
        labels: ['Used'],
    };
    var chart = new ApexCharts(document.querySelector("#device-chart"), options);
    chart.render();
})();
</script>

@endif
@endpush

