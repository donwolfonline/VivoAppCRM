@extends('layouts.admin')
@section('page-title')
    {{__('Timeline')}}
@endsection
@push('css-page')
@endpush
@push('script-page')
@endpush
@section('breadcrumb')
    <h6 class="h2 d-inline-block mb-0">{{__('Timeline')}}</h6>
    <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
        <ol class="breadcrumb breadcrumb-links">
            <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{__('Timeline')}}</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('file', __('File')) }}
                                    <input type="file" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('description', __('Description')) }}
                                    {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">

                                    {{Form::submit(__('Create'),array('class'=>'btn btn-primary'))}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">

                            <div class="card-body">
                                <div class="timeline timeline-one-side" data-timeline-content="axis" data-timeline-axis-style="dashed">
                                    <div class="timeline-block">
                                      <span class="timeline-step badge-success">
                                        <i class="ni ni-bell-55"></i>
                                      </span>
                                        <div class="timeline-content">
                                            <small class="text-muted font-weight-bold">10:30 AM</small>
                                            <h5 class=" mt-3 mb-0">New message</h5>
                                            <p class=" text-sm mt-1 mb-0">Nullam id dolor id nibh ultricies vehicula ut id elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                                            <div class="mt-3">
                                                <span class="badge badge-pill badge-success">design</span>
                                                <span class="badge badge-pill badge-success">system</span>
                                                <span class="badge badge-pill badge-success">creative</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-block">
                  <span class="timeline-step badge-danger">
                    <i class="ni ni-html5"></i>
                  </span>
                                        <div class="timeline-content">
                                            <small class="text-muted font-weight-bold">10:30 AM</small>
                                            <h5 class=" mt-3 mb-0">Product issue</h5>
                                            <p class=" text-sm mt-1 mb-0">Nullam id dolor id nibh ultricies vehicula ut id elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                                            <div class="mt-3">
                                                <span class="badge badge-pill badge-danger">design</span>
                                                <span class="badge badge-pill badge-danger">system</span>
                                                <span class="badge badge-pill badge-danger">creative</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-block">
                  <span class="timeline-step badge-info">
                    <i class="ni ni-like-2"></i>
                  </span>
                                        <div class="timeline-content">
                                            <small class="text-muted font-weight-bold">10:30 AM</small>
                                            <h5 class=" mt-3 mb-0">New likes</h5>
                                            <p class=" text-sm mt-1 mb-0">Nullam id dolor id nibh ultricies vehicula ut id elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                                            <div class="mt-3">
                                                <span class="badge badge-pill badge-info">design</span>
                                                <span class="badge badge-pill badge-info">system</span>
                                                <span class="badge badge-pill badge-info">creative</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-block">
                  <span class="timeline-step badge-success">
                    <i class="ni ni-bell-55"></i>
                  </span>
                                        <div class="timeline-content">
                                            <small class="text-muted font-weight-bold">10:30 AM</small>
                                            <h5 class=" mt-3 mb-0">New message</h5>
                                            <p class=" text-sm mt-1 mb-0">Nullam id dolor id nibh ultricies vehicula ut id elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                                            <div class="mt-3">
                                                <span class="badge badge-pill badge-success">design</span>
                                                <span class="badge badge-pill badge-success">system</span>
                                                <span class="badge badge-pill badge-success">creative</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="timeline-block">
                  <span class="timeline-step badge-danger">
                    <i class="ni ni-html5"></i>
                  </span>
                                        <div class="timeline-content">
                                            <small class="text-muted font-weight-bold">10:30 AM</small>
                                            <h5 class=" mt-3 mb-0">Product issue</h5>
                                            <p class=" text-sm mt-1 mb-0">Nullam id dolor id nibh ultricies vehicula ut id elit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.</p>
                                            <div class="mt-3">
                                                <span class="badge badge-pill badge-danger">design</span>
                                                <span class="badge badge-pill badge-danger">system</span>
                                                <span class="badge badge-pill badge-danger">creative</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

