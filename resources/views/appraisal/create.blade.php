{{ Form::open(['url' => 'appraisal', 'method' => 'post']) }}
@php 
$plansettings = App\Models\Utility::plansettings();
@endphp
<div class="row">
@if (isset($plansettings['enable_chatgpt']) && $plansettings['enable_chatgpt'] == 'on')
 <div class="text-end">

        <a href="#" data-size="lg" data-ajax-popup-over="true" data-url="{{ route('generate','appraisal') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate') }}" float-end>
            <span class="btn btn-primary btn-sm"> <i class="fas fa-robot">  {{ __('Generate With AI') }}</span></i>
        </a>
 </div>
 @endif
 
 <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('branch', __('Branch*'), ['class' => 'col-form-label']) }}
                <select name="branches" id="branches" required class="form-control branches">
                    <option selected disabled value="0">Select Branches</option>
                            @foreach ($branches as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6 mt-2">
            <div class="form-group">
                {{ Form::label('employee', __('Employee*'), ['class' => 'form-label']) }}
              
                <div class="employee_div">
                   
                    <select name="employee" id="employee" class="form-control " required>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('appraisal_date', __('Select Month*'), ['class' => 'col-form-label']) }}
                {{ Form::month('appraisal_date', '', ['class' => 'form-control current_date','autocomplete'=>'off' ,'required' => 'required', 'id' => 'current_month']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '3','placeholder'=>'Enter remark']) }}
            </div>
        </div>
    </div>
    <div class="row" id="stares">
    </div>
{{-- </div> --}}

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
  
<script>
    $(document).on( "change", "#employee", function() {
//$('#employee').change(function(){
          var emp_id = $('#employee').val();
          $.ajax({
            url: "{{ route('empByStar') }}",
            type: "post",
            data:{
                "employee": emp_id,
                "_token": "{{ csrf_token() }}",
            },
            cache: false,
            success: function(data) {
                 $('#stares').html(data.html);
            }
        })
    });
    
//-----------Get Employee--------------------------------------- 
  $(document).on( "change", "#branches", function() {
     var branch_id = this.value;
      
    $.ajax({
            url: "{{ route('getemployee') }}",
            type: "post",
            data:{
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {
                $('#employee').html('<option value="">Select Employee</option>');
                $.each(data.employee, function (key, value) {
                     $("#employee").append('<option value="' + value.id + '">' + value.name + '</option>');
                });

            }
        })
    });
    
 </script>

<script>
document.getElementById('current_month').valueAsDate = new Date();
</script>
 