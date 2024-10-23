@extends('layouts.admin')
@push('script-page')
    <script>
        $(document).on('click', '.type', function () {
            var type = $(this).val();
            if (type == 'Employee') {
                $('.department').addClass('d-block');
                $('.department').removeClass('d-none')
            } else {
                $('.department').addClass('d-none')
                $('.department').removeClass('d-block');
            }
        });
    </script>
@endpush
@section('page-title')
    {{__('Notice Board')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Notice Board')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Notice Board')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')


    <a href="{{ route('noticeBoard.index') }}" class="btn btn-sm btn-primary btn-icon m-1" >
        <i class="ti ti-list text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}"></i>
    </a>

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
        data-bs-target="#exampleModal" data-url="{{ route('noticeBoard.create') }}"
        data-bs-whatever="{{__('Create New Notice Board')}}"> <span class="text-white"> 
            <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
    </a>

    @endif
@endsection

@section('content')
 
        <div class="col-12">
            <div class=" {{isset($_GET['type'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('noticeBoard.grid'),'method'=>'get')) }}
                    <div class="row filter-css">
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="type">
                                <option value="0">{{__('Select Type')}}</option>
                                <option value="{{__('Employee')}}" {{isset($_GET['type']) && $_GET['type']=='employee'?'selected':''}}>{{__('Employee')}}</option>
                                <option value="{{__('Client')}}" {{isset($_GET['type']) && $_GET['type']=='client'?'selected':''}}>{{__('Client')}}</option>
                            </select>
                        </div>
                        <div class="action-btn bg-info ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip" 
                                data-title="{{__('Apply')}}"><i class="ti ti-search text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Apply') }}"></i></button>
                            </div>
                        </div>
                        <div class="action-btn bg-danger ms-2">
                            <div class="col-auto">
                                <a href="{{route('noticeBoard.index')}}" data-toggle="tooltip" data-title="{{__('Reset')}}" 
                                class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Reset') }}"></i></a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

    <div class="row">
        @foreach($noticeBoards as $noticeBoard)
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">{{$noticeBoard->heading}}</h6>
                        </div>
                        <div class="text-right">
                            <div class="actions">
                                <div class="dropdown action-item">
                                    <a href="#" class="action-item" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        
                                            <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('noticeBoard.edit',$noticeBoard->id) }}"
                                                data-bs-whatever="{{__('Edit Notice Board')}}"> 
                                                <i class="ti ti-edit"></i>{{__('Edit')}}</a>

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['noticeBoard.destroy', $noticeBoard->id]]) !!}
                                                <a href="#!" class=" show_confirm dropdown-item">
                                                    <i class="ti ti-trash"></i>{{ __('Delete') }}
                                                </a>
                                                {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-justify">{{$noticeBoard->notice_detail}}</p>
                    <div class="media align-items-center mt-2">
                        <div class="media-body">
                            <span class="h6 mb-0">{{__('Date')}}</span>
                            <span class="text-sm text-muted">{{\Auth::user()->dateFormat($noticeBoard->created_at)}}</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <ul class="list-inline">
                            <li class="list-inline-item pr-3" data-toggle="tooltip" data-title="{{__('Assign to')}}">
                                <span class="badge bg-info fix_badge p-2 px-3 rounded">{{$noticeBoard->type}}</span>
                            </li>
                            <li class="list-inline-item pr-3" data-toggle="tooltip" data-title="{{__('Department')}}">
                                <span class=" badge bg-success fix_badge p-2 px-3 rounded">{{($noticeBoard->type!='Client') ?($noticeBoard->type=='Employee' && !empty($noticeBoard->departments)?$noticeBoard->departments->name:__('All')):'-'}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endsection

