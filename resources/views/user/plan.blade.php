@foreach ($plans as $plan)
    <div class="list-group-item">
        <div class="row align-items-center">
            <!-- <div class="col-auto">
                <a href="#" class="avatar rounded-circle">
                    <img alt="Image placeholder" src="{{ asset(Storage::url('uploads/plan')) . '/' . $plan->image }}" class="wid-50">
                </a>
            </div> -->
            <div class="col ml-n2">
                <a href="#!" class="d-block h6 mb-0">{{ $plan->name }}</a>
                <div>
                    <span class="text-sm">{{ env('CURRENCY_SYMBOL') . $plan->price }}
                        {{ ' / ' . $plan->duration }}</span>
                </div>
            </div>
            <div class="col ml-n2">
                <a href="#!" class="d-block h6 mb-0">{{ __('Employees') }}</a>
                <div>
                    <span class="text-sm">{{ $plan->max_employee }}</span>
                </div>
            </div>
            <div class="col ml-n2">
                <a href="#!" class="d-block h6 mb-0">{{ __('Clients') }}</a>
                <div>
                    <span class="text-sm">{{ $plan->max_client }}</span>
                </div>
            </div>
            <div class="col-auto">
                @if ($user->plan == $plan->id)
                    <span class="btn btn-success btn-sm square-pill my-auto w-100">{{ __('Active') }}</span>
                @else
                    <a href="{{ route('plan.active', [$user->id, $plan->id]) }}"
                        class="btn btn-primary btn-sm square-pill my-auto w-100" data-toggle="tooltip"
                        data-original-title="{{ __('Click to Upgrade Plan') }}">
                        <span class="btn-inner--icon">
                            <i class="fas fa-cart-plus"></i>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endforeach
