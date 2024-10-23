@extends('layouts.admin')
@push('script-page')
    <script>
        $(document).on('click', '.type', function() {
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
    {{ __('Notice Board') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Notice Board') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Notice Board') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'company')
        <a href="{{ route('noticeBoard.grid') }}" class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-layout-grid text-white" data-bs-toggle="tooltip"
                data-bs-original-title="{{ __('Grid VIew') }}"></i>
        </a>

        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('noticeBoard.create') }}" data-bs-whatever="{{ __('Create New Notice Board') }}"> <span
                class="text-white">
                <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
    @endif
@endsection

@section('content')
    <div class="col-xl-12">
        <div class=" {{ isset($_GET['type']) ? 'show' : '' }}">
            <div class="card card-body">
                {{ Form::open(['url' => 'noticeBoard', 'method' => 'get']) }}
                <div class="row filter-css">
                    <div class="col-md-2">
                        <select class="form-control" data-toggle="select" name="type">
                            <option value="0">{{ __('Select Type') }}</option>
                            <option value="{{ __('Employee') }}"
                                {{ isset($_GET['type']) && $_GET['type'] == 'Employee' ? 'selected' : '' }}>{{ __('Employee') }}
                            </option>
                            <option value="{{ __('Client') }}"
                                {{ isset($_GET['type']) && $_GET['type'] == 'Client' ? 'selected' : '' }}>{{ __('Client') }}
                            </option>
                        </select>
                    </div>
                    <div class="action-btn bg-info ms-2">
                        <div class="col-auto">
                            <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip"
                                data-title="{{ __('Apply') }}"><i class="ti ti-search text-white"
                                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('Apply') }}"></i></button>
                        </div>
                    </div>
                    <div class="action-btn bg-danger ms-2">
                        <div class="col-auto">
                            <a href="{{ route('noticeBoard.index') }}" data-toggle="tooltip"
                                data-title="{{ __('Reset') }}" class="mx-3 btn btn-sm d-flex align-items-center"><i
                                    class="ti ti-trash-off text-white" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Reset') }}"></i></a>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>


    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('Notice') }}</th>
                                <th scope="col">{{ __('Date') }}</th>
                                <th scope="col">{{ __('To') }}</th>
                                <th scope="col">{{ __('Department') }}</th>
                                <th scope="col">{{ __('Descrition') }}</th>
                                @if (\Auth::user()->type == 'company')
                                    <th scope="col" class="text-right">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($noticeBoards as $noticeBoard)
                                <tr>
                                    <td>{{ $noticeBoard->heading }}</td>
                                    <td>{{ \Auth::user()->dateFormat($noticeBoard->created_at) }}</td>
                                    <td>{{ $noticeBoard->type }}</td>
                                    <td>{{ $noticeBoard->type != 'Client' ? ($noticeBoard->type == 'Employee' && !empty($noticeBoard->departments) ? $noticeBoard->departments->name : __('All')) : '-' }}
                                    </td>
                                    <td style="white-space: inherit">{{ $noticeBoard->notice_detail }}</td>
                                    @if (\Auth::user()->type == 'company')
                                        <td class="text-right">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                    data-url="{{ route('noticeBoard.edit', $noticeBoard->id) }}"
                                                    data-bs-whatever="{{ __('Edit Notice Board') }}"> <span
                                                        class="text-white"> <i class="ti ti-edit" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                            </div>

                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['noticeBoard.destroy', $noticeBoard->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
