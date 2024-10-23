@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Label')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Label')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Label')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('label.create') }}"
    data-bs-whatever="{{__('Create New Label')}}"> <span class="text-white"> 
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>
    @endif
@endsection
@section('filter')
@endsection
@section('content')

<div class="row justify-content-center">
    <div class="col-sm-12 col-md-10 col-xxl-8">
        <div class="p-3 card">
            <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                @php($i=0)
                @foreach($pipelines as $key => $pipeline)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($i==0) active @endif" id="pills-user-tab-1" data-bs-toggle="pill"
                                data-bs-target="#tab{{$key}}" type="button">{{$pipeline['name']}}
                        </button>
                    </li>
                    @php($i++)
                @endforeach
            </ul>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    @php($i=0)
                    @forelse($pipelines as $key => $pipeline)
                        <div class="tab-pane fade show @if($i==0) active @endif" id="tab{{$key}}" role="tabpanel" aria-labelledby="pills-user-tab-1">
                            <ul class="list-group sortable">
                                @foreach ($pipeline['labels'] as $label)
                                    <li class="list-group-item" data-id="{{$label->id}}">
                                        <span class="badge fix_badges bg-{{$label->color}} p-2 px-3 rounded">{{$label->name}}</span>
                                        @if(\Auth::user()->type=='company')
                                        <span class="float-end">
                                           
                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" 
                                                    data-url="{{ route('label.edit',$label->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"  data-bs-whatever="{{__('Edit Lead Stage')}}" 
                                                    data-size="md">
                                                        <i class="ti ti-edit text-white"  data-bs-toggle="tooltip" title="{{__('Edit')}}"></i>
                                                    </a>
                                                </div>
                                         
                                            
                                                <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['label.destroy', $label->id]]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                </div>
                                           
                                        </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @php($i++)
                        @empty
                        <div class="col-md-12 text-center">
                            <h4>{{__('No data available')}}</h4>
                        </div>
                    @endforelse

                  
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection

