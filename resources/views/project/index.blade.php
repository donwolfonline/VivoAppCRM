@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));

@endphp
@push('script-page')
@endpush
@section('page-title')
    {{ __('Project') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Project') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('All Project') }}</li>
@endsection
@section('action-btn')
    <a href="{{ route('project.grid') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
        data-bs-original-title="{{ __('Grid View') }}">
        <i class="ti ti-layout-grid text-white"></i>
    </a>
    @if (\Auth::user()->type == 'company')
        <a href="{{ route('project.create') }}" class="btn btn-sm btn-primary btn-icon m-1"
            data-bs-whatever="{{ __('Create New Project') }}" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Create') }}"> <i class="ti ti-plus text-white"></i></a>
    @endif
@endsection
@section('filter')
@endsection
@section('content')
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <!-- <h5></h5> -->
                <div class="table-responsive">
                    <table class="table" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col" class="sort" data-sort="name">{{ __('Title') }}</th>
                                <th scope="col" class="sort" data-sort="budget">{{ __('Budget') }}</th>
                                <th scope="col" class="sort" data-sort="status">{{ __('Status') }}</th>
                                <th scope="col">{{ __('Users') }}</th>
                                <th scope="col" class="sort" data-sort="completion">{{ __('Completion') }}</th>
                                <th scope="col" class="text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($projects as $project)
                                @php
                                    $percentages = 0;
                                    $total = count($project->tasks);
                                    // $total = 0;
                                    if ($total != 0) {
                                        $percentages = round($project->completedTask($stage_id) / ($total / 100));
                                    }
                                @endphp
                                <tr>
                                    <th scope="row">
                                        <div class="media align-items-center">
                                            <div class="media-body">
                                                <a href="{{ route('project.show', \Crypt::encrypt($project->id)) }}"
                                                    class="name mb-0 h6 text-sm">{{ $project->title }}</a>
                                            </div>
                                        </div>
                                    </th>
                                    <td class="budget">
                                        {{ \Auth::user()->priceFormat($project->price) }}
                                    </td>
                                    <td>
                                        @if ($project->status == 'not_started')
                                            <span class="badge fix_badges bg-primary p-1 px-3 rounded">
                                                <i class="bg-primary"></i>
                                                <span class="status">{{ __('Not Started') }}</span>
                                            </span>
                                        @elseif($project->status == 'in_progress')
                                            <span class="badge fix_badges bg-success p-1 px-3 rounded">
                                                <i class="bg-success"></i>
                                                <span class="status">{{ __('In Progress') }}</span>
                                            </span>
                                        @elseif($project->status == 'on_hold')
                                            <span class="badge fix_badges bg-info p-1 px-3 rounded">
                                                <i class="bg-info"></i>
                                                <span class="status">{{ __('On Hold') }}</span>
                                            </span>
                                        @elseif($project->status == 'canceled')
                                            <span class="badge fix_badges bg-danger p-1 px-3 rounded">
                                                <i class="bg-danger"></i>
                                                <span class="status">{{ __('Canceled') }}</span>
                                            </span>
                                        @elseif($project->status == 'finished')
                                            <span class="badge fix_badges bg-warning p-1 px-3 rounded">
                                                <i class="bg-warning"></i>
                                                <span class="status">{{ __('Finished') }}</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td>

                                        <div class="user-group">
                                            @foreach ($project->users as $projectUser)
                                                <!-- <a href="#" class="avatar rounded-circle avatar-sm"> -->
                                                <img @if (!empty($projectUser->avatar)) src="{{ $profile . $projectUser->avatar }}" @else avatar="{{ $projectUser->name }}" @endif
                                                    class="" data-bs-toggle="tooltip"
                                                    title="{{ $projectUser->name }}">
                                                <!-- </a> -->
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="completion mr-2">{{ $percentages }}%</span>
                                            <div>
                                                <div class="progress" style="width: 100px;">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        aria-valuenow="{{ $percentages }}" aria-valuemin="0"
                                                        aria-valuemax="100" style="width: {{ $percentages }}%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        
                                        <a href="#" class="btn btn-sm action-btn bg-secondary ms-2"
                                            data-bs-toggle="modal" data-bs-target="#exampleModal"
                                            data-url="{{ route('project.copy', [$project->id]) }}"
                                            data-bs-whatever="{{ __('Create New Item') }}"> <span class="text-white">
                                                <i class="ti ti-copy text-white" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Duplicate Project') }}"></i></span>
                                        </a>

                                        @if (\Auth::user()->type == 'company')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="{{ route('project.edit', \Crypt::encrypt($project->id)) }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                    data-bs-whatever="{{ __('Edit Project') }}" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Edit') }}"> <span class="text-white">
                                                        <i class="ti ti-edit"></i></span></a>
                                            </div>
                                        @endif
                                        <div class="action-btn bg-warning ms-2">
                                            <a href="{{ route('project.show', \Crypt::encrypt($project->id)) }}"
                                                class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                data-bs-whatever="{{ __('View Project') }}" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('View') }}"> <span class="text-white"> <i
                                                        class="ti ti-eye"></i></span></a>
                                        </div>



                                        @if (\Auth::user()->type == 'company')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['project.destroy', $project->id]]) !!}
                                                <a href="#!"
                                                    class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
