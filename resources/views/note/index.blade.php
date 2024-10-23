@extends('layouts.admin')

@push('script-page')
@endpush
@section('page-title')
    {{ __('Note') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Note') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Note') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('note.create') }}" data-bs-whatever="{{ __('Create New Note') }}"> <span class="text-white">
                <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                    data-bs-original-title="{{ __('Create') }}"></i></span>
        </a>
    @endif
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="row notes-list">
                @forelse ($notes as $note)
                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">{{ $note->title }}</h6>
                                    </div>
                                    <div class="text-right">
                                        <div class="actions">
                                            <div class="dropdown action-item">
                                                <a href="#" class="action-item" data-bs-toggle="dropdown"><i
                                                        class="ti ti-dots-vertical"></i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a href="#" class="dropdown-item" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal"
                                                        data-url="{{ route('note.edit', $note->id) }}"
                                                        data-bs-whatever="{{ __('Edit Note') }}">
                                                        <i class="ti ti-edit"> </i>{{ __('Edit') }}</a>

                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['note.destroy', $note->id]]) !!}
                                                    <a href="#!" class=" show_confirm dropdown-item">
                                                        <i class="ti ti-trash"></i>{{ __('Delete') }}
                                                    </a>
                                                    {!! Form::close() !!}

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-justify">{{ $note->description }}</p>
                                <div class="media align-items-center mt-2">
                                    <div class="media-body">
                                        <span class="h6 mb-0">{{ __('Created Date') }}</span><br>
                                        <span
                                            class="text-sm text-muted">{{ \Auth::user()->dateFormat($note->created_at) }}</span>
                                    </div>
                                    @if (!empty($note->file))
                                        @php
                                            $x = pathinfo($note->file, PATHINFO_FILENAME);

                                            $extension = pathinfo($note->file, PATHINFO_EXTENSION);
                                            // dd($extension);
                                            $result = str_replace(['#', "'", ';'], '', $note->file);
                                            $notes = \App\Models\Utility::get_file('uploads/notes/');
                                            // dd($notes);
                                        @endphp
                                        <div class="media-body text-end">
                                            <a href="{{ route('note.receipt', [$x, "$extension"]) }}" data-toggle="tooltip"
                                                class="btn btn-sm btn-primary btn-icon rounded-pill">
                                                <i class="ti ti-download" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Download') }}"></i>
                                            </a>
                                            <a href="{{ $notes . $x . '.' . $extension }}" data-toggle="tooltip" target="_blank"
                                                class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                                <i class="ti ti-crosshair" data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('Preview') }}"></i>
                                            </a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card text-center">
                        <div class="pt-10 card-body">
                            <span> {{ __('No Entry Found') }} </span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
