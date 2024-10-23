@extends('layouts.admin')
@php
    $attachments = \App\Models\Utility::get_file('contract_attechment');
@endphp
@push('css-page')
    <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
@endpush

@push('pre-purpose-script-page')
    <script>
        $(document).on("click", ".status", function() {
            var status = $(this).attr('data-id');
            var url = $(this).attr('data-url');
            $.ajax({
                url: url,
                type: 'POST',
                data: {

                    "status": status,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    toastrs('{{ __('Success') }}', 'Status Update Successfully!', 'success');
                    location.reload();
                }

            });
        });
    </script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
    <script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>

    <script>
        Dropzone.autoDiscover = true;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            parallelUploads: 1,

            url: "{{ route('contract.file.upload', [$contract->id]) }}",
            success: function(file, response) {
                location.reload()
                if (response.is_success) {
                    toastrs('{{ __('Success') }}', 'Attachment Create Successfully!', 'success');
                    dropzoneBtn(file, response);
                } else {

                    myDropzone.removeFile(file);
                    toastrs('{{ __('Error') }}', 'The attachment must be same as stoarge setting', 'Error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    toastrs('{{ __('Error') }}', 'The attachment must be same as stoarge setting', 'error');
                } else {
                    toastrs('{{ __('Error') }}', 'The attachment must be same as stoarge setting', 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("contract_id", {{ $contract->id }});
        });

        function dropzoneBtn(file, response) {
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "action-btn btn-primary mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('data-original-title', "{{ __('Download') }}");
            download.innerHTML = "<i class='fas fa-download'></i>";

            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "action-btn btn-danger mx-1 mt-1 btn btn-sm d-inline-flex align-items-center");
            del.setAttribute('data-toggle', "tooltip");
            del.setAttribute('data-original-title', "{{ __('Delete') }}");
            del.innerHTML = "<i class='ti ti-trash'></i>";

            del.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm("Are you sure ?")) {
                    var btn = $(this);
                    $.ajax({
                        url: btn.attr('href'),
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'DELETE',
                        success: function(response) {
                            location.reload();
                            if (response.is_success) {
                                btn.closest('.dz-image-preview').remove();
                            } else {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            }
                        },
                        error: function(response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            } else {
                                toastrs('{{ __('Error') }}', response.error, 'error');
                            }
                        }
                    })
                }
            });

            var html = document.createElement('div');
            html.setAttribute('class', "text-center mt-10");
            file.previewTemplate.appendChild(html);
        }
        $(document).on('click', '#comment_submit', function(e) {
            var curr = $(this);

            var comment = $.trim($("#form-comment textarea[name='comment']").val());
            if (comment != '') {
                $.ajax({
                    url: $("#form-comment").data('action'),
                    data: {
                        comment: comment,
                        "_token": "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    success: function(data) {
                        toastrs('{{ __('Success') }}', 'Comment Create Successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 500)
                        data = JSON.parse(data);
                        console.log(data);
                        var html = "<div class='list-group-item px-0'>" +
                            "                    <div class='row align-items-center'>" +
                            "                        <div class='col-auto'>" +
                            "                            <a href='#' class='avatar avatar-sm rounded-circle ms-2'>" +
                            "                                <img src=" + data.default_img +
                            " alt='' class='avatar-sm rounded-circle'>" +
                            "                            </a>" +
                            "                        </div>" +
                            "                        <div class='col ml-n2'>" +
                            "                            <p class='d-block h6 text-sm font-weight-light mb-0 text-break'>" +
                            data.comment + "</p>" +
                            "                            <small class='d-block'>" + data.current_time +
                            "</small>" +
                            "                        </div>" +
                            "                        <div class='action-btn bg-danger me-4'><div class='col-auto'><a href='#' class='mx-3 btn btn-sm  align-items-center delete-comment' data-url='" +
                            data.deleteUrl +
                            "'><i class='ti ti-trash text-white'></i></a></div></div>" +
                            "                    </div>" +
                            "                </div>";

                        $("#comments").prepend(html);
                        $("#form-comment textarea[name='comment']").val('');
                        load_task(curr.closest('.task-id').attr('id'));
                        toastrs('{{ __('success') }}', '{{ __('Comment Added Successfully!') }}');
                    },
                    error: function(data) {
                        toastrs('error', '{{ __('Some Thing Is Wrong!') }}');
                    }
                });
            } else {
                toastrs('error', '{{ __('Please write comment!') }}');
            }
        });

        $(document).on("click", ".delete-comment", function() {
            var btn = $(this);

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                dataType: 'JSON',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    load_task(btn.closest('.task-id').attr('id'));
                    toastrs('{{ __('success') }}', '{{ __('Comment Deleted Successfully!') }}');
                    btn.closest('.list-group-item').remove();
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('error', data.message);
                    } else {
                        toastrs('error', '{{ __('Some Thing Is Wrong!') }}');
                    }
                }
            });
        });
    </script>
