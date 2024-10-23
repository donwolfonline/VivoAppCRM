@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Training Detail')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-nline-block font-weight-400 mb-0">{{__('Training Detail')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('training.index')}}">{{__('Training')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Employee')}}</li>
@endsection
@section('action-btn')

@endsection
@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card card-fluid">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <a href="#!" class="d-block h6 mb-0">{{ !empty($training->trainers)?$training->trainers->firstname:'-' }}</a>
                            <small class="d-block text-muted">{{ !empty($training->types)?$training->types->name:'' }}</small>
                        </div>
                        <div class="col text-right">
                            <span class="h6 mb-0">{{__('Training Cost')}}</span>
                            <span class="d-block text-sm">{{\Auth::user()->priceFormat($training->training_cost)}}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <span class="h6 mb-0">{{__('Start Date')}}</span>
                            <span class="d-block text-sm">{{\Auth::user()->dateFormat($training->start_date)}}</span>
                        </div>

                        <div class="col text-right">
                            <span class="h6 mb-0">{{\Auth::user()->dateFormat($training->end_date)}}</span>
                            <span class="d-block text-sm">{{__('End Date')}}</span>
                        </div>

                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <p>{{$training->description}}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card card-fluid">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <a href="#" class="avatar rounded-circle">
                                <img height="60px" width="60px" @if(!empty($training->employees) && !empty($training->employees->avatar)) src="{{asset(Storage::url('uploads/avatar')).'/'.$training->employees->avatar}}" @else avatar="{{ !empty($training->employees)?$training->employees->name:'' }}" @endif class="avatar  rounded-circle">
                            </a>
                        </div>
                        <div class="col ml-md-n2">
                            <a href="{{route('employee.show',!empty($training->employees)?\Illuminate\Support\Facades\Crypt::encrypt($training->employees->id):0)}}" class="d-block h6 mb-0">
                                {{ !empty($training->employees)?$training->employees->name:'' }}
                            </a>

                            <small class="d-block text-muted"> {{ !empty($training->employees)?!empty($training->employees->designation)?$training->employees->designation->name:'':'' }}</small>
                        </div>
                    </div>
                </div>
                {{Form::model($training,array('route' => array('training.status', $training->id), 'method' => 'post')) }}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="hidden" value="{{$training->id}}" name="id">
                            <div class="form-group">
                                {{Form::label('performance',__('Performance'),['class' => "form-label"])}}
                                {{Form::select('performance',$performance,null,array('class'=>'form-control multi-select'))}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('status',__('Status'),['class' => "form-label"])}}
                                {{Form::select('status',$status,null,array('class'=>'form-control multi-select'))}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('remarks',__('Remarks') ,['class' => "form-label"])}}
                                {{Form::textarea('remarks',null,array('class'=>'form-control','rows'=>2,'placeholder'=>__('Remarks')))}}
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer me-2 mb-2">
                    {{ Form::submit(__('Update'), ['class' => 'btn  btn-primary']) }}
                </div>
                {{Form::close()}}
            </div>
        </div>
    </div>
@endsection

