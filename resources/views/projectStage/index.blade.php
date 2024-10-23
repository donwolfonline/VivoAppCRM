@extends('layouts.admin')
@push('script-page')

<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('public/custom_assets/js/jquery-ui.min.js') }}"></script>

@if(\Auth::user()->type=='company')
<script>
    $(function() {
        $(".sortable").sortable();
        $(".sortable").disableSelection();
        $(".sortable").sortable({
            stop: function() {
                var order = [];
                $(this).find('li').each(function(index, data) {
                    order[index] = $(data).attr('data-id');
                });

                $.ajax({
                    url: "{{route('projectStage.order')}}"
                    , data: {
                        order: order
                        , _token: $('meta[name="csrf-token"]').attr('content')
                    }
                    , type: 'POST'
                    , success: function(data) {

                    }
                    , error: function(data) {
                        data = data.responseJSON;
                        toastr('Error', data.error, 'error')
                    }
                })
            }
        });
    });

</script>
@endif
@endpush
@section('page-title')
{{__('Project Task Stage')}}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Project Task Stage')}}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{__('Project Task Stage')}}</li>
@endsection
@section('action-btn')
@if(\Auth::user()->type=='company')
<a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal" data-bs-target="#exampleModal" data-url="{{ route('projectStage.create') }}" data-bs-whatever="{{__('Create New Project Stage')}}"> <span class="text-white">
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i></span>
</a>


@endif
@endsection
@section('filter')
@endsection
@section('content')
<div class="card">

    <div class="card-body">
        <div class="tab-pane fade show" id="tab" role="tabpanel">
            <ul class="list-group sortable">
                @forelse ($projectStages as $project_stages)
                <li class="d-flex align-items-center justify-content-between list-group-item" data-id="{{$project_stages->id}}">
                    <h6 class="mb-0">
                        <i class="me-3 ti ti-arrows-maximize " data-feather="move"></i>
                        {{$project_stages->name}}
                    </h6>
                    @if(\Auth::user()->type=='company')
                    <span class="float-end">
                        <div class="action-btn bg-info ms-2">
                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('projectStage.edit',$project_stages->id) }}" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="{{__('Edit Project Stage')}}" data-size="md">
                                <i class="ti ti-edit text-white" data-bs-toggle="tooltip" title="{{__('Edit')}}"></i>
                            </a>
                        </div>

                        <div class="action-btn bg-danger ms-2">
                            {!! Form::open(['method' => 'DELETE', 'route' => ['projectStage.destroy', $project_stages->id]]) !!}
                            <a href="#" class="mx-3 btn btn-sm  align-items-center show_confirm" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                            {!! Form::close() !!}
                        </div>
                    </span>
                    @endif
                </li>
                @empty
                <div class="col-md-12 text-center">
                    <h4>{{__('No data available')}}</h4>
                </div>
                @endforelse

            </ul>
        </div>
        <p class="text-muted mt-4"><strong>{{__('Note')}} : </strong>{{__('You can easily order change of project task stage using drag & drop.')}}</p>
    </div>
</div>
@endsection

