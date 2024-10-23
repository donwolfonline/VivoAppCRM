@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@section('page-title')
    {{ __('Zoom Meeting') }}
@endsection

@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Zoom Meeting') }}</h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Zoom Meeting') }}</li>
@endsection

@push('css-page')
    <!-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/daterangepicker.css') }}"> -->
@endpush

@section('action-btn')
    @if (\Auth::user()->type == 'company')
        <a href="{{ route('zoommeeting.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
            title="Calendar View">
            <i class="ti ti-calendar text-white"></i>
        </a>

        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-size="lg"
            data-bs-target="#exampleModal" data-url="{{ route('zoommeeting.create') }}"
            data-bs-whatever="{{ __('Create New Zoom Meeting') }}"> <span class="text-white">
                <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
    @endif
@endsection

@section('content')
    <div class="page-content">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header card-body table-border-style">
                    <!-- <h5></h5> -->
                    <div class="table-responsive">
                        <table class="table" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th> {{ __('TITLE') }} </th>

                                    <th> {{ __('PROJECT') }} </th>
                                    @if (\Auth::user()->type == 'employee' || \Auth::user()->type == 'company')
                                        <th> {{ __('CLIENT') }} </th>
                                    @endif
                                    @if (\Auth::user()->type == 'company')
                                        <th> {{ __('EMPLOYEE') }} </th>
                                    @endif
                                    <th> {{ __('MEETING TIME') }} </th>
                                    <th> {{ __('DURATION') }} </th>
                                    <th> {{ __('JOIN URL') }} </th>
                                    <th> {{ __('STATUS') }} </th>
                                    @if (\Auth::user()->type == 'company')
                                        <th class="text-right"> {{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if (\Auth::user()->type != 'super admin')
                                    @forelse ($meetings as $item)
                                        <tr>
                                            <td>{{ $item->title }}</td>
                                            <td>{{ !empty($item->projectName) ? $item->projectName->title : '' }}</td>
                                            @if (\Auth::user()->type == 'employee' || \Auth::user()->type == 'company')
                                                <td>{{ !empty($item->projectClient) ? $item->projectClient->name : '' }}</td>
                                            @endif
                                            @if (\Auth::user()->type == 'company')
                                                <td class="user-group1">
                                                    @foreach (explode(',', $item->employee) as $projectUser)
                                                        <img @if (!empty($getUsersData[$projectUser]['avatar'])) src="{{ $profile . '/' . $getUsersData[$projectUser]['avatar'] }}" @else avatar="{{ !empty($projectUser) ? $getUsersData[$projectUser]['name'] : '' }}" @endif
                                                            data-original-title="{{ !empty($projectUser) ? $getUsersData[$projectUser]['name'] : '' }}"
                                                            data-toggle="tooltip"
                                                            data-original-title="{{ !empty($projectUser) ? $getUsersData[$projectUser]['name'] : '' }}"
                                                            class="">
                                                    @endforeach
                                                </td>
                                            @endif
                                            <td>{{ $item->start_date }}</td>
                                            <td>{{ $item->duration }} {{ __('Minutes') }}</td>
                                            <td>
                                                @if ($item->created_by == \Auth::user()->id && $item->checkDateTime())
                                                    <a href="{{ $item->start_url }}" target="_blank">
                                                        {{ __('Start meeting') }} <i
                                                            class="fas fa-external-link-square-alt "></i></a>
                                                @elseif($item->checkDateTime())
                                                    <a href="{{ $item->join_url }}" target="_blank">
                                                        {{ __('Join meeting') }} <i
                                                            class="fas fa-external-link-square-alt "></i></a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($item->checkDateTime())
                                                    @if ($item->status == 'waiting')
                                                        <span
                                                            class="badge bg-info p-2 px-3 rounded">{{ ucfirst($item->status) }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-success p-2 px-3 rounded">{{ ucfirst($item->status) }}</span>
                                                    @endif
                                                @else
                                                    <span
                                                        class="badge bg-danger p-2 px-3 rounded">{{ __('End') }}</span>
                                                @endif
                                            </td>
                                            @if (\Auth::user()->type == 'company')
                                                <td class="text-end">
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['zoommeeting.destroy', $item->id]]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center show_confirm">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>

                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
    <script src="{{ url('assets/js/daterangepicker.js') }}"></script>
    <script type="text/javascript">
        $(document).on('change', '#client_id', function() {
            getProjects($(this).val());
        });

        function getProjects(id) {
            $.get("{{ url(']') }}/" + id, function(data, status) {

                var list = '';
                $('#project_id').empty();
                if (data.length > 0) {
                    list += "<option value=''> {{ __('Select Project') }}</option>";
                } else {
                    list += "<option value=''> {{ __('No Projects') }} </option>";
                }

                $.each(data, function(i, item) {
                    list += "<option value='" + item.id + "'>" + item.name + "</option>"
                });
                $('#project_id').html(list);
            });
        }
        $(document).on("click", '.member_remove', function() {
            var rid = $(this).attr('data-id');
            alert(rid);
            $('.confirm_yes').addClass('m_remove');
            $('.confirm_yes').attr('uid', rid);
            $('#cModal').modal('show');
        });
        $(document).on('click', '.m_remove', function(e) {
            var id = $(this).attr('uid');
            var p_url = "{{ url('zoom-meeting') }}" + '/' + id;
            var data = {
                id: id
            };
            deleteAjax(p_url, data, function(res) {
                toastrs(res.flag, res.msg);
                if (res.flag == 1) {
                    location.reload();
                }
                $('#cModal').modal('hide');
            });
        });
    </script>
@endpush
