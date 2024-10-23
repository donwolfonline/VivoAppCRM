{{ Form::open(['url' => 'support', 'enctype' => 'multipart/form-data']) }}

@php
    $plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
    @if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
        <div class="text-end">
            <a href="#" data-size="lg" data-ajax-popup-over="true" data-bs-toggle="tooltip" data-bs-placement="top"
                title="{{ __('Generate') }}" data-url="{{ route('generate', ['support']) }}"
                data-title="{{ __('Generate') }}" float-end>
                <span class="btn btn-primary btn-sm"> <i class="fas fa-robot"> {{ __('Generate With AI') }}</span></i>
            </a>
        </div>
    @endif

    <div class="form-group col-md-12">
        {{ Form::label('subject', __('Subject')) }}
        {{ Form::text('subject', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Subject')]) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('user', __('Support for User')) }}
        {{ Form::select('user', $users, null, ['class' => 'form-control multi-select', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('priority', __('Priority')) }}
        {{ Form::select('priority', $priority, null, ['class' => 'form-control multi-select', 'required' => 'required']) }}
    </div>
    <div class="form-group col-md-6">
        {{ Form::label('attachment', __('Attachment')) }}
        {{ Form::file('attachment', ['class' => 'form-control file-validate', 'id' => 'files','data-filename' => 'upload_file']) }}
        <span id="" class="file-error text-danger"></span>
        <p class="upload_file"></p>
        <img id="image" class="mt-2" src="{{asset(Storage::url('uploads/logo/logo-dark.png'))}}" style="width:25%;"/>
    </div>

    <div class="form-group col-md-6">
        {{ Form::label('end_date', __('End Date')) }}
        {{ Form::date('end_date', new \DateTime(), ['class' => 'form-control', 'required' => 'required']) }}
    </div>
</div>
<div class="row">
    <div class="form-group col-md-12">
        {{ Form::label('description', __('Description')) }}
        {!! Form::textarea('description', null, [
            'class' => 'form-control',
            'required' => 'required',
            'rows' => '3',
            'placeholder' => __('Enter Description'),
        ]) !!}
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn  btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn  btn-primary']) }}
</div>
{{ Form::close() }}


<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>
<script>
    document.getElementById('files').onchange = function() {
        var src = URL.createObjectURL(this.files[0])
        document.getElementById('image').src = src
    }
</script>
