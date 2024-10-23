@extends('layouts.admin')

@push('pre-purpose-css-page')
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}">
@endpush
@push('pre-purpose-script-page')
    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
        <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
         @if ($pipeline)
            <script>
                ! function(a) {
                    "use strict";
                    var t = function() {
                        this.$body = a("body")
                    };
                    t.prototype.init = function() {
                        a('[data-plugin="dragula"]').each(function() {
                            var t = a(this).data("containers"),
                                n = [];
                            if (t)
                                for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]);
                            else n = [a(this)[0]];
                            var r = a(this).data("handleclass");
                            r ? dragula(n, {
                                moves: function(a, t, n) {
                                    return n.classList.contains(r)
                                }
                            }) : dragula(n).on('drop', function(el, target, source, sibling) {

                                var order = [];
                                $("#" + target.id + " > div").each(function() {
                                    order[$(this).index()] = $(this).attr('data-id');
                                });

                                var id = $(el).attr('data-id');

                                var old_status = $("#" + source.id).data('status');
                                var new_status = $("#" + target.id).data('status');
                                var stage_id = $(target).attr('data-id');
                                var pipeline_id = '1';

                                $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div")
                                    .length);
                                $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div")
                                    .length);

                                $.ajax({
                                    url: '{{ route('lead.order') }}',
                                    type: 'POST',
                                    data: {
                                        lead_id: id,
                                        stage_id: stage_id,
                                        order: order,
                                        new_status: new_status,
                                        old_status: old_status,
                                        pipeline_id: pipeline_id,
                                        "_token": $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(data) {
                                        toastrs('Success', 'Task successfully updated', 'success');
                                    },
                                    error: function(data) {
                                        data = data.responseJSON;
                                        toastrs('{{ __('Error') }}', data.error, 'error')
                                    }
                                });
                            });
                        })
                    }, a.Dragula = new t, a.Dragula.Constructor = t
                }(window.jQuery),
                function(a) {
                    "use strict";

                    a.Dragula.init()

                }(window.jQuery);
            </script>
            <script>
                $(document).on("change", "#change-pipeline select[name=default_pipeline_id]", function() {
                    $('#change-pipeline').submit();
                });
            </script>
            <script>
                $(document).on("click", ".pipeline_id", function() {
                    var pipeline_id = $(this).attr('data-id');
                    $.ajax({
                        url: '{{ route('lead.change.pipeline') }}',
                        type: 'POST',
                        data: {
                            pipeline_id: pipeline_id,
                            "_token": $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(data) {
                            $('#change-pipeline').submit();
                            location.reload();
                        }
                    });
                });
            </script>
        @endif
    @endif
@endpush
@section('page-title')
    {{ __('Lead') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{ __('Lead') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Lead') }}</li>
@endsection
@section('action-btn')
    @if (\Auth::user()->type == 'company')
        @if ($pipeline)
            <div class="btn-group">
                <button class="btn btn-sm btn-primary btn-icon m-1 dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    {{ $pipeline->name }}
                </button>
                <div class="dropdown-menu">
                    @foreach ($pipelines as $pipe)
                        <a class="dropdown-item pipeline_id" data-id="{{ $pipe->id }}"
                            href="#">{{ $pipe->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
    data-url="{{ route('lead.file.import') }}" data-bs-whatever="{{ __('Import CSV file') }}"> <span
        class="text-white">
        <i class="ti ti-file-import" data-bs-toggle="tooltip"
            data-bs-original-title="{{ __('Import item CSV file') }}"></i>
    </a>
   
    <a href="{{ route('lead.grid') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i class="ti ti-layout-grid text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('List View') }}">
        </i>
    </a>

    @if (\Auth::user()->type == 'company')
        <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal"
            data-url="{{ route('lead.create') }}" data-bs-whatever="{{ __('Create New Lead') }}"
            data-bs-original-title="{{ __('Create New Lead') }}">
            <i data-bs-toggle="tooltip" title="{{ __('Create') }}" class="ti ti-plus text-white"></i>
        </a>
    @endif

@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            @if ($pipeline)
                @php
                    $lead_stages = $pipeline->leadStages;
                    $json = [];
                    foreach ($lead_stages as $lead_stage) {
                        $json[] = 'kanban-blacklist-' . $lead_stage->id;
                    }
                @endphp

                <div class="row kanban-wrapper horizontal-scroll-cards kanban-board"
                    data-containers='{!! json_encode($json) !!}' data-plugin="dragula">
                    @foreach ($lead_stages as $lead_stage)
                        @php $leads = $lead_stage->lead() @endphp
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <div class="float-end">
                                        <label class="btn btn-sm btn-primary btn-icon task-header">
                                            <span class="count text-white">{{ count($leads) }}</span>
                                        </label>
                                    </div>
                                    <h4 class="mb-0">{{ $lead_stage->name }}</h4>
                                </div>
                                <div class="card-body kanban-box" id="kanban-blacklist-{{ $lead_stage->id }}"
                                    data-id="{{ $lead_stage->id }}">
                                    @foreach ($leads as $lead)
                                        @php $labels = $lead->labels() @endphp
                                        <div class="card" data-id="{{ $lead->id }}">
                                            <div class="pt-3 ps-3">
                                                @if ($labels)
                                                    @foreach ($labels as $label)
                                                        <span
                                                            class="badge rounded-pill bg-{{ $label->color }} ml-1">{{ $label->name }}</span>
                                                    @endforeach
                                                @endif
                                                <div class="card-header border-0 pb-0 position-relative">
                                                    <h5>
                                                        <a href="{{ route('lead.show', \Crypt::encrypt($lead->id)) }}"
                                                            data-bs-whatever="{{ __('View Lead Details') }}"
                                                            data-bs-toggle="tooltip" title
                                                            data-bs-original-title="{{ __('Lead Detail') }}">{{ $lead->name }}</a>
                                                    </h5>
                                                    <div class="card-header-right">
                                                        <div class="btn-group card-option">
                                                            <button type="button" class="btn dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="ti ti-dots-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @if (!$lead->is_active)
                                                                    <a href="#" class="table-action">
                                                                        <i class="ti ti-lock"></i>
                                                                    </a>
                                                                @else
                                                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                                                        <a href="#!" class="dropdown-item"
                                                                            data-size="lg"
                                                                            data-url="{{ route('lead.edit', $lead->id) }}"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal"
                                                                            data-bs-whatever="{{ __('Edit Lead') }}">
                                                                            <i class="ti ti-edit"></i>
                                                                            <span>{{ __('Edit') }}</span>
                                                                        </a>
                                                                    @endif

                                                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                                                        <a href="#!" class="dropdown-item"
                                                                            data-size="lg"
                                                                            data-url="{{ route('lead.label', $lead->id) }}"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#exampleModal"
                                                                            data-bs-whatever="{{ __('Add Label') }}">
                                                                            <i class="ti ti-sticker"></i>
                                                                            <span>{{ __('Add Label') }}</span>
                                                                        </a>
                                                                    @endif

                                                                    @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                                                                        {!! Form::open([
                                                                            'method' => 'DELETE',
                                                                            'route' => ['lead.destroy', $lead->id],
                                                                            'id' => 'delete-form-' . $lead->id,
                                                                        ]) !!}
                                                                        <a href="#!"
                                                                            class="dropdown-item show_confirm">
                                                                            <i class="ti ti-trash"></i>{{ __('Delete') }}
                                                                        </a>
                                                                        {!! Form::close() !!}
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted text-sm" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Subject') }}">
                                                        {{ $lead->subject }}</p>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <ul class="list-inline mb-0">
                                                            <li class="list-inline-item d-inline-flex align-items-center"><i
                                                                    class="f-16 text-primary ti ti-message-2"></i>{{ \Auth::user()->dateFormat($lead->date) }}
                                                            </li>
                                                        </ul>
                                                        <div class="user-group">
                                                            @foreach ($lead->users as $user)
                                                                <a href="#" class="avatar rounded-circle avatar-sm"
                                                                    data-original-title="{{ $user->name }}"
                                                                    data-toggle="tooltip">
                                                                    <img @if (!empty($user->avatar)) src="{{ asset('/storage/uploads/avatar/' . $user->avatar) }}" @else avatar="{{ $user->name }}" @endif
                                                                        class="">
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="col-md-12 text-center">
                    <h4>{{ __('No data available') }}</h4>
                </div>
            @endif
            <!-- [ sample-page ] end -->
        </div>
    </div>
@endsection
