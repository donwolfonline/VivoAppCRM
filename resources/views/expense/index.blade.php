@extends('layouts.admin')
@php
    $attachment=\App\Models\Utility::get_file('uploads/attachment/');
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{__('Expense')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Expense')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Expense')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
        <a href="#" data-size="lg" data-url="{{ route('expense.create') }}"data-bs-toggle="modal" data-bs-target="#exampleModal" 
         data-bs-whatever="{{__('Create New Expense')}}"
         class="btn btn-sm btn-primary btn-icon m-1">
            <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Create') }}"></i>
        </a>
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
                                <th scope="col">{{__('Attachment')}}</th>
                                <th scope="col">{{__('Date')}}</th>
                                <th scope="col">{{__('Amount')}}</th>
                                <th scope="col">{{__('User')}}</th>
                                <th scope="col">{{__('Project')}}</th>
                                <th scope="col">{{__('Description')}}</th>
                                @if(\Auth::user()->type=='company')
                                    <th scope="col" class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $expense)
                                <tr class="font-style">
                                    <td>
                                        @if(!empty($expense->attachment))
                                        @php
                                            $x = pathinfo($expense->attachment, PATHINFO_FILENAME);
                                            $extension = pathinfo($expense->attachment, PATHINFO_EXTENSION);
                                            $result = str_replace(array("#", "'", ";"), '', $expense->attachment);
                                        
                                        @endphp
                                        <a  href="{{route('expense.receipt' , [$x,"$extension"]) }}"  data-toggle="tooltip" class="btn btn-sm btn-primary btn-icon rounded-pill">
                                            <i class="ti ti-download" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download') }}"></i>
                                        </a>
                                        <a  href="{{$attachment.$x.'.'.$extension}}" target="_blank" data-toggle="tooltip" class="btn btn-sm btn-secondary btn-icon rounded-pill">
                                            <i class="ti ti-crosshair" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>
                                        </a>
                                        @else
                                        -
                                        @endif

                                    </td>
                                    <td>{{  Auth::user()->dateFormat($expense->date)}}</td>
                                    <td>{{  Auth::user()->priceFormat($expense->amount)}}</td>
                                    <td>{{  (!empty($expense->users)?$expense->users->name:'')}}</td>
                                    <td>{{  !empty($expense->projects)?$expense->projects->title:''}}</td>

                                    <td>{{  $expense->description}}</td>
                                    @if(\Auth::user()->type=='company')
                                        <td class="action text-right">
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" data-size="lg" data-url="{{ route('expense.edit',$expense->id) }}"
                                                    class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#exampleModal"  data-bs-whatever="{{__('Edit Expense')}}">
                                                    <i class="ti ti-edit text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i>
                                                </a>
                                            </div>
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]) !!}
                                                <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
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

