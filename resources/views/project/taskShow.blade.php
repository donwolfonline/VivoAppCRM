<div class="row timer_div">
    <div class="col-auto">
        <h5 class="h5">{{ $task->title }}</h5>
    </div>
    <div class="col text-end start-div">
        @if ($task->time_tracking == 0)
            <a href="#" class="start-task start_timer" data-type="start" data-id="{{ $task->id }}">
                <i class="far fa-clock"></i>
                {{ __('Start Tracking') }}
            </a>
        @else
            <div class="timer-counter"></div>
            <a href="#" class="stop-task finish_timer" data-type="stop" data-id="{{ $task->id }}">
                <i class="far fa-clock"></i>
                {{ __('Stop Tracking') }}
            </a>
        @endif
    </div>
</div>


<div class="py-3 my-2 border-top border-bottom">
    <h6 class="text-sm">{{ __('Description') }}:
        @if ($task->priority == 'low')
            <div class="badge badge-pill badge-sm badge-success float-right"> {{ ucfirst($task->priority) }}</div>
        @elseif($task->priority == 'medium')
            <div class="badge badge-pill badge-sm badge-warning float-right"> {{ ucfirst($task->priority) }}</div>
        @elseif($task->priority == 'high')
            <div class="badge badge-pill badge-sm badge-danger float-right"> {{ ucfirst($task->priority) }}</div>
        @endif
    </h6>
    <p class="text-sm mb-0">{{ $task->description }}</p>
</div>

<dl class="row">
    <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{ __('Start Date') }}</span></dt>
    <dd class="col-sm-9"><span class="text-sm">{{ \Auth::user()->dateFormat($task->start_date) }}</span>
    </dd>
    <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{ __('Due Date') }}</span></dt>
    <dd class="col-sm-9"><span class="text-sm">{{ \Auth::user()->dateFormat($task->due_date) }}</span>
    </dd>
    <dt class="col-sm-3"><span class="h6 text-sm mb-0">{{ __('Milestone') }}</span></dt>
    <dd class="col-sm-9"><span class="text-sm">{{ !empty($task->milestone) ? $task->milestone->title : '' }}</span>
    </dd>
</dl>