@endpush

@section('page-title')
    {{ __('Contract Detail') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Contract') }}</h5>
    </div>
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"> </h5>
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contract.index') }}">{{ __('contract') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ \Auth::user()->contractNumberFormat($contract->id) }}</li>
@endsection

@section('action-btn')
    <div class="col-md-6 text-end d-flex ">
        <a href="{{ route('contract.download.pdf', \Crypt::encrypt($contract->id)) }}"
            class="btn btn-sm btn-primary btn-icon m-2" data-bs-toggle="tooltip" data-bs-placement="top"
            title="{{ __('Download') }}" target="_blanks"><i class="ti ti-download"></i>
        </a>

        <a href="{{ route('get.contract', $contract->id) }}" target="_blank" class="btn btn-sm btn-primary btn-icon m-2">
            <i class="ti ti-eye text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('PreView') }}"> </i>
        </a>

        @if (\Auth::user()->type == 'company' && $contract->status == 'accept')
            <a href="#" class="btn btn-sm btn-primary btn-icon m-2" data-bs-toggle="modal"
                data-bs-target="#exampleModal" data-size="lg" data-url="{{ route('contract.copy', $contract->id) }}"
                data-bs-whatever="{{ __('Duplicate') }}"> <span class="text-white"> <i class="ti ti-copy text-white"
                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('Duplicate') }}"></i></span></a>

            <a href="{{ route('send.mail.contract', $contract->id) }}" class="btn btn-sm btn-primary btn-icon m-2"
                data-bs-toggle="tooltip" data-bs-original-title="{{ __('Send Email') }}">
                <i class="ti ti-mail text-white"></i>
            </a>
        @endif

        @if (
            (\Auth::user()->type == 'company' && $contract->company_signature == '') ||
                (\Auth::user()->type == 'client' && $contract->client_signature == ''))
            {{-- @if ($contract->status == 'accept') --}}
            <a href="#" class="btn btn-sm btn-primary btn-icon m-2" data-bs-toggle="modal"
                data-bs-target="#exampleModal" data-size="md" data-url="{{ route('signature', $contract->id) }}"
                data-bs-whatever="{{ __('signature') }}"> <span class="text-white"> <i class="ti ti-pencil text-white"
                        data-bs-toggle="tooltip" data-bs-original-title="{{ __('signature') }}"></i></span></a>
            </a>
            {{-- @endif --}}
        @endif
        @php
            $status = App\Models\Contract::status();
        @endphp
        @php
            $status = App\Models\Contract::status();
        @endphp
        @if (\Auth::user()->type == 'client')
            <ul class="list-unstyled mb-0 m-1">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary">{{ ucfirst($contract->status) }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        @foreach ($status as $k => $status)
                            <a class="dropdown-item status" data-id="{{ $k }}"
                                data-url="{{ route('contract.status', $contract->id) }}"
                                href="#">{{ ucfirst($status) }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>
        @endif
    </div>
@endsection

@section('content')
    <div class="col-xl-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card sticky-top" style="top:30px">
                    <div class="list-group list-group-flush" id="useradd-sidenav">
                        <a href="#useradd-1" class="list-group-item list-group-item-action border-0">{{ __('General') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#useradd-2" class="list-group-item list-group-item-action border-0">{{ __('Attachment') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#useradd-3" class="list-group-item list-group-item-action border-0">{{ __('Comment') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                        <a href="#useradd-4" class="list-group-item list-group-item-action border-0">{{ __('Notes') }}
                            <div class="float-end"><i class="ti ti-chevron-right"></i></div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-xl-9">
                <div id="useradd-1">
                    <div class="row">
                        <div class="col-xl-7">
                            <div class="row">
                                <div class="col-lg-4 col-6">
                                    <div class="card">
                                        <div class="card-body" style="min-height: 205px;">
                                            <div class="theme-avtar bg-primary">
                                                <i class="ti ti-user-plus"></i>
                                            </div>
                                            <h6 class="mb-3 mt-4">{{ __('Attachment') }}</h6>
                                            <h3 class="mb-0">{{ count($contract->files) }}</h3>
                                            <h3 class="mb-0"></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-6">
                                    <div class="card">
                                        <div class="card-body" style="min-height: 205px;">
                                            <div class="theme-avtar bg-info">
                                                <i class="ti ti-click"></i>
                                            </div>
                                            <h6 class="mb-3 mt-4">{{ __('Comment') }}</h6>
                                            <h3 class="mb-0">{{ count($contract->comment) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-6">
                                    <div class="card">
                                        <div class="card-body" style="min-height: 205px;">
                                            <div class="theme-avtar bg-warning">
                                                <i class="ti ti-file"></i>
                                            </div>
                                            <h6 class="mb-3 mt-4 ">{{ __('Notes') }}</h6>
                                            <h3 class="mb-0">{{ count($contract->note) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-5">
                            <div class="card report_card total_amount_card">
                                <div class="card-body pt-0" style="margin-bottom: -30px; margin-top: -10px;">

                                    <address class="mb-0 text-sm">

                                        <dl class="row mt-4 align-items-center">
                                            <h5>{{ __('Contract Detail') }}</h5>
                                            <br>
                                            <dt class="col-sm-4 h6 text-sm">{{ __('Subject') }}</dt>
                                            <dd class="col-sm-8 text-sm">{{ $contract->subject }}</dd>
                                            <dt class="col-sm-4 h6 text-sm">{{ __('Project') }}</dt>
                                            <dd class="col-sm-8 text-sm">
                                                {{ !empty($contract->projects) ? $contract->projects->title : '' }}</dd>
                                            <dt class="col-sm-4 h6 text-sm">{{ __('Value') }}</dt>
                                            <dd class="col-sm-8 text-sm">
                                                {{ \Auth::user()->priceFormat($contract->value) }}</dd>
                                            <dt class="col-sm-4 h6 text-sm">{{ __(' Type') }}</dt>
                                            <dd class="col-sm-8 text-sm">
                                                {{ !empty($contract->types) ? $contract->types->name : '' }}</dd>
                                            <dt class="col-sm-4 h6 text-sm">{{ __('Start Date') }}</dt>
                                            <dd class="col-sm-8 text-sm">
                                                {{ Auth::user()->dateFormat($contract->start_date) }}</dd>
                                            <dt class="col-sm-4 h6 text-sm">{{ __('End Date') }}</dt>
                                            <dd class="col-sm-8 text-sm">
                                                {{ Auth::user()->dateFormat($contract->end_date) }}</dd>

                                        </dl>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Contract Description ') }}</h5>
                        </div>
                        <div class="card-body">
                            {{ Form::open(['route' => ['contract.contract_description.store', $contract->id]]) }}
                            <div class="col-md-12">
                                <div class="form-group mt-3">
                                    <textarea class="tox-target summernote-simple " name="contract_description" id="summernote" rows="8">{!! $contract->contract_description !!}</textarea>
                                </div>
                            </div>
                            @if (\Auth::user()->type == 'company' && $contract->status == 'accept')
                                <div class="col-md-12 text-end">
                                    <div class="form-group mt-3 me-3">
                                        {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
                                    </div>
                                </div>
                            @endif
                            {{ Form::close() }}
                        </div>
                    </div>

                </div>

                <div id="useradd-2">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ __('Contract Atachments') }}</h5>
                        </div>
                        <div class="card-body">
                            @if ($contract->status == 'accept')
                                <div class="form-group">
                                    <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                                </div>
                            @endif
                            <div class="scrollbar-inner">
                                <div class="card-wrapper p-3 lead-common-box">
                                    @foreach ($contract->files as $file)
                                        <div class="card mb-3 border shadow-none">
                                            <div class="px-3 py-3">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <h6 class="text-sm mb-0">
                                                            <a href="#!">{{ $file->files }}</a>
                                                        </h6>
                                                        <p class="card-text small text-muted">
                                                            {{ number_format(\File::size(storage_path('contract_attechment/' . $file->files)) / 1048576, 2) . ' ' . __('MB') }}
                                                        </p>
                                                    </div>
                                                    <div class="action-btn bg-warning ">
                                                        <a href="{{ $attachments . '/' . $file->files }}"
                                                            class=" btn btn-sm d-inline-flex align-items-center"
                                                            download="" data-bs-toggle="tooltip" title="Download">
                                                            <span class="text-white">
                                                                <i class="ti ti-download"></i>
                                                            </span>
                                                        </a>
                                                    </div>

                                                    @if ((\Auth::user()->id == $file->user_id || \Auth::user()->type == 'company') && $contract->status == 'accept')
                                                        <div class="col-auto actions">
                                                            <div class="action-btn bg-danger ms-2">
                                                                {!! Form::open(['method' => 'DELETE', 'route' => ['contracts.file.delete', $contract->id, $file->id]]) !!}
                                                                <a href="#!"
                                                                    class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                                    <i class="ti ti-trash text-white"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-original-title="{{ __('Delete') }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <a href="#"
                                                        class="btn btn-sm btn-primary btn-icon m-1 getclienee"
                                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                                        data-url="{{ route('signature', 1) }}" data-size="lg"
                                                        data-bs-whatever="{{ __('Signature') }}"> <span
                                                            class="text-white">
                                                            <i class="ti ti-plus text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('signature') }}"></i></span>
                                                    </a>

                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="useradd-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-12">
                                <div class="row p-2">
                                    <div class="col-6">
                                        <h5>{{ __('Comments') }}</h5>
                                    </div>
                                    @if (App\Models\Utility::is_chatgpt_enable())
                                        <div class="col-6 text-end">
                                            <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white "
                                                data-ajax-popup-over="true" id="grammarCheck"
                                                data-url="{{ route('grammar', ['contract_comment']) }}"
                                                data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                                <i class="ti ti-rotate"></i>
                                                <span>{{ __('Grammar check with AI') }}</span></a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="col-12 d-flex">
                                    @if ($contract->status == 'accept')
                                        <div class="form-group mb-0 form-send w-100">
                                            <form method="post" class="card-comment-box" id="form-comment"
                                                data-action="{{ route('comment.store', [$contract->id]) }}">
                                                <textarea rows="1" class="form-control contract_comment" name="comment" data-toggle="autosize"
                                                    placeholder="{{ __('Add a comment...') }}"></textarea>
                                            </form>
                                        </div>
                                        <button id="comment_submit" class="btn btn-send mt-2"><i
                                                class="f-16 text-primary ti ti-brand-telegram"></i></button>
                                    @endif
                                </div>
                            </div>
                            <div class="list-group list-group-flush mb-0" id="comments">
                                @foreach ($contract->comment as $comment)
                                    <div class="list-group-item ">
                                        <div class="row align-items-center">
                                            <div class="col-auto ">
                                                <a href="{{ !empty($contract->clients->avatar) ? asset(Storage::url('uploads/avatar')) . '/' . $contract->clients->avatar : asset(Storage::url('uploads/avatar')) . '/avatar.png' }}"
                                                    target="_blank">
                                                    <img class="rounded-circle" width="50" height="50"
                                                        src="{{ !empty($contract->clients->avatar) ? asset(Storage::url('uploads/avatar')) . '/' . $contract->clients->avatar : asset(Storage::url('uploads/avatar')) . '/avatar.png' }}">
                                                </a>
                                            </div>
                                            <div class="col-auto">
                                            </div>
                                            <div class="col ml-n2">
                                                <p class="d-block h6 text-sm font-weight-light mb-0 text-break">
                                                    {{ $comment->comment }}</p>
                                                <small class="d-block">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            @if ((\Auth::user()->id == $comment->user_id || \Auth::user()->type == 'company') && $contract->status == 'accept')
                                                <div class="col-auto actions">
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['comment_store.destroy', $comment->id]]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>

                <div id="useradd-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="col-12">
                                <div class="row p-2">
                                    <div class="col-6">
                                        <h5> {{ __('Notes') }}</h5>
                                    </div>
                                    @if (App\Models\Utility::is_chatgpt_enable())
                                        <div class="col-6 text-end">
                                            <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white "
                                                data-ajax-popup-over="true" id="grammarCheck"
                                                data-url="{{ route('grammar', ['contract_notes']) }}"
                                                data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                                <i class="ti ti-rotate"></i>
                                                <span>{{ __('Grammar check with AI') }}</span></a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="col-12 d-flex">
                                    @if ($contract->status == 'accept')
                                        <div class="form-group mb-0 form-send w-100">
                                            {{ Form::open(['route' => ['note_store.store', $contract->id]]) }}
                                            <div class="form-group">
                                                <textarea rows="3" class="form-control contract_notes" name="notes" data-toggle="autosize"
                                                    placeholder="{{ __('Add a Notes...') }}" required></textarea>
                                            </div>
                                            <div class="col-md-12 text-end mb-0">
                                                {{ Form::submit(__('Add'), ['class' => 'btn  btn-primary']) }}
                                            </div>
                                            {{ Form::close() }}
                                    @endif
                                </div>
                            </div>
                            <div class="list-group list-group-flush mb-0" id="comments">
                                @foreach ($contract->note as $note)
                                    <div class="list-group-item ">
                                        <div class="row align-items-center">
                                            <div class="col-auto ">
                                                <a href="{{ !empty($contract->clients->avatar) ? asset(Storage::url('uploads/avatar')) . '/' . $contract->clients->avatar : asset(Storage::url('uploads/avatar')) . '/avatar.png' }}"
                                                    target="_blank">
                                                    <img class="rounded-circle" width="50" height="50"
                                                        src="{{ !empty($contract->clients->avatar) ? asset(Storage::url('uploads/avatar')) . '/' . $contract->clients->avatar : asset(Storage::url('uploads/avatar')) . '/avatar.png' }}">
                                                </a>
                                            </div>
                                            <div class="col-auto">
                                            </div>
                                            <div class="col ml-n2">
                                                <p class="d-block h6 text-sm font-weight-light mb-0 text-break">
                                                    {{ $note->notes }}</p>
                                                <small class="d-block">{{ $note->created_at->diffForHumans() }}</small>
                                            </div>
                                            @if ((\Auth::user()->id == $note->user_id || \Auth::user()->type == 'company') && $contract->status == 'accept')
                                                <div class="col-auto actions">
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['note_store.destroy', $note->id]]) !!}
                                                        <a href="#!"
                                                            class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                                            <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Delete') }}"></i>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
