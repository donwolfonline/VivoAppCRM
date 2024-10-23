@extends('layouts.admin')
@php
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('css-page')
@endpush
@push('script-page')
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#useradd-sidenav',
            offset: 300
        })
    </script>
@endpush
@section('page-title')
    {{ __('Employee Edit') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0"> {{ __('Employee Edit') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection
@section('action-btn')
@endsection


@section('content')
    <div class="row">
        <!-- [ sample-page ] start -->
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    <div class="card sticky-top" style="top:30px">
                        <div class="list-group list-group-flush" id="useradd-sidenav">
                            <a href="#useradd-1"
                                class="list-group-item list-group-item-action border-0">{{ __('Personal Info') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-2"
                                class="list-group-item list-group-item-action border-0">{{ __('Company Info') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                            <a href="#useradd-3"
                                class="list-group-item list-group-item-action border-0">{{ __('Bank Info') }} <div
                                    class="float-end"><i class="ti ti-chevron-right"></i></div></a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9">
                    <div id="useradd-1" class="card">
                        {{ Form::model($employee, ['route' => ['employee.personal.update', $employee->user_id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <h5>{{ __('Personal Info') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your personal information') }}</small>
                        </div>

                        <div class="card-body">
                            <form>
                                <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                            {{ Form::text('name', $user->name, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Name']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('mobile', __('Mobile'), ['class' => 'form-label']) }}
                                            {{ Form::number('mobile', $employee->mobile, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Mobile']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('emergency_contact', __('Emergency Contact'), ['class' => 'form-label']) }}
                                            {{ Form::text('emergency_contact', $employee->emergency_contact, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'Enter Emergency Contact']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
                                            {!! Form::date('dob', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="gender"
                                                            value="Male" id="customCheckinlh1"
                                                            {{ $employee->gender == 'Male' ? 'checked' : 'checked' }}>
                                                        <label class="form-check-label" for="customCheckinlh1">
                                                            {{ __('Male') }}
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="gender"
                                                            value="Female" id="customCheckinlh2"
                                                            {{ $employee->gender == 'Female' ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="customCheckinlh2">
                                                            {{ __('Female') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card bg-gradient-primary hover-shadow-lg border-0">
                                            <div class="card-body py-3">
                                                <div class="row row-grid align-items-center">
                                                    <div class="col-lg-8">
                                                        <div class="media align-items-center">
                                                            <a href="#" class="avatar avatar-lg rounded-circle mr-3">
                                                                <img @if (!empty($user->avatar)) src="{{ $profile . '/' . $user->avatar }}" @else avatar="{{ $user->name }}" @endif
                                                                    class="avatar  rounded-circle avatar-lg"
                                                                    style="width:60px">
                                                            </a>
                                                            <div class="media-body ms-3">
                                                                <h5 class="text-dark mb-2">{{ $user->name }}</h5>
                                                                <div>
                                                                    <div class="input-group">
                                                                        <input type="file" class="form-control"
                                                                            id="file-1" name="profile"
                                                                            aria-describedby="inputGroupFileAddon04"
                                                                            aria-label="Upload"
                                                                            data-multiple-caption="{count} files selected"
                                                                            multiple />
                                                                    </div>


                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                            {{ Form::textarea('address', $employee->address, ['class' => 'form-control', 'required' => 'required', 'rows' => '3']) }}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
                                    </div>


                                </div>
                            </form>
                        </div>
                        {{ Form::close() }}
                    </div>



                    <div id="useradd-2" class="card">
                        {{ Form::model($employee, ['route' => ['employee.company.update', $employee->user_id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                        <div class="card-header">
                            <h5>{{ __('Company Info') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your company information') }}</small>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row mt-3">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            {!! Form::label('emp_id', __('Employee ID'), ['class' => 'form-label']) !!}
                                            {!! Form::text('emp_id', $employeesId, ['class' => 'form-control', 'readonly']) !!}
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'branch_id']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('department_id', __('Department'), ['class' => 'form-label', 'placeholder' => 'Select Department']) }}
                                        <select class=" select form-control " id="department_id" name="department_id"
                                            required="required">
                                            <option value="">{{ __('Select any Department') }}</option>
                                            @foreach ($departmentData as $key => $val)
                                                <option value="{{ $key }}"
                                                    {{ $key == $employee->department ? 'selected' : '' }}>
                                                    {{ $val }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                        <select class="select form-control " id="designation_id" name="designation_id"
                                            required="required"></select>
                                    </div>

                                    <div class="col-sm-6">
                                        <div cla`s="form-group">
                                            {!! Form::label('joining_date', __('Date of Joining'), ['class' => 'form-label']) !!}
                                            {!! Form::date('joining_date', null, ['class' => 'form-control', 'required' => 'required']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {!! Form::label('exit_date', __('Date of Exit'), ['class' => 'form-label']) !!}
                                            {!! Form::date('exit_date', !empty($employee->exit_date) ? null : '', [
                                                'class' => 'form-control',
                                                'required' => 'required',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            {{ Form::label('salary_type', __('Salary Type'), ['class' => 'form-label']) }}
                                            {{ Form::select('salary_type', $salaryType, null, ['class' => 'form-control multi-select']) }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {!! Form::label('salary', __('Salary'), ['class' => 'form-label']) !!}
                                            {!! Form::number('salary', null, ['class' => 'form-control']) !!}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{ Form::close() }}
                    </div>



                    <div id="useradd-3" class="card">
                        {{ Form::model($employee, ['route' => ['employee.bank.update', $employee->user_id], 'method' => 'post']) }}
                        <div class="card-header">
                            <h5>{{ __('Bank Info') }}</h5>
                            <small class="text-muted">{{ __('Edit details about your bank information') }}</small>
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {!! Form::label('account_holder_name', __('Account Holder Name'), ['class' => 'form-label']) !!}
                                            {!! Form::text('account_holder_name', null, [
                                                'class' => 'form-control',
                                                'required',
                                                'placeholder' => 'Account Holder Name',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('account_number', __('Account Number'), ['class' => 'form-label']) }}
                                            {!! Form::text('account_number', null, [
                                                'class' => 'form-control',
                                                'required',
                                                'placeholder' => 'Account Number',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {{ Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) }}
                                            {!! Form::text('bank_name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Bank Name']) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            {!! Form::label('bank_identifier_code', __('Bank Identifier Code'), ['class' => 'form-label']) !!}
                                            {!! Form::text('bank_identifier_code', null, [
                                                'class' => 'form-control',
                                                'required',
                                                'placeholder' => 'Bank Identifier Code',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            {!! Form::label('branch_location', __('Branch Location'), ['class' => 'form-label']) !!}
                                            {!! Form::text('branch_location', null, [
                                                'class' => 'form-control',
                                                'required',
                                                'placeholder' => 'Branch Location',
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Update'), ['class' => 'btn btn-primary d-flex align-items-center']) }}
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
            <!-- [ sample-page ] end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>
@endsection

@push('script-page')
    <script type="text/javascript">
        $(document).on('change', '#branch_id', function() {
            var branch_id = $(this).val();
            getDepartment(branch_id);
        });

        function getDepartment(branch_id) {
            var data = {
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            }

            $.ajax({
                url: '{{ route('employee.getdepartment') }}',
                method: 'POST',
                data: data,
                success: function(data) {
                    $('#department_id').empty();
                    $('#department_id').append(
                        '<option value="" disabled>{{ __('Select any Department') }}</option>');

                    $.each(data, function(key, value) {
                        $('#department_id').append('<option value="' + key + '">' + value +
                            '</option>');
                    });
                    $('#department_id').val('');
                }
            });
        }
    </script>
    <script type="text/javascript">
        function getDesignation(did) {
            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    "department_id": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#designation_id').empty();
                    $('#designation_id').append(
                        '<option value="">{{ __('Select any Designation') }}</option>');

                    $.each(data, function(key, value) {
                        var select = '';
                        if (key == '{{ $employee->designation }}') {
                            select = 'selected';
                        }

                        $('#designation_id').append('<option value="' + key + '"  ' + select + '>' +
                            value + '</option>');
                    });
                }
            });
        }

        $(document).ready(function() {
            var d_id = $('#department_id').val();
            var designation_id = '{{ $employee->designation }}';
            getDesignation(d_id);
        });

        $(document).on('change', 'select[name=department_id]', function() {
            var department_id = $(this).val();
            getDesignation(department_id);
        });
    </script>
@endpush
