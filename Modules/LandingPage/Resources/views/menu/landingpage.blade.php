<li class="dash-item dash-hasmenu  @if (request()->segment(1) == 'landingpage' ||
        request()->segment(1) == 'custom_page' ||
        request()->segment(1) == 'homesection' ||
        request()->segment(1) == 'features' ||
        request()->segment(1) == 'discover' ||
        request()->segment(1) == 'screenshots' ||
        request()->segment(1) == 'pricing_plan' ||
        request()->segment(1) == 'faq' ||
        request()->segment(1) == 'join_us' ||
        request()->segment(1) == 'testimonials') active @endif">
    <a href="{{ route('landingpage.index') }}" class="dash-link">
        <span class="dash-micon"><i class="ti ti-license"></i></span><span
            class="dash-mtext">{{ __('Landing Page') }}</span>
    </a>
</li>
