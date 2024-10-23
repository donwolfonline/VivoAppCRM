@extends('layouts.admin')
@push('script-page')
<script>
    $(document).on('change', '#invoice', function() {

            var id = $(this).val();
            var url = "{{ route('invoice.get') }}";

            $.ajax({
                url: url,
                type: 'get',
                cache: false,
                data: {
                    'id': id,

                },
                success: function(data) {
                    $('#amount').val(data)
                },

            });

        })
</script>
@endpush
@section('page-title')
{{ __('Credit Notes') }}
@endsection
@section('title')
<div class="d-inline-block">
    <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Credit Notes') }}</h5>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item active" aria-current="page">{{ __('Credit Notes') }}</li>
@endsection
@section('action-btn')
@if (\Auth::user()->type == 'company')
<a href="{{ route('creditnote.export') }}" class="btn btn-sm btn-primary btn-icon m-1"
    data-bs-original-title="{{ __('Export credit notes csv file') }}" data-bs-toggle="tooltip">
    <i class="ti ti-file-export"></i>
</a>

<a href="#" data-size="lg" data-url="{{ route('creditNote.create') }}" data-bs-toggle="modal"
    data-bs-target="#exampleModal" data-bs-whatever="{{ __('Create Payment') }}"
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
                            <th scope="col">{{ __('Invoice') }}</th>
                            @if (\Auth::user()->type != 'client')
                            <th scope="col">{{ __('Client') }}</th>
                            @endif
                            <th scope="col">{{ __('Date') }}</th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Description') }}</th>
                            @if (\Auth::user()->type == 'company')
                            <th scope="col" class="text-right">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                        @php
                            $creditNotes = $invoice->creditNote;
                        @endphp
                        @if (!empty($creditNotes))
                        @foreach ($creditNotes as $creditNote)
                        <tr>
                            <td>
                                <div class="media-body">
                                    <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                        class="btn btn-outline-primary">
                                        {{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
                                </div>
                            </td>
                            @if (\Auth::user()->type != 'client')
                            <td>{{ !empty($invoice->clients) ? $invoice->clients->name : '' }}</td>
                            @endif
                            <td>{{ Auth::user()->dateFormat($creditNote->date) }}</td>
                            <td>{{ Auth::user()->priceFormat($creditNote->amount) }}</td>
                            <td>{{ $creditNote->description }}</td>
                            <td class="table-actions text-right">
                                <div class="action-btn bg-warning ms-2">
                                    <a href="{{ route('invoice.show', Crypt::encrypt($invoice->id)) }}"
                                        class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                        <i class="ti ti-eye text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('View') }}"></i>
                                    </a>
                                </div>
                                @if (\Auth::user()->type == 'company')
                                <div class="action-btn bg-info ms-2">
                                    <a href="#" data-size="lg"
                                        data-url="{{ route('creditNote.edit', $creditNote->id) }}"
                                        data-bs-toggle="modal" data-bs-target="#exampleModal"
                                        data-bs-whatever="{{ __('Edit Credit Note') }}"
                                        class="mx-3 btn btn-sm d-inline-flex align-items-center">
                                        <i class="ti ti-edit text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Edit') }}"></i>
                                    </a>
                                </div>
                                <div class="action-btn bg-danger ms-2">
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['creditNote.destroy',
                                    $creditNote->id]]) !!}
                                    <a href="#!" class="mx-3 btn btn-sm  align-items-center show_confirm ">
                                        <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Delete') }}"></i>
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection