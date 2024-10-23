@extends('layouts.admin')
@php
    $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>
    <script>
        !function (a) {
            "use strict";
            var t = function () {
                this.$body = a("body")
            };
            t.prototype.init = function () {
                a('[data-plugin="dragula"]').each(function () {
                    var t = a(this).data("containers"), n = [];
                    if (t) for (var i = 0; i < t.length; i++) n.push(a("#" + t[i])[0]); else n = [a(this)[0]];
                    var r = a(this).data("handleclass");
                    r ? dragula(n, {
                        moves: function (a, t, n) {
                            return n.classList.contains(r)
                        }
                    }) : dragula(n).on('drop', function (el, target, source, sibling) {

                        var id = $(el).attr('data-id');
                        var order = [];
                        $("#" + target.id).each(function () {
                            order[$(this).index()] = $(this).attr('data-id');
                        });


                        var old_status = $("#" + source.id).data('status');
                        var new_status = $("#" + target.id).data('status');
                        var stage_id = $(target).attr('data-id');

                        $("#" + source.id).parent().find('.count').text($("#" + source.id + " > div").length);
                        $("#" + target.id).parent().find('.count').text($("#" + target.id + " > div").length);

                        $.ajax({
                            url: '{{route('project.task.order')}}',
                            type: 'POST',
                            data: {task_id: id, stage_id: stage_id, order: order, old_status: old_status, new_status: new_status, "_token": $('meta[name="csrf-token"]').attr('content')},
                            success: function (data) {
                                toastrs('Success', 'Task successfully updated', 'success');
                            },
                            error: function (data) {
                                data = data.responseJSON;
                                toastrs('{{__("Error")}}', data.error, 'error')
                            }
                        });
                    });
                })
            }, a.Dragula = new t, a.Dragula.Constructor = t
        }(window.jQuery), function (a) {
            "use strict";

            a.Dragula.init()

        }(window.jQuery);
    </script>

    <script>

        $(document).on("click", ".status", function () {
            var status = $(this).attr('data-id');
            var url = $(this).attr('data-url');

            $.ajax({
                url: url,
                type: 'POST',
                data: {status: status, "_token": $('meta[name="csrf-token"]').attr('content')},
                success: function (data) {
                    $('#change-project-status').submit();
                    location.reload();
                }
            });
        });
    </script>
    <script>

        $(document).on('change', '#project', function () {
            var project_id = $(this).val();

            $.ajax({
                url: '{{route('project.getMilestone')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#milestone_id').empty();
                    $('#milestone_id').append('<option value="0"> -- </option>');
                    $.each(data, function (key, value) {
                        $('#milestone_id').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });

            $.ajax({
                url: '{{route('project.getUser')}}',
                type: 'POST',
                data: {
                    "project_id": project_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#assign_to').empty();
                    $.each(data, function (key, value) {
                        $('#assign_to').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });

        });
    </script>
@endpush
@section('page-title')
    {{__('Task')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Task')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Task')}}</li>
@endsection
@section('action-btn')

    <a href="{{ route('task.calendar') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip" title="Calendar View" >
        <i class="ti ti-calendar text-white"></i>
    </a>
    
    <a href="{{ route('project.all.task.gantt.chart') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"  data-bs-original-title="{{__('Gnatt Chart')}}">
        <i class="ti ti-chart-line"></i>
    </a>


    <a href="{{ route('project.all.task') }}" class="btn btn-sm btn-primary btn-icon m-1">
        <i  data-bs-toggle="tooltip"  data-bs-original-title="{{__('List View')}}" class="ti ti-list"></i>
    </a>


    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" 
    data-bs-target="#exampleModal" data-url="{{ route('project.task.create',0) }}" data-size="lg"
    data-bs-whatever="{{__('Create New Task')}}" >
        <i data-bs-toggle="tooltip"  data-bs-original-title="{{__('Create')}}" class="ti ti-plus text-white"></i>
    </a>


@endsection

@section('content')

        <div class="col-sm-12">
            <div class=" {{isset($_GET['project'])?'show':''}}" >
                <div class="card card-body">
                    {{ Form::open(array('route' => array('project.all.task.kanban'),'method'=>'get')) }}
                    <div class="row filter-css">
                        @if(\Auth::user()->type=='company')
                            <div class="col-md-3">
                                {{ Form::select('project', $projectList,!empty($_GET['project'])?$_GET['project']:'', array('class' => 'form-control','data-toggle'=>'select')) }}
                            </div>
                        @endif
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="status">
                                <option value="">{{__('Select Status')}}</option>
                                @foreach($stageList as $k=>$val)
                                    <option value="{{$k}}" {{isset($_GET['status']) && $_GET['status']==$k?'selected':''}}> {{$val}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control" data-toggle="select" name="priority">
                                <option value="">{{__('Select Priority')}}</option>
                                @foreach($priority as $val)
                                    <option value="{{$val}}" {{isset($_GET['priority']) && $_GET['priority']==$val?'selected':''}}> {{$val}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            {{Form::date('due_date',isset($_GET['due_date']) ? $_GET['due_date'] : new \DateTime(),array('class'=>'form-control'))}}                        </div>
                        <div class="action-btn bg-info ms-2 col-auto">
                            <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center" data-toggle="tooltip" data-title="{{__('Apply')}}"><i data-bs-toggle="tooltip" data-bs-original-title="{{__('Apply')}}" class="ti ti-search text-white"></i></button>
                        </div>
                        <div class="action-btn bg-danger ms-2 col-auto">
                            <a href="{{route('project.all.task.kanban')}}" data-toggle="tooltip" data-title="{{__('Reset')}}" class="mx-3 btn btn-sm d-flex align-items-center"><i data-bs-toggle="tooltip" data-bs-original-title="{{__('Reset')}}" class="ti ti-trash-off text-white"></i></a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
  
    <div class="row">
        <div class="col-sm-12">
            @php
                $json = [];
                foreach ($stages as $stage){
                    $json[] = 'kanban-blacklist-'.$stage->id;
                }
            @endphp
            <div class="row kanban-wrapper horizontal-scroll-cards kanban-board" data-containers='{!! json_encode($json) !!}' data-plugin="dragula">
                @foreach($stages as $stage)
                    @php
                        if(empty($_GET['project']) && empty($_GET['priority']) && empty($_GET['due_date'])){
                        $tasks = $stage->allTask;
                        }else{
                            $tasks=$stage->allTaskFilter($_GET['project'] , $_GET['priority'],$_GET['due_date']);
                        }
                    @endphp
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <div class="float-end">
                                        <button class="btn btn-sm btn-primary btn-icon task-header">
                                            <span class="count text-white">{{count($tasks)}}</span>
                                        </button>
                                    </div>
                                    <h4 class="mb-0">{{$stage->name}}</h4>
                                </div>
                                <div class="card-body kanban-box" data-id="{{$stage->id}}"  id="kanban-blacklist-{{$stage->id}}">
                                    @foreach($tasks as $task)
                                        <div class="card" data-id="{{$task->id}}">
                                            <div class="pt-3 ps-3">
                                                @if($task->priority =='low')
                                                        <div class="badge bg-success p-1 px-3 rounded"> {{ ucfirst($task->priority) }}</div>
                                                @elseif($task->priority =='medium')
                                                    <div class="badge bg-warning p-1 px-3 rounded"> {{ ucfirst($task->priority) }}</div>
                                                @elseif($task->priority =='high')
                                                    <div class="badge bg-danger p-1 px-3 rounded"> {{ucfirst($task->priority)  }}</div>
                                                @endif
                                                <div class="card-header border-0 pb-0 position-relative">
                                                    <h5> 
                                                        <a href="#" data-size="lg" data-url="{{ route('project.task.show',$task->id) }}" data-bs-toggle="modal"
                                                        data-bs-target="#exampleModal" data-bs-whatever="{{__('View Task Details')}}" 
                                                        data-bs-toggle="tooltip"  title data-bs-original-title="{{__('Task Detail')}}" >{{$task->title}}</a></h5>
                                                        <div class="card-header-right">
                                                            <div class="btn-group card-option">
                                                                <button type="button" class="btn dropdown-toggle"
                                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                    <i class="ti ti-dots-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    @if(\Auth::user()->type=='company')
                                                                        <a href="#!" class="dropdown-item" data-size="lg" data-url="{{ route('project.task.edit',$task->id) }}" 
                                                                            data-bs-toggle="modal" data-bs-target="#exampleModal"  data-bs-whatever="{{__('Edit Task')}}">
                                                                            <i class="ti ti-edit"></i>
                                                                            <span>{{__('Edit')}}</span>
                                                                        </a>
                                                                    @endif
                                                                    <a href="#!" class="dropdown-item"  data-size="lg" data-url="{{ route('project.task.show',$task->id) }}" 
                                                                        data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{__('View Task Details')}}">
                                                                        <i class="ti ti-eye"></i>
                                                                        <span>{{__('View')}}</span>
                                                                    </a>
                                                                    <span class="">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['project.task.destroy', $task->id],'id'=>'task-delete-form-'.$task->id]) !!}
                                                                        <a href="#!" class="dropdown-item  show_confirm ">
                                                                            <i class="ti ti-trash"></i>{{ __('Delete') }}
                                                                        </a>
                                                                        {!! Form::close() !!}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted text-sm" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Description') }}">{{ $task->description }}</p>
                                                    <p class="text-muted text-sm">{{$task->taskCompleteCheckListCount()}}/{{$task->taskTotalCheckListCount()}}</p>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <ul class="list-inline mb-0">
                                                            
                                                            <li class="list-inline-item d-inline-flex align-items-center"><i
                                                                    class="f-16 text-primary ti ti-message-2"></i>{{\Auth::user()->dateFormat($task->start_date)}}</li>
                                                            
                                                            <li class="list-inline-item d-inline-flex align-items-center"><i
                                                                    class="f-16 text-primary ti ti-link"></i>{{\Auth::user()->dateFormat($task->due_date)}}</li>
                                                        </ul>
                                                        <div class="user-group">
                                                            <img alt="image" data-toggle="tooltip" data-original-title="{{!empty($task->taskUser)?$task->taskUser->name:''}}" @if($task->taskUser && !empty($task->taskUser->avatar)) src="{{$profile.'/'.$task->taskUser->avatar}}" @else avatar="{{!empty($task->taskUser)?$task->taskUser->name:''}}" @endif class="">
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
            <!-- [ sample-page ] end -->
        </div>
    </div>
@endsection



