<div class="modal-body">
    <ul class="list-unstyled">
        <li class="mb-2"><strong class="text-dark">{{ __('Title')}} :</strong> &nbsp; {{$task->title}}</li>
        <li class="mb-2"><strong class="text-dark">{{ __('Priority')}} :</strong> &nbsp;
            {{ucfirst($task->priority)}}</li>
        <li class="mb-2"><strong class="text-dark">{{ __('Description')}} :</strong> &nbsp; {{$task->description}}</li>
        <li class="mb-2"><strong class="text-dark">{{ __('Start Date')}}  :</strong> &nbsp; {{$task->start_date}}</li>
        <li class="mb-2"><strong class="text-dark">{{ __('Due Date')}}  :</strong> &nbsp; {{$task->due_date}}</li>
        <li class="mb-2"><strong class="text-dark">{{ __('Milestone')}} :</strong> &nbsp; {{!empty($task->milestone)?$task->milestone->title:''}}</li>
    </ul>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mb-3" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active text-uppercase" id="checklist-tab" data-bs-toggle="tab" href="#checklist" role="tab" aria-controls="checklist" aria-selected="true">{{__('Checklist')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase" id="comment-tab" data-bs-toggle="tab" href="#comment" role="tab" aria-controls="comment" aria-selected="true">{{__('Comments')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-uppercase" id="files-tab" data-bs-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">{{__('Files')}}</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="checklist" role="tabpanel" aria-labelledby="checklist-tab">
                    @can('create checklist')
                        @if(\Auth::user()->type!='client' || (\Auth::user()->type=='client' && in_array('show checklist',$perArr)))
                            <div class="tab-pane fad active px-2" id="tab_1_3">
                                <div class="row">
                                    @if(\Auth::user()->type!='client' || (\Auth::user()->type=='client' && in_array('create checklist',$perArr)))
                                        <div class="col-md-11">
                                            <div class="row mb-10">
                                                <div class="col-md-6 font-weight-bold text-sm  mb-1">{{__('Progress')}}</div>
                                                <div class="col-md-6 font-weight-bold text-sm text-end">
                                                    <div class="progress-wrap">
                                                        <span class="progressbar-label custom-label">0%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-left">
                                                <div class="custom-widget__item flex-fill">
                                                    <div class="custom-widget__progress d-flex  align-items-center">
                                                        <div class="progress" style="height: 5px;width: 100%;">
                                                            <div class="progress-bar" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" id="taskProgress"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <a class="btn btn-sm btn-primary btn-icon" data-bs-toggle="collapse" href="#form-checklist" role="button" aria-expanded="false" aria-controls="collapseExample">
                                               <i class="ti ti-plus"></i>
                                            </a>
                                        </div>

                                    @endif
                                    <form method="POST" id="form-checklist" class="collapse col-md-12 pt-2" data-action="{{ route('task.checklist.store',[$task->id]) }}">
                                      @csrf
                                        <div class="form-group">
                                            <label class="col-form-label">{{__('Name')}}</label>
                                            <input type="text" name="name" class="form-control checklist-name" required placeholder="{{__('Checklist Name')}}">
                                        </div>
                                        <div class="text-right">
                                            <div class="btn-group mb-2 ml-2 d-none d-sm-inline-block">
                                                <button type="submit" class="btn btn-primary submit-checklist">{{ __('Create')}}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="row">
                                    <ul class="col-md-12 mt-3 text-sm" id="check-list">
                                        @foreach($task->taskCheckList as $checkList)
                                            <li class="media">
                                                <div class="media-body">
                                                    <h5 class="mt-0 mb-1 font-weight-bold"></h5>
                                                    <div class="row">
                                                        <div class="col-8">
                                                            <div class="form-check form-check-inline">
                                                                @can('create checklist')
                                                                    @if(\Auth::user()->type!='client' || (\Auth::user()->type=='client' && in_array('edit checklist',$perArr)))
                                                                    <input class="form-check-input" type="checkbox" value="" id="checklist-{{$checkList->id}}" {{($checkList->status==1)?'checked':''}} value="{{$checkList->id}}" data-url="{{route('task.checklist.update',[$checkList->task_id,$checkList->id])}}">
                                                                    <label class="form-check-label" for="checklist-{{$checkList->id}}">
                                                                        {{$checkList->name}}
                                                                    </label>
                                                                    @else
                                                                        <p class="mb-0">{{$checkList->name}}</p>
                                                                    @endif
                                                                @endcan
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="comment-trash text-right">
                                                                @if(\Auth::user()->type!='client' || (\Auth::user()->type=='client' && in_array('delete checklist',$perArr)))
                                                                    <a href="#" class="btn btn-outline btn-sm text-danger delete-checklist" data-url="{{route('task.checklist.destroy',[$checkList->task_id,$checkList->id])}}">
                                                                        <i class="ti ti-trash"></i>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>
                <div class="tab-pane fade" id="comment" role="tabpanel" aria-labelledby="comment-tab">
                    <div class="form-group m-0">
                        <form method="post" id="form-comment" data-action="{{route('comment.store',[$task->project_id,$task->id])}}">
                            <textarea class="form-control" name="comment" placeholder="{{ __('Write message')}}" id="example-textarea" rows="3" required></textarea>
                            <br>
                            <div class="text-end mt-10">
                                <div class="btn-group mb-2 ml-2 d-none d-sm-inline-block">
                                    <button type="button" class="btn btn-primary">{{ __('Submit')}}</button>
                                </div>
                            </div>
                        </form>
                        <div class="comment-holder" id="comments">
                            @foreach($task->comments as $comment)
                                <div class="media">
                                    <div class="media-body">
                                        <div class="d-flex justify-content-between align-items-end">
                                            <div>
                                                <h5 class="mt-0">{{(!empty($comment->user)?$comment->user->name:'')}}</h5>
                                                <p class="mb-0 text-xs">{{$comment->comment}}</p>
                                            </div>
                                            <a href="#" class="btn btn-outline btn-sm bg-danger delete-comment mx-3 d-inline-flex align-items-center" data-url="{{route('comment.destroy',$comment->id)}}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                    <div class="form-group m-0">
                        <form method="post" id="form-file" enctype="multipart/form-data" data-url="{{ route('comment.file.store',$task->id) }}">
                            @csrf
                            <div class="choose-file form-group">
                                <label for="file" class="form-control-label">
                                    <div>{{__('Choose file here')}}</div>
                                    <input type="file" class="form-control" name="file" id="file" onchange="document.getElementById('imgs').src = window.URL.createObjectURL(this.files[0])">
                                    <img src="" id="imgs" class="mt-2" width="25%"/>
                                </label>
                                <p class="file_update"></p>
                            </div>
                            <span class="invalid-feedback" id="file-error" role="alert"></span>
                            <div class="text-end mt-10">
                                <div class="btn-group mb-2 ml-2 d-none d-sm-inline-block">
                                    <button type="submit" class="btn btn-primary">{{ __('Upload')}}</button>
                                </div>
                            </div>

                        </form>
                        <div class="row my-3" id="comments-file">
                            @foreach($task->taskFiles as $file)
                                <div class="col-7 mb-2 file-{{$file->id}}">
                                    <h5 class="mt-0 mb-1 font-weight-bold text-sm"> {{$file->name}}</h5>
                                    <p class="m-0 text-xs">{{$file->file_size}}</p>
                                </div>
                                <div class="col-5 mb-2 file-{{$file->id}}">
                                    @php
                                    $file_storage=\App\Models\Utility::get_file('tasks/');
                                    @endphp
                                    <div class="comment-trash" style="float: right">
                                        {{ number_format(\File::size(storage_path('tasks/' .$file->file)) / 1048576, 2) . ' ' . __('MB') }}
                                        <a download href="{{$file_storage.'/'.$file->file}}" class="btn btn-outline btn-sm text-primary m-0 px-2">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <a href="#" class="btn btn-outline btn-sm red text-danger delete-comment-file m-0 px-2" data-id="{{$file->id}}" data-url="{{route('comment.file.destroy',[$file->id])}}">
                                            <i class="ti ti-trash"></i>
                                        </a>
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
