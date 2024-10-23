@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{__('Appraisal')}}
@endsection

@push('css-page')
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push('script-page')
<script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
<script>
$('document').ready(function() {
    $('.toggleswitch').bootstrapToggle();
    $("fieldset[id^='demo'] .stars").click(function() {
        alert($(this).val());
        $(this).attr("checked");
    });
});
</script>

@endpush
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Appraisal')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Appraisal')}}</li>
@endsection
@section('action-btn')
    @if(\Auth::user()->type=='company')
    <a href="#" class="btn btn-sm btn-primary btn-icon m-1" data-size="lg" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-url="{{ route('appraisal.create') }}"
    data-bs-whatever="{{__('Create New Appraisal')}}">
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
                                <th>{{ __('Branch') }}</th>
                                <th>{{__('Department')}}</th>
                                <th>{{__('Designation')}}</th>
                                <th>{{__('Employee')}}</th>
                                <th>{{ __('Target Rating') }}</th>
                                <th>{{__('Overall Rating')}}</th>
                                <th>{{__('Appraisal Date')}}</th>
                                @if(\Auth::user()->type=='company')
                                    <th class="text-right">{{__('Action')}}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($appraisals as $appraisal)
                                @php
                                    $designation=!empty($appraisal->employees) ?  $appraisal->designation_id : '-';
                                    $targetRating =  \App\Models\Utility::getTargetrating($designation,$competencyCount);

                                    if(!empty($appraisal->rating)&&($competencyCount!=0))
                                    {
                                        $rating = json_decode($appraisal->rating,true);
                                        $starsum = is_array($rating) ? array_sum($rating) : 0;
                                        $overallrating = $starsum/$competencyCount;
                                    }
                                    else{
                                        $overallrating = 0;
                                    }
                                @endphp
                                    @php
                                        if (!empty($appraisal->rating)) {
                                            $rating = json_decode($appraisal->rating, true);
                                            if (!empty($rating)) {
                                                $starsum = array_sum($rating);
                                                $overallrating = $starsum / count($rating);
                                            } else {
                                                $overallrating = 0;
                                            }
                                        } else {
                                            $overallrating = 0;
                                        }
                                    @endphp
                                <tr>
                                    <td>{{ !empty($appraisal->branches) ? $appraisal->branches->name : '' }}</td>
                                    <td>{{ !empty($appraisal->department_name) ?  $appraisal->department_name : '-'}}</td>
                                    <td>{{ !empty($appraisal->designation_name) ?  $appraisal->designation_name : '-' }}</td>
                                    <td>{{ !empty($appraisal->employees) ? $appraisal->employees->name : '-' }}</td>
                                    <td>
                                        @for($i=1; $i<=5; $i++)
                                            @if($targetRating < $i)
                                            @if(is_float($targetRating) && (round($targetRating) == $i))
                                            <i class="text-warning fas fa-star-half-alt"></i>
                                            @else
                                            <i class="fas fa-star"></i>
                                            @endif
                                            @else
                                            <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor

                                        <span class="theme-text-color">({{number_format($targetRating,1)}})</span>
                                    </td>
                                    <td>

                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($overallrating < $i)
                                                @if (is_float($overallrating) && round($overallrating) == $i)
                                                    <i class="text-warning fas fa-star-half-alt"></i>
                                                @else
                                                    <i class="fas fa-star"></i>
                                                @endif
                                            @else
                                                <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor
                                        <span class="theme-text-color">({{ number_format($overallrating, 1) }})</span>
                                    </td>
                                    <td>{{ $appraisal->appraisal_date }}</td>
                                        
                                        @if(\Auth::user()->type=='company')
                                            <td class="text-right">
                                                <div class="action-btn bg-warning ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-size="lg" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal" data-url="{{ route('appraisal.show',$appraisal->id) }}"
                                                    data-bs-whatever="{{__('Appraisal Detail')}}"> <span class="text-white"> <i
                                                            class="ti ti-eye" data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"></i></span></a>
                                                </div>

                                                <div class="action-btn bg-info ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-size="lg" data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal" data-url="{{ route('appraisal.edit',$appraisal->id) }}"
                                                    data-bs-whatever="{{__('Edit Appraisal')}}"> <span class="text-white"> <i
                                                            class="ti ti-edit" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Edit') }}"></i></span></a>
                                                </div>

                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['appraisal.destroy', $appraisal->id]]) !!}
                                                    <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('delete') }}"></i>
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