<div class="row justify-content-center">
    <!-- [ sample-page ] start -->
    <div class="col-12">
        <div class="p-3 card">
            <ul class="nav nav-pills nav-fill" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-user-tab-1" data-bs-toggle="pill"
                        data-bs-target="#pills-user-1" type="button">{{ __('Checklist') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-user-tab-2" data-bs-toggle="pill" data-bs-target="#pills-user-2"
                        type="button">{{ __('Comments') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-user-tab-3" data-bs-toggle="pill" data-bs-target="#pills-user-3"
                        type="button">{{ __('Files') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-user-tab-4" data-bs-toggle="pill" data-bs-target="#pills-user-4"
                        type="button">{{ __('Time Tracking') }}</button>
                </li>
            </ul>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-user-1" role="tabpanel"
                        aria-labelledby="pills-user-tab-1">
                        <h3 class="mb-0">{{ __('Checklist') }}</h3>
                        <div class="row mt-3">
                            <div class="col-md-11">
                                <div class="row">
                                    <div class="col-md-6 form-label">
                                        <b>{{ __('Progress') }}</b>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <b>
                                            <span class="progressbar-label custom-label"
                                                style="margin-top: -9px !important;margin-left: .7rem">
                                                0%
                                            </span>
                                        </b>
                                    </div>
                                </div>
                                <div class="text-left">
                                    <div class="custom-widget__item flex-fill">
                                        <div class="custom-widget__progress d-flex  align-items-center">
                                            <div class="progress" style="height: 5px;width: 100%;">
                                                <div class="progress-bar" role="progressbar" aria-valuenow="100"
                                                    aria-valuemin="0" aria-valuemax="100" id="taskProgress"></div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="text-right mb-1">
                                    <a href="#"
                                        class="mx-1 btn btn-sm d-inline-flex btn-primary align-items-center"
                                        data-bs-toggle="collapse" data-bs-target="#form-checklist"
                                        aria-expanded="false" aria-controls="collapseExample"><i
                                            class="ti ti-plus"></i></a>
                                </div>
                            </div>

                            <form method="POST" id="form-checklist" class="collapse col-md-12"
                                data-action="{{ route('project.task.checklist.store', [$task->id]) }}">
                                <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
                                <div class="form-group">
                                    <label class="form-label text-start">{{ __('Name') }}</label>
                                    <input type="text" name="name" class="form-control" required
                                        placeholder="{{ __('Checklist Name') }}">
                                </div>
                                <div class="text-end">
                                    <div class="btn-group mb-2 ml-2 d-none d-sm-inline-block">
                                        <button type="button" class="btn btn-primary form-checklist">
                                            {{ __('Create') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="row mt-2">
                                <div class="col-md-11">
                                    <div class="checklist" id="check-list">
                                        @foreach ($task->taskCheckList as $checkList)
                                            <div class="card border checklist-div">
                                                <div class="px-3 py-2 row align-items-center">
                                                    <div class="col-10">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox"
                                                                id="checklist-{{ $checkList->id }}"
                                                                class="custom-control-input taskCheck"
                                                                {{ $checkList->status == 1 ? 'checked' : '' }}
                                                                value="{{ $checkList->id }}"
                                                                data-url="{{ route('project.task.checklist.update', [$checkList->task_id, $checkList->id]) }}">
                                                            <label class="custom-control-label h6 text-sm"
                                                                for="checklist-{{ $checkList->id }}">{{ $checkList->name }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#"class="mx-3 btn btn-sm d-inline-flex align-items-center delete-checklist"
                                                            data-url="{{ route('project.task.checklist.destroy', [$checkList->task_id, $checkList->id]) }}">
                                                            <i class="ti ti-trash text-white"></i>
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

                    <div class="tab-pane fade" id="pills-user-2" role="tabpanel" aria-labelledby="pills-user-tab-2">
                        <h3 class="mb-0">{{ __('Comment') }}</h3>
                        <div class="comment-holder">
                            <div class="list-group list-group-flush" id="comments">
                                @foreach ($task->comments as $comment)
                                    <div class="list-group-item comment-div">
                                        <div class="row">
                                            <div class="col ml-n2">
                                                <a href="#!"
                                                    class="d-block h6 mb-0">{{ !empty($comment->user) ? $comment->user->name : '' }}</a>
                                                <div>
                                                    <small>{{ $comment->comment }}</small>
                                                </div>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                <a href="#"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center  delete-comment"
                                                    data-url="{{ route('project.task.comment.destroy', [$comment->id]) }}">
                                                    <i class="ti ti-trash text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <li class="col-12 border-0 ">
                            <form id="form-comment" data-action="{{ route('project.task.comment.store', [$task->project_id, $task->id]) }}">
                                <div class="form-group mb-0 form-send w-100">
                                    <input type="text" class="form-control task_show" value=""
                                        name="comment" placeholder="Write your comment....">
                                    <button class="btn btn-send"><i
                                            class="f-16 text-primary ti ti-brand-telegram"></i></button>
                                </div>
                            </form>
                            @if (App\Models\Utility::is_chatgpt_enable())
                                <div class="col-12 text-end">
                                    <a data-size="md" class="btn btn-primary btn-icon btn-sm text-white "
                                        data-ajax-popup-over="true" id="grammarCheck"
                                        data-url="{{ route('grammar', ['task_show']) }}" data-bs-placement="top"
                                        data-title="{{ __('Grammar check with AI') }}">
                                        <i class="ti ti-rotate"></i>
                                        <span>{{ __('Grammar check with AI') }}</span></a>
                                </div>
                            @endif
                        </li>
                    </div>


                    <div class="tab-pane fade" id="pills-user-3" role="tabpanel" aria-labelledby="pills-user-tab-3">
                        <h3 class="mb-0">{{ __('Files') }}</h3>
                        <div class="row mt-3">
                            <form method="post" id="form-file" enctype="multipart/form-data"
                                data-url="{{ route('project.task.comment.file.store', $task->id) }}">
                                @csrf
                                <input type="file" class="form-control mb-2" name="file" id="file">
                                <span class="invalid-feedback" id="file-error" role="alert">
                                    <strong></strong>
                                </span>
                                <div class="text-end">
                                    <div class="btn-group mb-2 ml-2 d-none d-sm-inline-block">
                                        <button type="submit" class="btn btn-primary">{{ __('Upload') }}</button>
                                    </div>
                                </div>
                            </form>

                            <div class="row">
                                <div class="col-md-12">
                                    {{-- @dd($task->taskFiles); --}}
                                    @foreach ($task->taskFiles as $file)
                                        @php
                                            $file_storage = \App\Models\Utility::get_file('uploads/tasks/');
                                        @endphp
                                        <div class="card mb-3 border shadow-none" id="comments-file">
                                            <div class="px-3 py-3">
                                                <div class="row align-items-center">
                                                    <div class="col ml-n2">
                                                        <h6 class="text-sm mb-0">
                                                            <a href="#!">{{ $file->name }}</a>
                                                        </h6>
                                                        <p class="card-text small text-muted">
                                                            {{ $file->file_size }}
                                                        </p>
                                                    </div>
                                                    <div class="action-btn bg-info ms-2">
                                                        <a download href="{{ $file_storage . $file->file }}"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                                            <i class="ti ti-download text-white"></i>
                                                        </a>
                                                    </div>
                                                    <div class="action-btn bg-danger ms-2">
                                                        <a href="#"
                                                            class="mx-3 btn btn-sm d-inline-flex align-items-center delete-comment-file"
                                                            data-url="{{ route('project.task.comment.file.destroy', [$file->id]) }}">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="tab-pane fade" id="pills-user-4" role="tabpanel" aria-labelledby="pills-user-tab-4">
                        <h3 class="mb-0">{{ __('Time Tracking') }}</h3>
                        <div class="row mt-3">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-header card-body table-border-style" id="comments">
                                        <h5></h5>
                                        <div class="table-responsive">
                                            <table class="table" id="pc-dt-simple">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">{{ __('Start Time') }}</th>
                                                        <th scope="col">{{ __('End Time') }}</th>
                                                        <th scope="col">{{ __('Time') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($task->taskTimer as $time)
                                                        <tr>
                                                            <td>{{ $time->start_time }}</td>
                                                            <td>{{ $time->end_time }}</td>
                                                            <td>{{ $task->taskTime($time->start_time, $time->end_time) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="2" class="text-right">{{ __('Total Time') }}
                                                            :</td>
                                                        <td>{{ $task->totalTime() }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
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
    <!-- [ Main Content ] end -->
</div>

<script>
    $(document).on('click', '.form-checklist', function(e) {
        e.preventDefault();
        $.ajax({
            url: $("#form-checklist").data('action'),
            type: 'POST',
            data: $('#form-checklist').serialize(),
            dataType: 'JSON',
            success: function(data) {
                toastrs('Success', '{{ __('Checklist successfully created.') }}', 'success');

                var html = '<div class="card border draggable-item shadow-none">\n' +
                    '<div class="px-3 py-2 row align-items-center">\n' + '<div class="col-10">\n' +
                    '<div class="custom-control custom-checkbox ">\n' +
                    '<input type="checkbox" id="checklist-' +
                    data.id + '" class="custom-control-input taskCheck"  value="' + data.id +
                    '" data-url="' + data.updateUrl + '">\n' +
                    '<label class="custom-control-label h6 text-sm" for="checklist-' +
                    data.id + '">' + data.name + '</label>\n' + '</div>\n' + '</div>\n' +
                    '<div class="col-auto card-meta d-inline-flex align-items-center ml-sm-auto">\n' +
                    '<a href="#" class="action-btn bg-danger ms-2 mx-3 btn btn-sm d-inline-flex action-item delete-checklist " data-url="' +
                    data.deleteUrl + '">\n' + '<i class="ti ti-trash"></i>\n' + '</a>\n' +
                    '</div>\n' + '</div>\n' + '</div>';
                $("#check-list").prepend(html);
                $("#form-checklist input[name=name]").val('');
                $("#form-checklist").collapse('toggle');
            },
        });
    });
    $(document).on("click", ".delete-checklist", function() {
        if (confirm('Are You Sure!. ?')) {
            var checklist = $(this).parent().parent().parent();

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'JSON',
                success: function(data) {
                    toastrs('Success', '{{ __('Checklist successfully deleted.') }}', 'success');
                    checklist.remove();
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('Error', data.message, 'error');
                    } else {
                        toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
                    }
                }
            });
        }
    });
    var checked = 0;
    var count = 0;
    var percentage = 0;
    $(document).on("change", "#check-list input[type=checkbox]", function() {
        $.ajax({
            url: $(this).attr('data-url'),
            type: 'post',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                toastrs('Success', '{{ __('Checklist successfully updated.') }}', 'success');
            },
            error: function(data) {
                data = data.responseJSON;
                toastrs('Error', '{{ __('Something is wrong.') }}', 'error');
            }
        });
        taskCheckbox();
    });

    //for comment
    $(document).on('click', '#form-comment', function(e) {
        var comment = $("#form-comment input[name='comment']").val();
        var name = '{{ \Auth::user()->name }}';
        if (comment != '') {
            $.ajax({
                url: $("#form-comment").data('action'),
                data: {
                    comment: comment,
                    "_token": $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                success: function(data) {
                    data = JSON.parse(data);
                    var html = '<div class="list-group-item">\n' +
                        '                            <div class="row">\n' +
                        '                                <div class="col ml-n2">\n' +
                        '                                    <a href="#!" class="d-block h6 mb-0">' +
                        name + '</a>\n' +
                        '                                    <div>\n' +
                        '                                        <small>' + data.comment +
                        '</small>\n' +
                        '                                    </div>\n' +
                        '                                </div>\n' +
                        '                                <div class="col-auto">\n' +
                        '                                    <a href="#" class="action-item  delete-comment" data-url="' +
                        data.deleteUrl + '">\n' +
                        '                                        <i class="ti ti-trash"></i>\n' +
                        '                                    </a>\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                        </div>';


                    $("#comments").prepend(html);
                    $("#form-comment textarea[name='comment']").val('');
                    toastrs('Success', '{{ __('Comment successfully created.') }}', 'success');
                },
                error: function(data) {
                    toastrs('Error', '{{ __('Some thing is wrong.') }}', 'error');
                }
            });
        } else {
            toastrs('Success', '{{ __('Write Your Comment About This Task.') }}', 'success');

        }
    });
    $(document).on("click", ".delete-comment", function() {
        if (confirm('Are You Sure.! ?')) {
            var comment = $(this).parent().parent().parent();
            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'JSON',
                success: function(data) {
                    toastrs('Success', '{{ __('Comment Deleted Successfully!') }}', 'success');
                    comment.remove();
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('Error', data.message, 'error');
                    } else {
                        toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
                    }
                }
            });
        }
    });
    $(document).on('submit', '#form-file', function(e) {
        e.preventDefault();
        $.ajax({
            url: $("#form-file").data('url'),
            type: 'POST',
            data: new FormData(this),
            dataType: 'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {

                toastrs('Success', '{{ __('Files successfully uploaded.') }}', 'success');

                var html = '<div class="card mb-3 border shadow-none">\n' +
                    '                            <div class="px-3 py-3">\n' +
                    '                                <div class="row align-items-center">\n' +
                    '                                    <div class="col ml-n2">\n' +
                    '                                        <h6 class="text-sm mb-0">\n' +
                    '                                            <a href="#!">' + data.name +
                    '</a>\n' +
                    '                                        </h6>\n' +
                    '                                        <p class="card-text small text-muted">\n' +
                    '                                            ' + data.file_size + '\n' +
                    '                                        </p>\n' +
                    '                                    </div>\n' +
                    '                                    <div class="col-auto actions">\n' +
                    '                                        <a downloaced href="{{ asset(Storage::url('tasks')) }}' +
                    data.file +
                    '" class="action-btn bg-info ms-2  btn btn-sm d-inline-flex align-items-center">\n' +
                    '                                            <i class="ti ti-download text-white"></i>\n' +
                    '                                        </a>\n' +
                    '                                        <a href="#" class="action-btn bg-danger ms-2 btn btn-sm d-inline-flex align-items-center delete-comment-file" data-url="' +
                    data.deleteUrl + '">\n' +
                    '                                            <i class="ti ti-trash"></i>\n' +
                    '                                        </a>\n' +
                    '\n' +
                    '                                    </div>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                        </div>';
                $("#comments-file").prepend(html);
                location.reload();
            },
            error: function(data) {
                data = data.responseJSON;
                if (data.message) {

                    toastrs('{{ __('Error') }}', 'The attachment must be a file of type',
                        'error');
                } else {
                    toastrs('Error', '{{ __('Some Thing Is Wrong!') }}', 'error');
                }
            }
        });
    });
    $(document).on("click", ".delete-comment-file", function() {

        if (confirm('Are You Sure. ?')) {
            var div = $(this).parent().parent().parent().parent();

            $.ajax({
                url: $(this).attr('data-url'),
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'JSON',
                success: function(data) {
                    toastrs('Success', '{{ __('File successfully deleted.') }}', 'success');
                    div.remove();
                },
                error: function(data) {
                    data = data.responseJSON;
                    if (data.message) {
                        toastrs('Error', data.message, 'error');
                    } else {
                        toastrs('Error', '{{ __('Some thing is wrong.') }}', 'error');
                    }
                }
            });
        }
    });
    // For task timer start
    $(document).on("click", ".start_timer", function() {
        var main_div = $(this).parent().parent().parent();
        var current = $(this);
        var type = $(this).attr('data-type');
        var id = $(this).attr('data-id');
        $.ajax({
            url: "{{ route('project.task.timer') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                type: type,
                id: id
            },
            dataType: 'JSON',
            success: function(data) {
                clearInterval(timer);
                $('.timer-counter').removeClass('d-block');
                if (data.status == 'success') {
                    main_div.find('.start-div').html(
                        '<div class="timer-counter"></div> <a href="#" class="stop-task finish_timer" data-type="stop" data-id="' +
                        id + '"><i class="far fa-clock"></i> {{ __('Stop Tracking') }}</a>');
                    TrackerTimer(data.start_time);

                }
                toastrs(data.class, data.msg, data.status);
            }
        });
    });

    // For task timer finished
    $(document).on("click", ".finish_timer", function() {
        var main_div = $(this).parent().parent().parent();
        var current = $(this);
        var type = $(this).attr('data-type');
        var id = $(this).attr('data-id');
        $.ajax({
            url: "{{ route('project.task.timer') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                type: type,
                id: id
            },
            dataType: 'JSON',
            success: function(data) {
                clearInterval(timer);
                $('.timer-counter').removeClass('d-block');
                if (data.status == 'success') {
                    main_div.find('.start-div').html(
                        ' <a href="#" class="start-task start_timer" data-type="start" data-id="' +
                        id + '"><i class="far fa-clock"></i> {{ __('Start Tracking') }} </a>');
                    $('.timer-counter').addClass('d-none');
                    setInterval(function() {
                        location.reload();
                    }, 1000);
                }
                toastrs(data.class, data.msg, data.status);
            }
        });
    });

    @if (!empty($lastTime))
        TrackerTimer("{{ $lastTime->start_time }}");
    @endif
</script>
