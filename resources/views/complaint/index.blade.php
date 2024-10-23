 @extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Complaint')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Complaint')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Complaint')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('complaint.create') }}" data-size="lg"
    data-bs-whatever="{{__('Create New Complaint')}}">
        <i class="ti ti-plus text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
    </a>
    @endif

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
                                <th>{{__('Complaint From')}}</th>
                                <th>{{__('Complaint Against')}}</th>
                                <th>{{__('Title')}}</th>
                                <th>{{__('Complaint Date')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                                    <th class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($complaints as $complaint)
                            <tr>
                                <td>{{!empty( $complaint->complaintFrom)? $complaint->complaintFrom->name:'--' }}</td>
                                <td>{{ !empty($complaint->complaintAgainst)?$complaint->complaintAgainst->name:'--' }}</td>
                                <td>{{ $complaint->title }}</td>
                                <td>{{ \Auth::user()->dateFormat( $complaint->complaint_date) }}</td>
                                <td>{{ $complaint->description }}</td>
                                @if(\Auth::user()->type=='company' || \Auth::user()->type=='employee')
                                    <td class="text-right">
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal"
                                                data-bs-target="#exampleModal" data-url="{{ route('complaint.edit',$complaint->id) }}" data-size="lg"
                                                data-bs-whatever="{{__('Edit Complaint')}}"> <span class="text-white"> <i
                                                        class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                        </div>

                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['complaint.destroy', $complaint->id]]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm d-inline-flex align-items-center show_confirm m-2">
                                                <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
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

