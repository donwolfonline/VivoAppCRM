    @extends('layouts.admin')
@php
    $profile=\App\Models\Utility::get_file('uploads/avatar/');
    // $profile=asset(Storage::url('uploads/avatar'));
@endphp
@push('script-page')
@endpush
@section('page-title')
    {{__('Project Reports')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0">{{__('Project Reports')}}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{__('All Project')}}</li>
@endsection
@push('css-page')
<link rel="stylesheet" href="{{ asset('public/custom/css/datatables.min.css') }}">

<style>
.table.dataTable.no-footer {
    border-bottom: none !important;
} 
.display-none {
    display: none !important;
}
.dataTables_length {
    margin: 20px;
}
.dataTables_filter {
    margin: 20px;
}
</style>
@endpush

@section('content')

<div class="col-sm-12">
            <div class=" {{isset($_GET['start_month'])?'show':''}}" >
                <div class="card card-body">
                    <div class="row filter-css ">
                        <div class="form-group col-2">
                            <select class="select2 form-select" name="all_users" id="all_users">
                                <option value="" class="px-4">{{ __('All Users') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-3">
                            <select class="select form-select" name="status" id="status">
                                    <option value="" class="px-4">{{ __('All Status') }}</option>
                                    <option value="not_started">{{ __('Not Started')}}</option>
                                    <option value="in_progress">{{ __('In Progress')}}</option>
                                    <option value="on_hold">{{ __('On Hold')}}</option>
                                    <option value="canceled">{{ __('Canceled')}}</option>
                                    <option value="finished">{{ __('Finished')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">  
                                <div class="input-group date ">
                                <input class="form-control" type="date" id="start_date" name="start_date" value="" autocomplete="off" required="required"  placeholder="{{ __('Start Date') }}">
                            </div>
                        </div>
                            <div class="form-group col-md-3">
                            <div class="input-group date ">
                                    <input class="form-control" type="date" id="due_date" name="due_date" value="" autocomplete="off" required="required" placeholder="{{ __('End Date') }}">
                                </div>
                            </div>
                            <div class="action-btn bg-info mb-3 ms-2">
                            <div class="col-auto">
                                <button type="submit" class="mx-3 btn btn-sm d-flex align-items-center btn-filter apply" data-bs-toggle="tooltip"
                                title="{{__('Apply')}}"><i class="ti ti-search text-white"></i></button>
                            </div>
                        </div>
                
                        <div class="action-btn bg-danger mb-3 ms-2">
                            <div class="col-auto">
                                <a href="{{route('project_report.index')}}" data-bs-toggle="tooltip" title="{{__('Reset')}}" 
                                class="mx-3 btn btn-sm d-flex align-items-center"><i class="ti ti-trash-off text-white"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="col-xl-12 mt-3 ">
        <div class="card">
            <div class="card-header card-body table-border-style">
                <div class="table-responsive">
                    <table class="table" id="selection-datatable1">
                        <thead class="">
                            <tr>
                                <th>{{__('Projects')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Projects Members')}}</th>
                                <th>{{__('Completion')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('script-page')
    <!-- <script>
        (function () {
        const d_week = new Datepicker(document.querySelector(''), {
            buttonClass: 'btn',
            todayBtn: true,
            clearBtn: true,
            format: 'yyyy-mm-dd',
        });
    })();
    </script> -->

   <script src="{{ asset('public/custom/js/jquery.dataTables.min.js') }}"></script>
   <script>

    var dataTableLang = {
        paginate: {previous: "<i class='fas fa-angle-left'>", next: "<i class='fas fa-angle-right'>"},
        lengthMenu: "{{__('Show')}} _MENU_ {{__('entries')}}",
        zeroRecords: "{{__('No data available in table.')}}",
        info: "{{__('Showing')}} _START_ {{__('to')}} _END_ {{__('of')}} _TOTAL_ {{__('entries')}}",
        infoEmpty: "{{ __('Showing 0 to 0 of 0 entries') }}",
        infoFiltered: "{{ __('(filtered from _MAX_ total entries)') }}",
        search: "{{__('Search:')}}",
        thousands: ",",
        loadingRecords: "{{ __('Loading...') }}",
        processing: "{{ __('Processing...') }}"
    }
    </script>
    <script type="text/javascript">
        $(".filter").click(function() {
            $("#show_filter").toggleClass('display-none');
        });
    </script>
    <script>

$(document).on('click', '#form-comment', function(e) {
            var comment = $.trim($("#form-comment input[name='comment']").val());
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
                        console.log(data);
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
                toastrs('Error', '{{ __('Please write comment.') }}', 'error');
            }
        });
        $(document).on("click", ".delete-comment", function() {
            if (confirm('Are You Sure ?')) {
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
    </script>
    <script>
            $(document).ready(function() {

                    var table = $("#selection-datatable1").DataTable({
                    order: [],
                    select: {
                        style: "multi"
                    },
                    "language": dataTableLang,
                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                    }
                    });
                    $(document).on("click", ".btn-filter", function() {

                    getData();
                    });

                    function getData() {
                        table.clear().draw();
                        $("#selection-datatable1 tbody tr").html('<td colspan="11" class="text-center"> {{ __('Loading ...') }}</td>');

                        var data = {
                            status: $("#status").val(),
                            start_date: $("#start_date").val(),
                            due_date  : $("#due_date").val(),
                            all_users :$("#all_users").val(),
                           
                        };
                        $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                        $.ajax({
                            url: '{{ route('projects.ajax') }}',
                            type: 'POST',
                            data: data,
                            success: function(data) {  
                                table.rows.add(data.data).draw(true);
                               
                            },
                            error: function(data) {
                                toastrs('Info', data.error, 'error')
                            }
                        })
                    }

                getData();

            });
   </script>

@endpush




