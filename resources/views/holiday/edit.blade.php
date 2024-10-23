<div class="row">
    @php 
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
   @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
     <div class="text-end">
        <a href="#" data-size="md" class="btn btn-sm btn-primary" data-ajax-popup-over="true" data-size="md"
            data-title="{{ __('Generate Content Width Ai') }}" data-url="{{ route('generate', ['holiday']) }}"
            data-toggle="tooltip" title="{{ __('Generate') }}">
            <i class="fas fa-robot"> {{ __('Generate With AI') }}</i>
        </a>
    </div>
    @endif    
    {{ Form::model($holiday, ['route' => ['holiday.update', $holiday->id], 'method' => 'PUT']) }}
    <div class="form-group">
        {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
        {{ Form::date('date', null, ['class' => 'form-control']) }}
    </div>

    <div class="form-group">
        {{ Form::label('occasion', __('Occasion'), ['class' => 'col-form-label']) }}
        {{ Form::text('occasion', null, ['class' => 'form-control','placeholder'=>__('Occasion')]) }}
    </div>

    <div class="row">
        <!-- <div class="text-left col-6">
            <div class="form-group action-btn bg-danger">
                {!! Form::open(['method' => 'DELETE', 'route' => ['holiday.destroy', $holiday->id]]) !!}
                <a href="#!" class="mx-3 btn btn-sm d-flex align-items-center show_confirm">
                    <i class="ti ti-trash text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Delete') }}"></i>
                </a>
                {!! Form::close() !!}
            </div>
        </div> -->

        <div class="text-end col-6">
            <button type="button" class="btn text-end btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
            @if (\Auth::user()->type == 'company')
            {{ Form::submit(__('Update'), ['class' => 'btn text-end btn-primary']) }}
            @endif
        </div>
    </div>

    {{ Form::close() }}
</div>


<script>
    $(".show_confirm").click(function() {
        $("#exampleModal").modal('hide');
    });
</script>
