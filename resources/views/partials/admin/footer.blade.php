<script src="{{ asset('public/custom_assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{ asset('public/custom_assets/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('js/letter.avatar.js') }}"></script>
@stack('pre-purpose-script-page')
{{-- FullCalendar --}}
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>

<!-- sweet alert Js -->
<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<!-- <script src="{{ asset('assets/js/pages/ac-alert.js') }}"></script> -->

{{-- DataTable --}}
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>
<script>
    if ($("#pc-dt-simple").length > 0) {
        const dataTable = new simpleDatatables.DataTable("#pc-dt-simple");
    }
</script>

{{-- Multi Select --}}
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>



<!-- date -->
<script src="{{ asset('assets/js/plugins/datepicker-full.min.js') }}"></script>

<!--Botstrap switch-->
<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>

<script src="{{ asset('js/chatify/autosize.js') }}"></script>
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="{{ asset('css/summernote/summernote-bs4.js') }}"></script>
@php
    $settings = \App\Models\Utility::settings();
@endphp
@if ($settings['enable_cookie'] == 'on')
    {{-- @include('layouts.cookie_consent') --}}
@endif

@php
    use App\Models\Utility;
    // $settings = Utility::settingsById(\Auth::user()->creatorId());
    $setting_arr = Utility::file_validate();
@endphp
<script>
    var file_size = "{{ $setting_arr['max_size'] }}";
    var file_types = "{{ $setting_arr['types'] }}";
    // var type_err = "{{ __('Invalid file type. Please select a valid file ('.$setting_arr['types'].').') }}" ;
    var type_err = "{{ __('Invalid file type. Please select a valid file ') }}" + "({{ $setting_arr['types'] }}).";
    var size_err = "{{ __('File size exceeds the maximum limit of ') }}" + " {{ $setting_arr['max_size'] / 1024 }} MB.";
</script>

<script src="{{ asset('js/custom.js') }}"></script>

<!-- <script src="{{ asset('public/custom_assets/libs/select2/dist/js/select2.min.js')}}"></script> -->
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
<!-- report data table-->
<script>
    if ($("#pc-dt-export").length > 0)
    {
        const table = new simpleDatatables.DataTable(".pc-dt-export");

    }

</script>
<script>
    function taskCheckbox() {
        var checked = 0;
        var count = 0;
        var percentage = 0;

        count = $("#check-list input[type=checkbox]").length;
        checked = $("#check-list input[type=checkbox]:checked").length;
        percentage = parseInt(((checked / count) * 100), 10);
        if (isNaN(percentage)) {
            percentage = 0;
        }
        $(".custom-label").text(percentage + "%");
        $('#taskProgress').css('width', percentage + '%');


        $('#taskProgress').removeClass('bg-warning');
        $('#taskProgress').removeClass('bg-primary');
        $('#taskProgress').removeClass('bg-success');
        $('#taskProgress').removeClass('bg-danger');

        if (percentage <= 15) {
            $('#taskProgress').addClass('bg-danger');
        } else if (percentage > 15 && percentage <= 33) {
            $('#taskProgress').addClass('bg-warning');
        } else if (percentage > 33 && percentage <= 70) {
            $('#taskProgress').addClass('bg-primary');
        } else {
            $('#taskProgress').addClass('bg-success');
        }
    }
</script>
<script>
    feather.replace();
    var pctoggle = document.querySelector("#pct-toggler");
    if (pctoggle) {
        pctoggle.addEventListener("click", function() {
            if (
                !document.querySelector(".pct-customizer").classList.contains("active")
            ) {
                document.querySelector(".pct-customizer").classList.add("active");
            } else {
                document.querySelector(".pct-customizer").classList.remove("active");
            }
        });
    }

    if ($('#cust-darklayout').length > 0) {
        var custdarklayout = document.querySelector("#cust-darklayout");
            custdarklayout.addEventListener("click", function() {
                if (custdarklayout.checked) {
                    document
                        .querySelector(".m-header > .b-brand > .logo-lg")
                        .setAttribute("src", "{{ asset('/storage/uploads/logo/logo-light.png') }}");
                    document
                        .querySelector("#main-style-link")
                        .setAttribute("href", "{{ asset('assets/css/style-dark.css') }}");
                } else {
                    document
                        .querySelector(".m-header > .b-brand > .logo-lg")
                        .setAttribute("src", "{{ asset('/storage/uploads/logo/logo-dark.png') }}");
                    document
                        .querySelector("#main-style-link")
                        .setAttribute("href", "{{ asset('assets/css/style.css') }}");
                }
            });
    }

    if ($('#cust-theme-bg').length > 0) {
        var custthemebg = document.querySelector("#cust-theme-bg");
        custthemebg.addEventListener("click", function () {
            if (custthemebg.checked) {
                document.querySelector(".dash-sidebar").classList.add("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.add("transprent-bg");
            } else {
                document.querySelector(".dash-sidebar").classList.remove("transprent-bg");
                document
                    .querySelector(".dash-header:not(.dash-mob-header)")
                    .classList.remove("transprent-bg");
            }
        });
    }

    var themescolors = document.querySelectorAll(".themes-color > a");
            for (var h = 0; h < themescolors.length; h++) {
                var c = themescolors[h];

                c.addEventListener("click", function(event) {
                    var targetElement = event.target;
                    if (targetElement.tagName == "SPAN") {
                        targetElement = targetElement.parentNode;
                    }
                    var temp = targetElement.getAttribute("data-value");
                    removeClassByPrefix(document.querySelector("body"), "theme-");
                    document.querySelector("body").classList.add(temp);
                });
            }

    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
</script>
<script>
    var timer = '';
    var timzone = '{{ env('TIMEZONE') }}';

    function TrackerTimer(start_time) {
        timer = setInterval(function() {
            var start = new Date(start_time);
            //var end = new Date();

            var here = new Date();
            var end = changeTimezone(here, timzone);

            var hrs = end.getHours() - start.getHours();

            var min = end.getMinutes() - start.getMinutes();
            var sec = end.getSeconds() - start.getSeconds();
            var hour_carry = 0;
            var Timer = $(".timer-counter");
            var minutes_carry = 0;
            if (min < 0) {
                min += 60;
                hour_carry += 1;
            }
            hrs = hrs - hour_carry;
            if (sec < 0) {
                sec += 60;
                minutes_carry += 1;
            }
            min = min - minutes_carry;

            Timer.text(minTwoDigits(hrs) + ':' + minTwoDigits(min) + ':' + minTwoDigits(sec));
        }, 1000);
    }
    function minTwoDigits(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function changeTimezone(date, ianatz) {

        var invdate = new Date(date.toLocaleString('en-US', {
            timeZone: ianatz
        }));
        var diff = date.getTime() - invdate.getTime();
        return new Date(date.getTime() - diff);

    }
    function toastrs(title, message, type) {
        var f = document.getElementById('liveToast');
        var a = new bootstrap.Toast(f).show();
        if (type == 'success') {
            $('#liveToast').addClass('bg-primary');
        } else {
            $('#liveToast').addClass('bg-danger');
        }
        $('#liveToast .toast-body').html(message);
    }
</script>


{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script> --}}
<script type="text/javascript">
    $(document).on("click", ".show_confirm , .bs-pass-para", function() {
        var form = $(this).closest("form");
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        })
        swalWithBootstrapButtons.fire({
            title: 'Are you sure?',
            text: "This action can not be undone. Do you want to continue?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        })
    });
</script>

{{--
@php
    if (Auth::check()) {
        if (\Auth::user()->type == 'employee' && \Auth::user()->type != 'super admin') {
            $userTask = App\Models\ProjectTask::where('assign_to', \Auth::user()->id)
                ->where('time_tracking', 1)
                ->first();
        } else if(\Auth::user()->type != 'super admin') {
            $userTask = App\Models\ProjectTask::where('time_tracking', 1)->where('created_by', \Auth::user()->id)->first();
        }
    }

@endphp

@if (!empty($userTask))
    @php
         $lastTime = App\Models\ProjectTaskTimer::where('task_id', $userTask->id)
            ->orderBy('id', 'desc')
            ->first();
    @endphp
    <script>
        TrackerTimer("{{ $lastTime->start_time }}");
        $('.start-task').html("{{ $userTask->title }}");
    </script>
@endif --}}


@stack('script-page')

