@extends('layouts.admin')
@section('page-title')
    {{ __('Project Create') }}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Project Create') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('project.index') }}">{{ __('Project') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Create') }}</li>
@endsection
@section('content')
    {{ Form::open(['url' => 'project', 'class' => 'mt-4']) }}
    <div class="card">
        <div class="card-body">
            @php
                $plansettings = App\Models\Utility::plansettings();
            @endphp
            <div class="row">
                @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
                    <div class="text-end">
                        <a href="#" data-size="lg" data-ajax-popup-over="true"
                            data-url="{{ route('generate', ['project']) }}" data-bs-placement="top"
                            title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
                            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">
                                    {{ __('Generate With AI') }}</span></i>
                        </a>
                    </div>
                @endif
                <div class="form-group col-md-4">
                    {{ Form::label('title', __('Project Title'), ['class' => 'form-label']) }}
                    {{ Form::text('title', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Project Title')]) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('category', __('Category'), ['class' => 'form-label']) }}
                    {{ Form::select('category', $categories, '', ['class' => 'form-control multi-select']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('price', __('Price'), ['class' => 'form-label']) }}
                    {{ Form::number('price', null, ['class' => 'form-control', 'required' => 'required', 'stage' => '0.01', 'placeholder' => __('Price')]) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                    {{ Form::date('start_date', new \DateTime(), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                    {{ Form::date('due_date', new \DateTime(), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('lead', __('Lead'), ['class' => 'form-label']) }}
                    {{ Form::select('lead', $leads, null, ['class' => 'form-control multi-select']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('client', __('Client'), ['class' => 'form-label']) }}
                    {{ Form::select('client', $clients, '', ['class' => 'form-control multi-select']) }}
                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}
                    {{ Form::select('employee[]', $employees, null, ['class' => 'form-control multi-select', 'id' => 'choices-multiple', 'multiple' => '', 'required' => 'required']) }}

                </div>
                <div class="form-group col-md-4">
                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                    {{ Form::select('status', $projectStatus, null, ['class' => 'form-control multi-select', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-12">
                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Description')]) }}
                </div>
                <div class="modal-footer pr-0">
                    <input type="button" value="{{__('Close')}}" onclick="location.href = '{{route("project.index")}}';" class="btn btn-light">
                    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
