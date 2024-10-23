@php
     $password = base64_decode($project->password);
    //  dd($result->files);
@endphp
<div class="card-body">
    <div class="table-responsive">
        {{ Form::open(['route' => ['project.copy.link', $projectID], 'method' => 'POST']) }}
        <table class="table mb-0">
            <thead class="thead-light">
                <tr>
                    <th> {{ __('Name') }}</th>
                    <th class="text-right"> {{ __('On/Off') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('Basic details') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="basic_details" class="form-check-input"
                                @if (isset($result->basic_details) && $result->basic_details == 'on') checked="checked" @endif id="copy_link_1"
                                value="on">
                            <label class="custom-control-label" for="copy_link_1"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Task List') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="task" class="form-check-input" id="copy_link_2"
                                @if (isset($result->task) && $result->task == 'on') checked="checked" @endif value="on">
                            <label class="custom-control-label" for="copy_link_2"></label>
                        </div>
                    </td>
                </tr>

                 <tr>
                    <td>{{ __('Gantt Chart') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="ganttTasks" class="form-check-input" id="copy_link_3"
                                @if (isset($result->ganttTasks) && $result->ganttTasks == 'on') checked="checked" @endif value="on">
                            <label class="custom-control-label" for="copy_link_3"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Milestone') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="milestone" class="form-check-input"
                                @if (isset($result->milestone) && $result->milestone == 'on') checked="checked" @endif id="copy_link_4"
                                value="on">
                            <label class="custom-control-label" for="copy_link_4"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Notes') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="note" class="form-check-input"
                                @if (isset($result->note) && $result->note == 'on') checked="checked" @endif id="copy_link_5"
                                value="on">
                            <label class="custom-control-label" for="copy_link_5"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Files') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="files" class="form-check-input"
                                @if (isset($result->files) && $result->files == 'on') checked="checked" @endif id="copy_link_5"
                                value="on">
                            <label class="custom-control-label" for="copy_link_5"></label>
                        </div> 
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Comments') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="comments" class="form-check-input" id="copy_link_7"
                                @if (isset($result->comments) && $result->comments == 'on') checked="checked" @endif value="on">
                            <label class="custom-control-label" for="copy_link_7"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Client FeedBack') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="feedbacks" class="form-check-input"
                                @if (isset($result->feedbacks) && $result->feedbacks == 'on') checked="checked" @endif id="copy_link_8"
                                value="on">
                            <label class="custom-control-label" for="copy_link_8"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Invoice') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="invoice" class="form-check-input"
                                @if (isset($result->invoice) && $result->invoice == 'on') checked="checked" @endif id="copy_link_9"
                                value="on">
                            <label class="custom-control-label" for="copy_link_9"></label>
                        </div>
                    </td>
                </tr>
              
                <tr>
                    <td>{{ __('Timesheet') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="timesheet" class="form-check-input"
                                @if (isset($result->timesheet) && $result->timesheet == 'on') checked="checked" @endif id="copy_link_10"
                                value="on">
                            <label class="custom-control-label" for="copy_link_10"></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>{{ __('Payment') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="payment" class="form-check-input" id="copy_link_11"
                                @if (isset($result->payment) && $result->payment == 'on') checked="checked" @endif value="on">
                            <label class="custom-control-label" for="copy_link_11"></label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>{{ __('Expenses') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="expenses" class="form-check-input"
                                @if (isset($result->expenses) && $result->expenses == 'on') checked="checked" @endif id="copy_link_12"
                                value="on">
                            <label class="custom-control-label" for="copy_link_12"></label>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>{{ __('Password Protected') }}</td>
                    <td class="action text-right">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="password_protected"
                                class="form-check-input password_protect" id="password_protected"
                                @if (isset($result->password_protected) && $result->password_protected == 'on') checked="checked" @endif value="on">
                            <label class="custom-control-label" for="password_protected"></label>
                        </div>
                    </td>
                <tr class="passwords">
                    <td>
                        <div class="action input-group input-group-merge  text-left ">
                            <input type="password" value="{{ $password }}"
                                class=" form-control @error('password') is-invalid @enderror" name="password"
                                autocomplete="new-password" id="password"
                                placeholder="{{ __('Enter Your Password') }}">
                            <div class="input-group-append">
                                <span class="input-group-text py-3">
                                    <a href="#" data-toggle="password-text" data-target="#password">
                                        <i class="fas fa-eye-slash" id="togglePassword"></i>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                </tr>
            </tbody>
        </table>
        <div class="text-right pt-3">
            <div class="float-end px-3">
                {{ Form::button(__('Save'), ['type' => 'submit', 'class' => 'btn btn-primary']) }}
            </div>
            @php $projectID= Crypt::encrypt($project->id); @endphp
            {{-- <a href="#" class="btn btn-sm btn-primary btn-icon-only m-1 cp_link"
                data-link="{{ route('project.link', \Illuminate\Support\Facades\Crypt::encrypt($project->id)) }}"
                data-bs-toggle="tooltip" data-bs-title="{{ __('Copy link') }}">
                <span class=""></span><span class="btn-inner--text text-white"><i
                        class="ti ti-link"></i></span></a>
            </a> --}}
            <a href="#" class="btn btn-sm btn-primary cp_link cp_link"
            data-link="{{ route('project.link', \Illuminate\Support\Facades\Crypt::encrypt($project->id)) }}"
            data-toggle="tooltip" title="{{ __('copy project') }}"
            data-original-title="{{ __('Click to copy link') }}">
            <span class="btn-inner--icon"><i class="ti ti-link"></i></span>
            <input type="text" id="c_link" class="d-none"
                value="{{ route('project.link', \Illuminate\Support\Facades\Crypt::encrypt($project->id)) }}">
        </a>
        
        </div>
        {{ Form::close() }}
    </div>
</div>


<script>
    $(document).ready(function() {
        if ($('.password_protect').is(':checked')) {
            $('.passwords').show();
        } else {
            $('.passwords').hide();
        }
        $('#password_protected').on('change', function() {
            if ($('.password_protect').is(':checked')) {
                $('.passwords').show();
            } else {
                $('.passwords').hide();
            }
        });
    });
    $(document).on('change', '#password_protected', function() {
        if ($(this).is(':checked')) {
            $('.passwords').removeClass('password_protect');
            $('.passwords').attr("required", true);
        } else {
            $('.passwords').addClass('password_protect');
            $('.passwords').val(null);
            $('.passwords').removeAttr("required");
        }
    });

</script>
<script>
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    togglePassword.addEventListener("click", function() {
        // toggle the type attribute
        const type = password.getAttribute("type") === "password" ? "text" : "password";
        password.setAttribute("type", type);

        // toggle the icon
        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });

    // prevent form submit
    // const form = document.querySelector("form");
    // form.addEventListener('submit', function (e) {
    //     e.preventDefault();
    // });

    $('.cp_link').on('click', function() {
        
        var copyText = document.getElementById("c_link");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);
        toastrs('Success', '{{ __('Link Copy on Clipboard') }}', 'success');
// show_toastr('Success', '{{ __('Link Copy on Clipboard') }}', 'success')
    });
</script>

{{-- <script>
    $('.cp_link').on('click', function() {
                // console.log("hii");
        var value = $(this).attr('data-link');
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(value).select();
        document.execCommand("copy");
        $temp.remove();
        toastrs('Success', '{{ __('Link Copy on Clipboard2') }}', 'success');
    });
</script> --}}

