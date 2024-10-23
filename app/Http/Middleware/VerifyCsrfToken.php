<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        'plan/paytm/*',
        'plan-pay-with-paytm/*',
        'plan-pay-with-paymentwall/*',
        'paymentwall/*' ,
        'invoice/paytm/*',
        'invoice-pay-with-paytm/*',
        'invoice-pay-with-paymentwall/*',
        'iyzipay/callback/*',
        'paytab-success/*',
        '/aamarpay*',
        '/invoice-aamarpay-status/*',
        'plan-pay-with/Clinet/*',
        'invoice-pay-with/Clinet/*',
        'invoice-pay-with/Clinet/status/*',
    ];

}
