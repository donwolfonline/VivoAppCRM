@php

    $users = \Auth::user();
    $profile = \App\Models\Utility::get_file('uploads/avatar/');
    $currantLang = $users->currentLanguage();
    $emailTemplate = App\Models\EmailTemplate::first();
    $logo = \App\Models\Utility::get_file('uploads/logo/');

    $company_logo = \App\Models\Utility::GetLogo();
    //  dd($company_logo);
@endphp

@if (isset($settings['cust_theme_bg']) && $settings['cust_theme_bg'] == 'on')
    <nav class="dash-sidebar light-sidebar transprent-bg">
    @else
        <nav class="dash-sidebar light-sidebar">
@endif
<div class="navbar-wrapper">
    <div class="m-header main-logo">
        <a href="#" class="b-brand">

            @if ($settings['cust_darklayout'] == 'on')
                <img src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-light.png') . '?timestamp=' . time() }}"
                    alt="" class="img-fluid" />
            @else
                <img src="{{ $logo . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') . '?timestamp=' . time() }}"
                    alt="" class="img-fluid" />
            @endif

        </a>
    </div>
    <div class="navbar-content">
        <ul class="dash-navbar">
            <li class="dash-item">
                <a href="{{ route('dashboard') }}" class="dash-link"><span class="dash-micon"><i
                            class="ti ti-home"></i></span><span class="dash-mtext">{{ __('Dashboard') }}</span></a>
            </li>

            @if (\Auth::user()->type == 'super admin')
                <li class="dash-item">
                    <a href="{{ route('users.index') }}"
                        class="dash-link {{ Request::segment(1) == 'users' ? 'active' : '' }}">
                        <span class="dash-micon"><i class="ti ti-users"></i></span> <span
                            class="dash-mtext">{{ __('Company') }} </span>
                    </a>
                </li>
            @endif


            @if (\Auth::user()->type == 'company')
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'employee' || Request::segment(1) == 'client' || Request::segment(1) == 'userlogs' || Request::segment(1) == 'clientlogs' ? 'active dash-trigger' : '' }}">
                    <a class="dash-link " data-toggle="collapse" role="button"
                        aria-controls="navbar-getting-started"><span class="dash-micon"><i
                                class="ti ti-users"></i></span><span class="dash-mtext">{{ __('Staff') }}</span><span
                            class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li
                            class="dash-item dash-hasmenu {{ Request::segment(1) == 'employee' || Request::segment(1) == 'userlogs' ? 'active ' : '' }}">
                            <a class="dash-link" href="{{ route('employee.index') }}">{{ __('Employee') }}</span></a>

                        </li>
                        <li
                            class="dash-item dash-hasmenu {{ Request::segment(1) == 'client' || Request::segment(1) == 'clientlogs' ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('client.index') }}">{{ __('Client') }}</a>

                        </li>

                    </ul>
                </li>
            @elseif(\Auth::user()->type == 'employee')
                <li class="dash-item  {{ Request::segment(1) == 'employee' ? 'active ' : '' }}">
                    <a href="{{ route('employee.show', \Crypt::encrypt(\Auth::user()->id)) }}" class="dash-link"><span
                            class="dash-micon"><i class="ti ti-accessible"></i></span><span
                            class="dash-mtext">{{ __('My Profile') }}</span></a>

                </li>
            @elseif(\Auth::user()->type == 'client')
                <li class="dash-item {{ Request::segment(1) == 'client' ? 'active ' : '' }}">
                    <a href="{{ route('client.show', \Crypt::encrypt(\Auth::user()->id)) }}" class="dash-link"><span
                            class="dash-micon"><i class="ti ti-home"></i></span><span
                            class="dash-mtext">{{ __('My Profile') }}</span></a>

                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                <li class="dash-item dash-hasmenu">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-hand-three-fingers"></i></span><span
                            class="dash-mtext">{{ __('HR') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @if (\Auth::user()->type == 'company')
                            <li class="dash-item dash-hasmenu">
                                <a class="dash-link" href="#">{{ __('Attendance') }}<span class="dash-arrow"><i
                                            data-feather="chevron-right"></i></span></a>
                                <ul class="dash-submenu">
                                    <li class="dash-item">
                                        <a class="dash-link"
                                            href="{{ route('attendance.index') }}">{{ __('Attendance') }}</a>
                                    </li>
                                    <li class="dash-item">
                                        <a class="dash-link"
                                            href="{{ route('bulk.attendance') }}">{{ __('Bulk Attendance') }}</a>
                                    </li>
                                </ul>
                            </li>
                        @elseif(\Auth::user()->type == 'employee')
                            <li class="dash-item ">
                                <a class="dash-link" href="{{ route('attendance.index') }}">{{ __('Attendance') }}</a>
                            </li>
                        @endif
                        <li class="dash-item ">
                            <a class="dash-link" href="{{ route('holiday.index') }}">{{ __('Holiday') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('leave.index') }}">{{ __('Leave') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('meeting')) ? route(\Auth::user()->getDefualtViewRouteByModule('meeting')) : route('meeting.index') }}">{{ __('Meeting') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('account-assets.index') }}">{{ __('Asset') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('document-upload.index') }}">{{ __('Document') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('company-policy.index') }}">{{ __('Company Policy') }}</a>
                        </li>
                        <li class="dash-item dash-hasmenu">
                            <a class="dash-link" href="#">{{ __('HR Admin') }}<span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('award.index') }}">{{ __('Award') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('transfer.index') }}">{{ __('Transfer') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('resignation.index') }}">{{ __('Resignation') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('trip.index') }}">{{ __('Trip') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('promotion.index') }}">{{ __('Promotion') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('complaint.index') }}">{{ __('Complaints') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('warning.index') }}">{{ __('Warning') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('termination.index') }}">{{ __('Termination') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dash-item dash-hasmenu">
                            <a class="dash-link" href="#">{{ __('Performance') }}<span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('indicator.index') }}">{{ __('Indicator') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('appraisal.index') }}">{{ __('Appraisal') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('goaltracking.index') }}">{{ __('Goal
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    Tracking') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li
                            class="dash-item dash-hasmenu {{ Request::segment(1) == 'training' || Request::segment(1) == 'trainer' ? 'active dash-trigger' : '' }}">
                            <a class="dash-link" href="#">{{ __('Training') }}<span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'training' ? 'active' : '' }}">
                                    <a class="dash-link"
                                        href="{{ route('training.index') }}">{{ __('Training List') }}</a>
                                </li>
                                <li
                                    class="dash-item dash-hasmenu  {{ Request::segment(1) == 'trainer' ? 'active' : '' }}">
                                    <a class="dash-link" href="{{ route('trainer.index') }}">{{ __('Trainer') }}</a>
                                </li>

                            </ul>
                        </li>

                    </ul>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'lead' || Request::segment(1) == 'deal' || Request::segment(1) == 'estimate' || Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' ? 'active dash-trigger' : '' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-layout-2"></i></span><span
                            class="dash-mtext">{{ __('PreSale') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'lead' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('lead')) ? route(\Auth::user()->getDefualtViewRouteByModule('lead')) : route('lead.index') }}">{{ __('Lead') }}</a>
                            </li>
                        @endif
                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                            <li class="dash-item dash-hasmenu {{ Request::segment(1) == 'deal' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('deal')) ? route(\Auth::user()->getDefualtViewRouteByModule('deal')) : route('deal.index') }}">{{ __('Deal') }}</a>
                            </li>
                        @endif
                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                            <li
                                class="dash-item dash-hasmenu {{ Request::segment(1) == 'estimate' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('estimate.index') }}">{{ __('Estimation') }}</a>
                            </li>
                        @endif
                        @if (\Auth::user()->type == 'company')
                            <li
                                class="dash-item dash-hasmenu {{ Request::segment(1) == 'form_builder' || Request::segment(1) == 'form_response' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('form_builder.index') }}">{{ __('Form Builder') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee')
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'project' || Request::segment(1) == 'allTask' || Request::segment(1) == 'allTimesheet' ? 'active dash-trigger' : '' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-list-check"></i></span><span
                            class="dash-mtext">{{ __('Project') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li
                            class="dash-item dash-hasmenu {{ Request::segment(1) == 'project' && Request::segment(2) != 'allTask' && Request::segment(2) != 'allTaskKanban' && Request::segment(2) != 'allTimesheet' ? 'active  dash-trigger' : '' }}">
                            <a class="dash-link" href="{{ route('project.index') }}">{{ __('All Project') }}</a>
                        </li>
                        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                            <li
                                class="dash-item dash-hasmenu {{ Request::segment(2) == 'allTask' || Request::segment(2) == 'allTaskKanban' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('all task')) ? route(\Auth::user()->getDefualtViewRouteByModule('all task')) : route('project.all.task') }}">{{ __('Task') }}</a>
                            </li>
                            <li
                                class="dash-item dash-hasmenu {{ Request::segment(2) == 'allTimesheet' ? 'active' : '' }}">
                                <a class="dash-link"
                                    href="{{ route('project.all.timesheet') }}">{{ __('Timesheets') }}</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            </li>
            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee')
                <li
                    class="dash-item {{ Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show' ? 'active' : '' }}">
                    <a href="{{ route('project_report.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-chart-line"></i></span><span
                            class="dash-mtext">{{ __('Project Report') }}</span></a>
                </li>
            @endif
            @if (\Auth::user()->type == 'company')
                <li class="dash-item ">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('item')) ? route(\Auth::user()->getDefualtViewRouteByModule('item')) : route('item.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-apps"></i></span><span
                            class="dash-mtext">{{ __('Items') }}</span></a>
                </li>
            @endif
            @if (\Auth::user()->type == 'company')
                <li class="dash-item">
                    <a href="{{ route('itemstock.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-clipboard-check"></i></span><span
                            class="dash-mtext">{{ __('Item Stock') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'invoice' || Request::segment(1) == 'payment' || Request::segment(1) == 'creditNote' || Request::segment(1) == 'expense' ? 'active dash-trigger' : '' }}">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-stairs-up"></i></span><span
                            class="dash-mtext">{{ __('Sale') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item dash-hasmenu  {{ Request::segment(1) == 'invoice' ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('invoice')) ? route(\Auth::user()->getDefualtViewRouteByModule('invoice')) : route('invoice.index') }}">{{ __('Invoices') }}</a>
                        </li>
                        <li class="dash-item dash-hasmenu  {{ Request::segment(1) == 'payment' ? 'active ' : '' }}">
                            <a class="dash-link" href="{{ route('payment.index') }}">{{ __('Payment') }}</a>
                        </li>
                        <li
                            class="dash-item dash-hasmenu  {{ Request::segment(1) == 'creditNote' ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('creditNote.index') }}">{{ __('Credit Notes') }}</a>
                        </li>
                        @if (\Auth::user()->type == 'company')
                            <li
                                class="dash-item dash-hasmenu  {{ Request::segment(1) == 'expense' ? 'active' : '' }}">
                                <a class="dash-link" href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li class="dash-item {{ request()->is('budget*') ? 'active' : '' }}">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('budget')) ? route(\Auth::user()->getDefualtViewRouteByModule('budget')) : route('budget.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-businessplan"></i></span><span
                            class="dash-mtext">{{ __('Budget Planner') }}</span></a>
                </li>

                <li class="dash-item {{ request()->is('timetracker*') ? 'active' : '' }}">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('timetracker')) ? route(\Auth::user()->getDefualtViewRouteByModule('timetracker')) : route('timetracker.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-alarm"></i></span><span
                            class="dash-mtext">{{ __('Tracker') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type != 'super admin')
                <li class="dash-item {{ request()->is('zoommeeting*') ? 'active' : '' }}">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('zoommeeting')) ? route(\Auth::user()->getDefualtViewRouteByModule('zoommeeting')) : route('zoommeeting.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-video-plus"></i></span><span
                            class="dash-mtext">{{ __('Zoom Metting') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
                <li class="dash-item {{ request()->is('contract*') ? 'active' : '' }}">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('contract')) ? route(\Auth::user()->getDefualtViewRouteByModule('contract')) : route('contract.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-device-floppy"></i></span><span
                            class="dash-mtext">{{ __('Contract') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li
                    class="dash-item dash-hasmenu {{ Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry' || Request::segment(2) == 'ledger' || Request::segment(2) == 'balance-sheet' || Request::segment(2) == 'trial-balance' ? 'active dash-trigger' : '' }}">
                    <a href="#!"
                        class="dash-link {{ Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry' || Request::segment(2) == 'ledger' || Request::segment(2) == 'balance-sheet' || Request::segment(2) == 'trial-balance' ? 'show' : '' }}"><span
                            class="dash-micon"><i class="ti ti-forms"></i></span><span
                            class="dash-mtext">{{ __('Double Entry') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item {{ request()->is('chart-of-account*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('chart-of-account.index') }}">{{ __('Chart of Accounts') }}</a>
                        </li>
                        <li class="dash-item {{ request()->is('journal-entry*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('journal-entry.index') }}">{{ __('Journal Account') }}</a>
                        </li>
                        <li class="dash-item {{ request()->is('report.ledger*') ? 'active' : '' }}">
                            <a class="dash-link"
                                href="{{ route('report.ledger') }}">{{ __('Ledger Summary') }}</a>
                        </li>
                        <li class="dash-item {{ request()->is('report.balance.sheet*') ? 'active' : '' }} ">
                            <a class="dash-link"
                                href="{{ route('report.balance.sheet') }}">{{ __('Balance Sheet') }}</a>
                        </li>
                        <li class="dash-item {{ request()->is('trial.balance*') ? 'active' : '' }}">
                            <a class="dash-link" href="{{ route('trial.balance') }}">{{ __('Trial Balance') }}</a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee' || \Auth::user()->type == 'client')
                <li class="dash-item">
                    <a href="{{ url('chats') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-brand-hipchat"></i></span><span
                            class="dash-mtext">{{ __('Messenger') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee')
                <li class="dash-item {{ request()->is('support*') ? 'active' : '' }}">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('support')) ? route(\Auth::user()->getDefualtViewRouteByModule('support')) : route('support.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-headset"></i></span><span
                            class="dash-mtext">{{ __('Support') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'employee')
                <li class="dash-item">
                    <a href="{{ route('event.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-calendar-event"></i></span><span
                            class="dash-mtext">{{ __('Event') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee')
                <li class="dash-item">
                    <a href="{{ !empty(\Auth::user()->getDefualtViewRouteByModule('notice board')) ? route(\Auth::user()->getDefualtViewRouteByModule('notice board')) : route('noticeBoard.index') }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-clipboard-list"></i></span><span
                            class="dash-mtext">{{ __('Notice Board') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li class="dash-item">
                    <a href="{{ url('goal') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-award"></i></span><span
                            class="dash-mtext">{{ __('Goal') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'employee')
                <li class="dash-item {{ request()->is('note*') ? 'active' : '' }}">
                    <a href="{{ route('note.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-note"></i></span><span
                            class="dash-mtext">{{ __('Note') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li class="dash-item dash-hasmenu">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-chart-dots"></i></span><span
                            class="dash-mtext">{{ __('Report') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('report.attendance') }}">{{ __('Attendance') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.task') }}">{{ __('Task') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('report.income.expense') }}">{{ __('Income Vs Expense') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.timelog') }}">{{ __('Time Log') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.finance') }}">{{ __('Finance') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.leave') }}">{{ __('Leave') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.estimate') }}">{{ __('Estimate') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.invoice') }}">{{ __('Invoice') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.client') }}">{{ __('Client') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.lead') }}">{{ __('Lead') }}</a>
                        </li>

                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('report.deal') }}">{{ __('Deal') }}</a>
                        </li>

                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('report.product.stock.report') }}">{{ __('Item Stock') }}</a>
                        </li>

                    </ul>
                </li>
            @endif

            @if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                <li class="dash-item @if (str_contains(request()->url(), 'stripe')) active @endif">
                    <a href="{{ route('plan.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-trophy"></i></span><span
                            class="dash-mtext">{{ __('Plan') }}</span></a>
                </li>
            @endif


            @if (\Auth::user()->type == 'super admin')
                <li class="dash-item">
                    <a href="{{ route('plan_request.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-git-pull-request"></i></span><span
                            class="dash-mtext">{{ __('Plan Request') }}</span></a>
                </li>
            @endif
            @if (\Auth::user()->type == 'super admin')
                <li class="dash-item {{ Request::segment(1) == 'referral-program' ? 'active' : '' }}">
                    <a href="{{ route('referral.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-trophy"></i></span><span
                            class="dash-mtext">{{ __('Referral Program') }}</span></a>
                </li>
            @endif
            @if (\Auth::user()->type == 'company')
                <li class="dash-item {{ Request::segment(1) == 'referral-program' ? 'active' : '' }}">
                    <a href="{{ route('guideline.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-trophy"></i></span><span
                            class="dash-mtext">{{ __('Referral Program') }}</span></a>
                </li>
            @endif
            @if (\Auth::user()->type == 'super admin')
                <li class="dash-item {{ Request::segment(1) == 'coupon' ? 'active' : '' }}">
                    <a href="{{ route('coupon.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-clipboard-check"></i></span><span
                            class="dash-mtext">{{ __('Coupon') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                <li class="dash-item">
                    <a href="{{ route('order.index') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-shopping-cart"></i></span><span
                            class="dash-mtext">{{ __('Order') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'super admin')
                <li
                    class="dash-item {{ request()->is('email_template*') || request()->is('emailtemplate_lang*') ? 'active' : '' }}">
                    <a href="{{ route('manage.email.language', [$emailTemplate->id, \Auth::user()->lang]) }}"
                        class="dash-link"><span class="dash-micon"><i class="ti ti-template"></i></span><span
                            class="dash-mtext">{{ __('Email Template') }}</span></a>
                </li>
            @endif


            @if (\Auth::user()->type == 'company')
                <li class="dash-item dash-hasmenu">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-circle-square"></i></span><span
                            class="dash-mtext">{{ __('Constant') }}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu">
                        <li class="dash-item dash-hasmenu">
                            <a class="dash-link" href="#">{{ __('HR') }}<span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('department.index') }}">{{ __('Department') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('designation.index') }}">{{ __('Designation') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('salaryType.index') }}">{{ __('Salary Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('leaveType.index') }}">{{ __('Leave Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('award-type.index') }}">{{ __('Award Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('termination-type.index') }}">{{ __('Termination Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('training-type.index') }}">{{ __('Training Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('performanceType.index') }}">{{ __('Performance Type') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('competencies.index') }}">{{ __('Competencies') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dash-item dash-hasmenu">
                            <a class="dash-link" href="#">{{ __('PreSale') }}<span class="dash-arrow"><i
                                        data-feather="chevron-right"></i></span></a>
                            <ul class="dash-submenu">
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('pipeline.index') }}">{{ __('Pipeline') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('leadStage.index') }}">{{ __('Lead Stage') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('dealStage.index') }}">{{ __('Deal Stage') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link"
                                        href="{{ route('source.index') }}">{{ __('Source') }}</a>
                                </li>
                                <li class="dash-item">
                                    <a class="dash-link" href="{{ route('label.index') }}">{{ __('Label') }}</a>
                                </li>
                            </ul>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('projectStage.index') }}">{{ __('Project Task Stage') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('taxRate.index') }}">{{ __('Tax Rate') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('unit.index') }}">{{ __('Unit') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('category.index') }}">{{ __('Category') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('paymentMethod.index') }}">{{ __('Payment Method') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link"
                                href="{{ route('contractType.index') }}">{{ __('Contract Type') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('branch.index') }}">{{ __('Branch') }}</a>
                        </li>
                        <li class="dash-item">
                            <a class="dash-link" href="{{ route('goaltype.index') }}">{{ __('Goal Type') }}</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (\Auth::user()->type == 'company')
                <li class="dash-item @if (str_contains(request()->url(), 'notification-templates')) active @endif">
                    <a href="{{ url('notification-templates') }}" class="dash-link">
                        <span class="dash-micon">
                            <i class="ti ti-bell"></i>
                        </span>
                        <span class="dash-mtext">{{ __('Notification Templates') }}</span></a>
                </li>
            @endif

            @if (\Auth::user()->type == 'super admin')
                @include('landingpage::menu.landingpage')
            @endif

            @if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'company')
                <li class="dash-item">
                    <a href="{{ route('settings') }}" class="dash-link"><span class="dash-micon"><i
                                class="ti ti-settings"></i></span><span
                            class="dash-mtext">{{ __('Settings') }}</span></a>
                </li>
            @endif

        </ul>
    </div>
</div>
</nav>
<!-- [ navigation menu ] end -->
