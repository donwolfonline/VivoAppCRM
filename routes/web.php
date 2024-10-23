<?php

use App\Http\Controllers\AamarpayController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\SalaryTypeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadStageController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStageController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\ProjectStageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DocumentUploadController;
use App\Http\Controllers\CompanyPolicyController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\AiTemplateController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\AwardTypeController;
use App\Http\Controllers\ResignationController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\TerminationTypeController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\TrainingTypeController;
use App\Http\Controllers\PerformanceTypeController;
use App\Http\Controllers\CompetenciesController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EmailTemplateLangController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\LandingPageSectionController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\FlutterwavePaymentController;
use App\Http\Controllers\RazorpayPaymentController;
use App\Http\Controllers\PaytmPaymentController;
use App\Http\Controllers\MercadoPaymentController;
use App\Http\Controllers\MolliePaymentController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\CoingatePaymentController;
use App\Http\Controllers\PaymentWallPaymentController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\GoalTrackingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\GoalTypeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ZoommeetingController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\TimeTrackerController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BenefitPaymentController;
use App\Http\Controllers\CashfreeController;
use App\Http\Controllers\CinetPayController;
use App\Http\Controllers\FedapayController as ControllersFedapayController;
use App\Http\Controllers\ToyyibpayController;
use App\Http\Controllers\PayfastController;
use App\Http\Controllers\NotificationTemplatesController;
use App\Http\Controllers\IyziPayController;
use App\Http\Controllers\SspayController;
use App\Http\Controllers\PaytabController;
use App\Http\Controllers\PaytrController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\NepalstePaymnetController;
use App\Http\Controllers\referralController;
use App\Http\Controllers\XenditPaymentController;
use App\Http\Controllers\YooKassaController;
use App\Http\Controllers\PaiementProController;
use App\Http\Controllers\PayHereController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//Auth::routes();

// Route::get('/register/{lang?}', 'Auth\RegisterController@showRegistrationForm')->name('register');
// Route::post('register', 'Auth\RegisterController@register')->name('register');
// Route::get('/login/{lang?}', 'Auth\LoginController@showLoginForm')->name('login');
// Route::get('/password/resets/{lang?}', 'Auth\Au@showLinkRequestForm')->name('change.langPass');



Route::get('/', function () {
    return view('welcome');
})->name('frist_page');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

Route::get('/form/{code}', [FormBuilderController::class, 'formView'])->name('form.view');
Route::get('estimate/pdf/{id}', [EstimateController::class, 'pdf'])->name('estimate.pdf');

Route::get('/project{id}/edit', [ProjectController::class, 'copylink_setting_create'])->name('project.copylink.setting.create');
Route::any('/project/copy/link/{id}', [ProjectController::class, 'copylinksetting'])->name('project.copy.link');
Route::any('/project/link/{id}/{ps?}', [ProjectController::class, 'projectlink'])->name('project.link')->middleware(['XSS']);
Route::any('/project/passcheck/{id?}', [ProjectController::class, 'projectPassCheck'])->name('project.passcheck');
Route::get('/project/change_lang_copylink/{lang}', [ProjectController::class, 'changeLangcopylink'])->name('change_lang_copylink')->middleware(['XSS']);
Route::get('project/{id}/client/{cid}/permission', [ProjectController::class, 'clientPermission'])->name('client.permission');
Route::post('project/{id}/client/{cid}/permission/store', [ProjectController::class, 'storeClientPermission'])->name('client.store.permission');
Route::get('project/taskboard/{id}/show', [ProjectController::class, 'taskShows'])->name('task.shows');


Route::get('register/{lang?}', [RegisteredUserController::class, 'showemailform'])->name('register');
Route::get('/verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');

Route::get('/verify-email/{lang?}', [EmailVerificationPromptController::class, 'showVerifcation'])->name('verification.notice')->middleware(['auth','XSS']);

Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->name('verification.verify')->middleware('auth');
Route::get('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');
Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware(['XSS']);

//---------invoice payment start---
Route::get('invoice/pdf/{id}', [InvoiceController::class, 'pdf'])->name('invoice.pdf')->middleware(['XSS']);

Route::post('/invoice-pay-with-banktransfer', [BankTransferController::class, 'invoicePayWithBankTransfer'])->name('invoice.pay.with.banktransfer');
// Route::get('/invoice-bankpayment/{id}/action', [BankTransferController::class, 'InvoiceBankTransferStatus'])->name('banktransfer.show');
Route::get('/invoice-bankpayment/{id}/action', [BankTransferController::class, 'InvoiceBankTransferAction'])->name('banktransfer.show');

Route::post('/invoice-pay-with-stripe', [StripePaymentController::class, 'invoicePayWithStripe'])->name('invoice.pay.with.stripe');
Route::any('/invoice-pay-with-stripe/{invoice_id}/{pay_id}', [StripePaymentController::class, 'getInvociePaymentStatus'])->name('invoice.stripe');

Route::post('invoice/{id}/payment', [StripePaymentController::class, 'addpayment'])->name('client.invoice.payment');
Route::get('/invoice/pay/{invoice}', [InvoiceController::class, 'payinvoice'])->name('pay.invoice');

Route::post('{id}/pay-with-paypal', [PaypalController::class, 'clientPayWithPaypal'])->name('client.pay.with.paypal');
Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'clientGetPaymentStatus'])->name('client.get.payment.status');

Route::post('/invoice-pay-with-paystack', [PaystackPaymentController::class, 'invoicePayWithPaystack'])->name('invoice.pay.with.paystack');
Route::get('/invoice/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.paystack');

Route::post('/invoice-pay-with-flaterwave', [FlutterwavePaymentController::class, 'invoicePayWithFlutterwave'])->name('invoice.pay.with.flaterwave');
Route::get('/invoice/flaterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.flaterwave');

Route::post('/invoice-pay-with-razorpay', [RazorpayPaymentController::class, 'invoicePayWithRazorpay'])->name('invoice.pay.with.razorpay');
Route::get('/invoice/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.razorpay');

Route::post('/invoice-pay-with-paytm', [PaytmPaymentController::class, 'invoicePayWithPaytm'])->name('invoice.pay.with.paytm');
Route::post('/invoice/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.paytm');

Route::any('/invoice-pay-with-mercado', [MercadoPaymentController::class, 'invoicePayWithMercado'])->name('invoice.pay.with.mercado');
Route::any('/invoice/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.mercado');

Route::post('/invoice-pay-with-mollie', [MolliePaymentController::class, 'invoicePayWithMollie'])->name('invoice.pay.with.mollie');
Route::get('/invoice/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.mollie');

Route::post('/invoice-pay-with-skrill', [SkrillPaymentController::class, 'invoicePayWithSkrill'])->name('invoice.pay.with.skrill');
Route::get('/invoice/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.skrill');

Route::any('/invoice-pay-with-coingate', [CoingatePaymentController::class, 'invoicePayWithCoingate'])->name('invoice.pay.with.coingate');
Route::get('/invoice/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.coingate');

Route::any('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name('invoice.paymentwallpayment');
Route::post('/invoice-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])->name('invoice.pay.with.paymentwall');

Route::post('/invoice-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name('invoice.toyyibpaypayment')->middleware(['XSS']);
Route::get('/invoice/toyyibpay/{invoice}/{amt}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name('invoice.toyyibpay.status')->middleware(['XSS']);

Route::post('/invoice-pay-with-payfast', [PayfastController::class, 'invoicepaywithpayfast'])->name('invoice-pay-with-payfast');
Route::get('/invoice/payfast/{invoice}', [PayfastController::class, 'invoicepayfaststatus'])->name('invoice.payfast');
Route::get('/estimate/pay/{estimate}', [EstimateController::class, 'payestimate'])->name('pay.estimate');


Route::post('/invoice-pay-with-sspay', [SspayController::class, 'invoicepaywithsspay'])->name('invoice.sspaypayment')->middleware(['XSS']);
Route::get('/invoice/sspay/{invoice}/{amt}', [SspayController::class, 'getInvoicePaymentStatus'])->name('invoice.sspay.status')->middleware(['XSS']);


//---------invoice payment End ---
Route::group(['middleware' => ['verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    Route::get('/lang/change/{lang}', [DashboardController::class, 'changeLang'])->name('lang.change')->middleware(['auth', 'XSS']);
    // Route::get('/lang/change/{lang}',['as' => 'lang.change','uses' =>'DashboardController@changeLang'])->middleware(['auth','XSS']);

    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::post('edit-employee-company-info/{id}', [EmployeeController::class, 'employeeCompanyInfoEdit'])->name('employee.company.update');
            Route::post('edit-employee-personal-info/{id}', [EmployeeController::class, 'employeePersonalInfoEdit'])->name('employee.personal.update');
            Route::post('edit-employee-bank-info/{id}', [EmployeeController::class, 'employeeBankInfoEdit'])->name('employee.bank.update');

            Route::resource('employee', EmployeeController::class);
            Route::any('employee-reset-password/{id}', [EmployeeController::class, 'employeePassword'])->name('employee.reset');
            Route::post('employee-reset-password/{id}', [EmployeeController::class, 'employeePasswordReset'])->name('employee.password.update');
            Route::get('employee-login/{id}', [EmployeeController::class, 'LoginManage'])->name('employee.login');
        }
    );

    Route::post('employee/getdepartment', [EmployeeController::class, 'getDepartment'])->name('employee.getdepartment')->middleware(['auth', 'XSS']);
    Route::post('employee/json', [EmployeeController::class, 'json'])->name('employee.json')->middleware(['auth', 'XSS', 'revalidate',]);

    Route::group(['middleware' => ['auth', 'XSS', 'revalidate',],], function () {
        Route::resource('client', ClientController::class);
    });
    Route::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name('client.reset');
    Route::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name('client.password.update');
    Route::get('client-login/{id}', [ClientController::class, 'LoginManage'])->name('client.login');


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('department', DepartmentController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('designation', DesignationController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('salaryType', SalaryTypeController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('bulk-attendance', [AttendanceController::class, 'bulkAttendance'])->name('bulk.attendance');
            Route::post('bulk-attendance', [AttendanceController::class, 'bulkAttendanceData'])->name('bulk.attendance');
            Route::post('employee/attendance', [AttendanceController::class, 'attendance'])->name('employee.attendance');
            Route::resource('attendance', AttendanceController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {

            Route::resource('holiday', HolidayController::class);
        }
    );

    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::get('leave/{id}/action', [LeaveController::class, 'action'])->name('leave.action');
            Route::post('leave/changeAction', [LeaveController::class, 'changeAction'])->name('leave.changeaction');
            Route::post('leave/jsonCount', [LeaveController::class, 'jsonCount'])->name('leave.jsoncount');
            Route::get('leave/calendar', [LeaveController::class, 'calendar'])->name('leave.calendar');
            Route::resource('leave', LeaveController::class);

        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('leaveType', LeaveTypeController::class);
        }
    );

    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::get('meeting/calendar', [MeetingController::class, 'calendar'])->name('meeting.calendar');
            Route::resource('meeting', MeetingController::class);
        }
    );

    Route::group(
        [
            'middleware' => ['auth', 'XSS', 'revalidate',],
        ],
        function () {
            Route::get('lead/grid', [LeadController::class, 'grid'])->name('lead.grid');
            Route::post('lead/json', [LeadController::class, 'json'])->name('lead.json');
            Route::post('lead/order', [LeadController::class, 'order'])->name('lead.order');
            Route::get('lead/{id}/users', [LeadController::class, 'userEdit'])->name('lead.users.edit');
            Route::post('lead/{id}/users', [LeadController::class, 'userUpdate'])->name('lead.users.update');
            Route::delete('lead/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('lead.users.destroy');

            Route::get('lead/{id}/items', [LeadController::class, 'productEdit'])->name('lead.items.edit');
            Route::post('lead/{id}/items', [LeadController::class, 'productUpdate'])->name('lead.items.update');
            Route::delete('lead/{id}/items/{uid}', [LeadController::class, 'productDestroy'])->name('lead.items.destroy');

            Route::post('lead/{id}/file', [LeadController::class, 'fileUpload'])->name('lead.file.upload');
            Route::get('lead/{id}/file/{fid}', [LeadController::class, 'fileUpload'])->name('lead.file.download');
            Route::delete('lead/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('lead.file.delete');

            Route::get('lead/{id}/sources', [LeadController::class, 'sourceEdit'])->name('lead.sources.edit');
            Route::post('lead/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('lead.sources.update');
            Route::delete('lead/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('lead.sources.destroy');

            Route::get('lead/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('lead.discussions.create');
            Route::post('lead/{id}/discussions', [LeadController::class, 'discussionStore'])->name('lead.discussion.store');

            Route::get('lead/{id}/call', [LeadController::class, 'callCreate'])->name('lead.call.create');
            Route::post('lead/{id}/call', [LeadController::class, 'callStore'])->name('lead.call.store');
            Route::get('lead/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('lead.call.edit');
            Route::post('lead/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('lead.call.update');
            Route::delete('lead/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('lead.call.destroy');

            Route::get('lead/{id}/email', [LeadController::class, 'emailCreate'])->name('lead.email.create');
            Route::post('lead/{id}/email', [LeadController::class, 'emailStore'])->name('lead.email.store');

            Route::get('lead/{id}/label', [LeadController::class, 'labels'])->name('lead.label');
            Route::post('lead/{id}/label', [LeadController::class, 'labelStore'])->name('lead.label.store');

            Route::get('lead/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('lead.convert.deal');
            Route::post('lead/{id}/convert', [LeadController::class, 'convertToDeal'])->name('lead.convert.to.deal');

            Route::get('lead/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('lead.convert.deal');
            Route::post('lead/{id}/convert', [LeadController::class, 'convertToDeal'])->name('lead.convert.to.deal');

            Route::post('lead/change-pipeline', [LeadController::class, 'changePipeline'])->name('lead.change.pipeline');
            Route::resource('lead', LeadController::class);
        }
    );
    Route::post('lead/{id}/note', [LeadController::class, 'noteStore'])->name('lead.note.store')->middleware(['auth']);

    Route::get('import/lead/file', [LeadController::class, 'importFile'])->name('lead.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/lead', [LeadController::class, 'import'])->name('lead.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::get('import/deal/file', [DealController::class, 'importFile'])->name('deal.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/deal', [DealController::class, 'import'])->name('deal.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    //Export
    Route::get('export/deal', [DealController::class, 'export'])->name('deal.export')->middleware(['auth', 'XSS']);
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('pipeline', PipelineController::class);
        }
    );


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('leadStage/order', [LeadStageController::class, 'order'])->name('leadStage.order');
            Route::resource('leadStage', LeadStageController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('source', SourceController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('label', LabelController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('taxRate', TaxRateController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('unit', UnitController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('category', CategoryController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('deal/order', [DealController::class, 'order'])->name('deal.order');
            Route::get('deal/{id}/users', [DealController::class, 'userEdit'])->name('deal.users.edit');
            Route::post('deal/{id}/users', [DealController::class, 'userUpdate'])->name('deal.users.update');
            Route::delete('deal/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name('deal.users.destroy');

            Route::post('deal/{id}/update', [DealController::class, 'Update'])->name('deal.update');


            Route::get('deal/{id}/items', [DealController::class, 'productEdit'])->name('deal.items.edit');
            Route::post('deal/{id}/items', [DealController::class, 'productUpdate'])->name('deal.items.update');
            Route::delete('deal/{id}/items/{uid}', [DealController::class, 'productDestroy'])->name('deal.items.destroy');

            Route::post('deal/{id}/file', [DealController::class, 'fileUpload'])->name('deal.file.upload');
            Route::get('deal/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name('deal.file.download');
            Route::delete('deal/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name('deal.file.delete');



            Route::get('deal/{id}/task', [DealController::class, 'taskCreate'])->name('deal.tasks.create');
            Route::post('deal/{id}/task', [DealController::class, 'taskStore'])->name('deal.tasks.store');
            Route::get('deal/{id}/task/{tid}/show', [DealController::class, 'taskShow'])->name('deal.tasks.show');
            Route::get('deal/{id}/task/{tid}/edit', [DealController::class, 'taskEdit'])->name('deal.tasks.edit');
            Route::post('deal/{id}/task/{tid}', [DealController::class, 'taskUpdate'])->name('deal.tasks.update');
            Route::post('deal/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name('deal.tasks.update_status');
            Route::delete('deal/{id}/task/{tid}', [DealController::class, 'taskDestroy'])->name('deal.tasks.destroy');

            Route::get('deal/{id}/products', [DealController::class, 'productEdit'])->name('deal.products.edit');
            Route::post('deal/{id}/products', [DealController::class, 'productUpdate'])->name('deal.products.update');
            Route::delete('deal/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name('deal.products.destroy');

            Route::get('deal/{id}/sources', [DealController::class, 'sourceEdit'])->name('deal.sources.edit');
            Route::post('deal/{id}/sources', [DealController::class, 'sourceUpdate'])->name('deal.sources.update');
            Route::delete('deal/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name('deal.sources.destroy');



            Route::get('deal/{id}/discussions', [DealController::class, 'discussionCreate'])->name('deal.discussions.create');
            Route::post('deal/{id}/discussions', [DealController::class, 'discussionStore'])->name('deal.discussion.store');


            Route::get('deal/{id}/call', [DealController::class, 'callCreate'])->name('deal.call.create');
            Route::post('deal/{id}/call', [DealController::class, 'callStore'])->name('deal.call.store');
            Route::get('deal/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name('deal.call.edit');
            Route::post('deal/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name('deal.call.update');
            Route::delete('deal/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name('deal.call.destroy');

            Route::get('deal/{id}/email', [DealController::class, 'emailCreate'])->name('deal.email.create');
            Route::post('deal/{id}/email', [DealController::class, 'emailStore'])->name('deal.email.store');

            Route::get('deal/{id}/clients', [DealController::class, 'clientEdit'])->name('deal.clients.edit');
            Route::post('deal/{id}/clients', [DealController::class, 'clientUpdate'])->name('deal.clients.update');
            Route::delete('deal/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name('deal.clients.destroy');

            Route::get('deal/{id}/labels', [DealController::class, 'labels'])->name('deal.labels');
            Route::post('deal/{id}/labels', [DealController::class, 'labelStore'])->name('deal.labels.store');


            Route::get('deal/list', [DealController::class, 'deal_list'])->name('deal.list');
            Route::post('deal/change-pipeline', [DealController::class, 'changePipeline'])->name('deal.change.pipeline');


            Route::post('deal/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name('deal.change.status')->middleware(
                [
                    'auth',
                    'XSS',
                    'revalidate',
                ]
            );

            Route::resource('deal', DealController::class);
        }
    );

    Route::post('deal/{id}/note', [DealController::class, 'noteStore'])->name('deal.note.store')->middleware(['auth']);
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('dealStage/order', [DealStageController::class, 'order'])->name('dealStage.order');
            Route::post('dealStage/json', [DealStageController::class, 'json'])->name('dealStage.json');
            Route::resource('dealStage', DealStageController::class);

            Route::resource('dealStage', 'DealStageController');
        }
    );
    Route::get('estimate/preview/{template}/{color}/{code}', [EstimateController::class, 'previewEstimate'])->name('estimate.preview');
    Route::post('estimate/template/setting', [EstimateController::class, 'saveEstimateTemplateSettings'])->name('estimate.template.setting');


    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::post('estimate/product/destroy', [EstimateController::class, 'productDestroy'])->name('estimate.product.destroy');
            Route::post('estimate/product', [EstimateController::class, 'product'])->name('estimate.product');
            Route::get('estimate/{id}/send', [EstimateController::class, 'send'])->name('estimate.send');
            Route::get('estimate/status', [EstimateController::class, 'statusChange'])->name('estimate.status.change');
            Route::get('estimate/items', [EstimateController::class, 'items'])->name('estimate.items');

            Route::get('estimate/{id}/convert', [EstimateController::class, 'convert'])->name('estimate.convert');

            Route::resource('estimate', EstimateController::class);
        }
    );


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('business-setting', [SettingController::class, 'saveBusinessSettings'])->name('business.setting');
            Route::post('company-setting', [SettingController::class, 'saveCompanySettings'])->name('company.setting');
            Route::post('email-setting', [SettingController::class, 'saveEmailSettings'])->name('email.setting');
            Route::post('system-setting', [SettingController::class, 'saveSystemSettings'])->name('system.setting');
            Route::post('pusher-setting', [SettingController::class, 'savePusherSettings'])->name('pusher.setting');
            Route::any('payment-setting', [SettingController::class, 'savePaymentSettings'])->name('payment.setting');
            Route::post('company-payment-setting', [SettingController::class, 'saveCompanyPaymentSettings'])->name('company.payment.setting');
            Route::post('setting/google-calender', [SettingController::class, 'saveGoogleCalenderSettings'])->name('google.calender.settings');



            Route::get('test-mail', [SettingController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail', [SettingController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail/send', [SettingController::class, 'testSendMail'])->name('test.send.mail');
            Route::post('storage-settings', [SettingController::class, 'storageSettingStore'])->name('storage.setting.store')->middleware(['auth', 'XSS']);
            Route::get('settings', [SettingController::class, 'index'])->name('settings');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('project/{project}/user', [ProjectController::class, 'projectUser'])->name('project.user');
            Route::post('project/{project}/user', [ProjectController::class, 'addProjectUser'])->name('project.user.add');
            Route::delete('project/{project}/user/{user}/destroy', [ProjectController::class, 'destroyProjectUser'])->name('project.user.destroy');
            Route::post('project/{project}/status', [ProjectController::class, 'changeStatus'])->name('project.status');
            Route::get('project/copy/{id}', [ProjectController::class, 'copyproject'])->name('project.copy');
            Route::post('project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name('project.copy.store');
            Route::get('project/grid', [ProjectController::class, 'grid'])->name('project.grid');


            // Route::get('project/{id}/task', 'ProjectController@taskBoard')->name('project.task');
            Route::get('task/calendar', [ProjectController::class, 'calendar'])->name('task.calendar');

            Route::get('project/{id}/task/create', [ProjectController::class, 'taskCreate'])->name('project.task.create');
            Route::post('project/{id}/task/store', [ProjectController::class, 'taskStore'])->name('project.task.store');
            Route::get('project/task/{id}/edit', [ProjectController::class, 'taskEdit'])->name('project.task.edit');
            Route::post('project/task/{id}/update', [ProjectController::class, 'taskUpdate'])->name('project.task.update');
            Route::delete('project/task/{id}/delete', [ProjectController::class, 'taskDestroy'])->name('project.task.destroy');
            Route::get('project/task/{id}/show', [ProjectController::class, 'taskShow'])->name('project.task.show');
            Route::post('project/order', [ProjectController::class, 'order'])->name('project.task.order');

            Route::post('project/task/{id}/checklist/store', [ProjectController::class, 'checkListStore'])->name('project.task.checklist.store');
            Route::post('project/task/{id}/checklist/{cid}/update', [ProjectController::class, 'checklistUpdate'])->name('project.task.checklist.update');
            Route::delete('project/task/{id}/checklist/{cid}', [ProjectController::class, 'checklistDestroy'])->name('project.task.checklist.destroy');

            Route::post('project/{id}/task/{tid}/comment', [ProjectController::class, 'commentStore'])->name('project.task.comment.store');
            Route::post('project/task/{id}/file', [ProjectController::class, 'commentStoreFile'])->name('project.task.comment.file.store');
            Route::delete('project/task/comment/{id}', [ProjectController::class, 'commentDestroy'])->name('project.task.comment.destroy');
            Route::delete('project/task/file/{id}', [ProjectController::class, 'commentDestroyFile'])->name('project.task.comment.file.destroy');

            Route::get('project/{id}/milestone', [ProjectController::class, 'milestone'])->name('project.milestone.create');
            Route::post('roject/{id}/milestone', [ProjectController::class, 'milestoneStore'])->name('project.milestone.store');
            Route::get('project/milestone/{id}/edit', [ProjectController::class, 'milestoneEdit'])->name('project.milestone.edit');
            Route::post('project/milestone/{id}', [ProjectController::class, 'milestoneUpdate'])->name('project.milestone.update');
            Route::delete('project/milestone/{id}', [ProjectController::class, 'milestoneDestroy'])->name('project.milestone.destroy');
            Route::get('project/milestone/{id}/show', [ProjectController::class, 'milestoneShow'])->name('project.milestone.show');
            Route::get('project/task', [ProjectController::class, 'task'])->name('project.task');

            Route::get('project/{id}/note', [ProjectController::class, 'notes'])->name('project.note.create');
            Route::post('project/{id}/note', [ProjectController::class, 'noteStore'])->name('project.note.store');
            Route::get('project/{pid}/note/{id}', [ProjectController::class, 'noteEdit'])->name('project.note.edit');
            Route::post('project/{pid}/note/{id}', [ProjectController::class, 'noteupdate'])->name('project.note.update');
            Route::delete('project/{pid}/note/{id}', [ProjectController::class, 'noteDestroy'])->name('project.note.destroy');

            Route::get('project/{id}/file', [ProjectController::class, 'file'])->name('project.file.create');
            Route::post('project/{id}/file', [ProjectController::class, 'fileStore'])->name('project.file.store');
            Route::get('project/{pid}/file/{id}', [ProjectController::class, 'fileEdit'])->name('project.file.edit');
            Route::post('project/{pid}/file/{id}', [ProjectController::class, 'fileupdate'])->name('project.file.update');
            Route::delete('project/{pid}/file/{id}', [ProjectController::class, 'fileDestroy'])->name('project.file.destroy');

            Route::post('project/{id}/comment', [ProjectController::class, 'projectCommentStore'])->name('project.comment.store');
            Route::get('project/{id}/comment', [ProjectController::class, 'projectComment'])->name('project.comment.create');
            Route::get('project/{id}/comment/{cid}/reply', [ProjectController::class, 'projectCommentReply'])->name('project.comment.reply');


            Route::post('project/{id}/client/feedback', [ProjectController::class, 'projectClientFeedbackStore'])->name('project.client.feedback.store');
            Route::get('project/{id}/client/feedback', [ProjectController::class, 'projectClientFeedback'])->name('project.client.feedback.create');
            Route::get('project/{id}/client/feedback/{cid}/reply', [ProjectController::class, 'projectClientFeedbackReply'])->name('project.client.feedback.reply');

            Route::get('project/{id}/timesheet', [ProjectController::class, 'projectTimesheet'])->name('project.timesheet.create');
            Route::post('project/{id}/timesheet', [ProjectController::class, 'projectTimesheetStore'])->name('project.timesheet.store');
            Route::get('project/{id}/timesheet/{tid}/edit', [ProjectController::class, 'projectTimesheetEdit'])->name('project.timesheet.edit');
            Route::post('project/{id}/timesheet{tid}/edit', [ProjectController::class, 'projectTimesheetUpdate'])->name('project.timesheet.update');
            Route::DELETE('project/{pid}/timesheet/{id}', [ProjectController::class, 'projectTimesheetDestroy'])->name('project.timesheet.destroy');
            Route::get('project/{id}/timesheet/{tid}/note', [ProjectController::class, 'projectTimesheetNote'])->name('project.timesheet.note');


            Route::get('project/timesheet', [ProjectController::class, 'timesheet'])->name('project.timesheet');

            //    For Project All Task
            Route::get('project/allTask', [ProjectController::class, 'allTask'])->name('project.all.task');
            Route::get('project/allTaskKanban', [ProjectController::class, 'allTaskKanban'])->name('project.all.task.kanban');
            Route::get('project/alltask/gantt-chart/{duration?}', [ProjectController::class, 'allTaskGanttChart'])->name('project.all.task.gantt.chart');
            Route::post('project/milestone', [ProjectController::class, 'getMilestone'])->name('project.getMilestone');
            Route::post('project/user', [ProjectController::class, 'getUser'])->name('project.getUser');


            //    For Project All Task
            Route::get('project/allTimesheet', [ProjectController::class, 'allTimesheet'])->name('project.all.timesheet');
            Route::post('project/task', [ProjectController::class, 'getTask'])->name('project.getTask');



            // Gantt Chart


            Route::post('projects/{id}/gantt', [ProjectController::class, 'ganttPost'])->name('project.gantt.post')->middleware(
                [
                    'auth',
                    'XSS',
                    'revalidate',
                ]
            );


            // Project Task Timer
            Route::post('project/task/timer', [ProjectController::class, 'taskStart'])->name('project.task.timer');


            Route::resource('project', ProjectController::class)->middleware(
                [
                    'auth',
                    'XSS',
                    'revalidate',
                ]
            );
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('projectStage', ProjectStageController::class);
            Route::post('projectStage/order', [ProjectStageController::class, 'order'])->name('projectStage.order');
        }
    );

    Route::resource('payment', PaymentController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {

            Route::get('creditNote/invoice', [CreditNoteController::class, 'getinvoice'])->name('invoice.get');
            Route::resource('creditNote', CreditNoteController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('expense', ExpenseController::class);
        }
    );
    // contract
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('contract/{id}/description', [ContractController::class, 'description'])->name('contract.description');
            Route::get('contract/grid', [ContractController::class, 'grid'])->name('contract.grid');
            Route::resource('contract', ContractController::class);

            // contract dwonload
            Route::get('contract/{id}/get_contract', [ContractController::class, 'printContract'])->name('get.contract');
            Route::get('/contract/{id}/mail', [ContractController::class, 'sendmailContract'])->name('send.mail.contract');
            Route::get('contract/pdf/{id}', [ContractController::class, 'pdffromcontract'])->name('contract.download.pdf');

            Route::post('/contract_status_edit/{id}', [ContractController::class, 'contract_status_edit'])->name('contract.status')->middleware(['auth', 'XSS']);
            Route::get('/signature/{id}', [ContractController::class, 'signature'])->name('signature')->middleware(['auth', 'XSS']);
            Route::post('/signaturestore', [ContractController::class, 'signatureStore'])->name('signaturestore')->middleware(['auth', 'XSS']);
        }
    );
    Route::post('contract/{id}/contract_description', [ContractController::class, 'contract_descriptionStore'])->name('contract.contract_description.store')->middleware(['auth']);
    Route::post('/contract/{id}/file', [ContractController::class, 'fileUpload'])->name('contract.file.upload')->middleware(['auth', 'XSS']);
    Route::get('/contract/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name('contracts.file.download')->middleware(['auth', 'XSS']);
    Route::delete('/contract/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name('contracts.file.delete')->middleware(['auth', 'XSS']);





    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('contractType', ContractTypeController::class);
        }
    );
    Route::post('/contract/{id}/comment', [ContractController::class, 'commentStore'])->name('comment.store');
    Route::post('/contract/{id}/notes', [ContractController::class, 'noteStore'])->name('note_store.store')->middleware(['auth']);
    Route::delete('/contract/{id}/notes', [ContractController::class, 'noteDestroy'])->name('note_store.destroy')->middleware(['auth']);
    Route::delete('/contract/{id}/comment', [ContractController::class, 'commentDestroy'])->name('comment_store.destroy');
    Route::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name('project.by.user.id')->middleware(['auth', 'XSS']);

    //client wise project show in modal
    Route::any('/contract/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name('contract.clients.select');

    //copy contract
    Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name('contract.copy')->middleware(['auth', 'XSS']);
    Route::post('/contract/copy/store', [ContractController::class, 'copycontractstore'])->name('contract.copy.store')->middleware(['auth', 'XSS']);


    ////**===================================== Project Reports =======================================================////


    Route::resource('/project_report', ProjectReportController::class)->middleware(['auth', 'XSS']);
    Route::post('/project_report_data', [ProjectReportController::class, 'ajax_data'])->name('projects.ajax')->middleware(['auth', 'XSS']);
    Route::post('/project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')->middleware(['auth', 'XSS']);
    Route::get('export/task_report/{id}', [ProjectReportController::class, 'export'])->name('project_report.export');
    Route::get('noticeBoard/grid', [NoticeBoardController::class, 'grid'])->name('noticeBoard.grid')->middleware(['auth', 'XSS']);



    Route::resource('noticeBoard', NoticeBoardController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('goal', GoalController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('note', NoteController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('event/employee', [EventController::class, 'getEmployee'])->name('event.employee');
            Route::resource('event', EventController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');
            Route::post('support/{id}/reply', [SupportController::class, 'replyAnswer'])->name('support.reply.answer');
            Route::get('support/grid', [SupportController::class, 'grid'])->name('support.grid');
            Route::resource('support', SupportController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('plan', PlanController::class);
            Route::get('plan/plan-trial/{id}', [PlanController::class, 'PlanTrial'])->name('plan.trial');
            Route::post('plan/plan-active', [PlanController::class, 'planActive'])->name('plan.enable')->middleware(['auth', 'XSS']);
        }
    );

    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::resource('plan_request', PlanRequestController::class);
        }
    );
    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['auth', 'XSS', 'revalidate',]);




    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('coupon', CouponController::class);
        }
    );
    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');
    Route::get('/change/mode', [UserController::class, 'changeMode'])->name('change.mode');





    //========================================HR===============================
    Route::resource('account-assets', AssetController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('document-upload', DocumentUploadController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('company-policy', CompanyPolicyController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );


    Route::resource('award', AwardController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('transfer', TransferController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('award-type', AwardTypeController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('resignation', ResignationController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('trip', TripController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('promotion', PromotionController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('complaint', ComplaintController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('warning', WarningController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );


    Route::resource('termination', TerminationController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('termination-type', TerminationTypeController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    Route::resource('indicator', IndicatorController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );


    Route::resource('appraisal', AppraisalController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('training-type', TrainingTypeController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('performanceType', PerformanceTypeController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('competencies', CompetenciesController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::resource('trainer', TrainerController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );


    Route::post('training/status', [TrainingController::class, 'updateStatus'])->name('training.status')->middleware(['auth', 'XSS', 'revalidate',]);

    Route::resource('training', TrainingController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );



    //======================================== OLD ===============================
    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::post('edit-profile', [UserController::class, 'editprofile'])->name('update.account')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::post('edit-client-profile/{id}', [UserController::class, 'clientCompanyInfoEdit'])->name('client.update.company')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::post('edit-client-personal-info/{id}', [UserController::class, 'clientPersonalInfoEdit'])->name('client.personal.update')->middleware(['auth', 'XSS', 'revalidate',]);


    Route::resource('users', UserController::class)->middleware(['auth', 'XSS', 'revalidate',]);

    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::resource('unit', UnitController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('paymentMethod', PaymentMethodController::class);
        }
    );


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('user', UserController::class);
        }
    );
    Route::any('user-reset-password/{id}', [UserController::class, 'userPassword'])->name('user.reset');
    Route::post('user-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update');
    Route::get('user-login/{id}', [UserController::class, 'LoginManage'])->name('user.login');

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('item/grid', [ItemController::class, 'grid'])->name('item.grid');
            Route::resource('item', ItemController::class);
        }
    );
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('proposal', ProposalController::class);
        }
    );
    Route::get('invoice/preview/{template}/{color}/{code}', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
    Route::post('invoice/template/setting', [InvoiceController::class, 'saveInvoiceTemplateSettings'])->name('invoice.template.setting');
    // Route::get('invoice/pdf/{id}', [InvoiceController::class, 'pdf'])->name('invoice.pdf')>middleware(['XSS']);
    // Route::get('invoice/pdf/{id}', 'InvoiceController@pdf')->name('invoice.pdf')->middleware(['XSS']);



    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('invoice/client/project', [InvoiceController::class, 'getClientProject'])->name('invoice.client.project');
            Route::get('invoice/{id}/item/create', [InvoiceController::class, 'createItem'])->name('invoice.create.item');
            Route::any('invoice/{id}/product/store', [InvoiceController::class, 'storeProduct'])->name('invoice.store.product');
            Route::delete('invoice/{id}/product/{uid}', [InvoiceController::class, 'productDestroy'])->name('invoice.items.destroy');
            Route::post('invoice/{id}/project/store', [InvoiceController::class, 'storeProject'])->name('invoice.store.project');
            Route::get('invoice/{id}/send', [InvoiceController::class, 'send'])->name('invoice.send');
            Route::get('invoice/{id}/receipt/create', [InvoiceController::class, 'createReceipt'])->name('invoice.create.receipt');
            Route::post('invoice/{id}/receipt/store', [InvoiceController::class, 'storeReceipt'])->name('invoice.store.receipt');
            Route::delete('invoice/{id}/payment/{pid}', [InvoiceController::class, 'paymentDelete'])->name('invoice.payment.delete');
            Route::delete('invoice/{id}/bankpayment/{pid}', [InvoiceController::class, 'bankpaymentDelete'])->name('invoice.bankpayment.delete');

            Route::get('invoice/status', [InvoiceController::class, 'statusChange'])->name('invoice.status.change');
            Route::get('invoice/item', [InvoiceController::class, 'items'])->name('invoice.items');
            Route::delete('invoice/{id}/item/{pid}', [InvoiceController::class, 'itemDelete'])->name('invoice.item.delete');
            Route::get('invoice/grid', [InvoiceController::class, 'grid'])->name('invoice.grid');


            Route::get('invoice/grid', 'InvoiceController@grid')->name('invoice.grid');

            Route::resource('invoice', InvoiceController::class);
        }
    );


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
            ],
        ],
        function () {
            Route::get('task-report', [ReportController::class, 'task'])->name('report.task');
            Route::get('timelog-report', [ReportController::class, 'timelog'])->name('report.timelog');
            Route::get('finance-report', [ReportController::class, 'finance'])->name('report.finance');
            Route::get('income-expense-report', [ReportController::class, 'incomeVsExpense'])->name('report.income.expense');
            Route::get('leave-report', [ReportController::class, 'leave'])->name('report.leave');
            Route::get('export/task_report', [ReportController::class, 'TaskReportExport'])->name('task_report.export');
            Route::get('export/leave_report', [LeaveController::class, 'LeaveReportExport'])->name('leave_report.export');
            Route::get('export/timelog_report', [ReportController::class, 'TimelogExport'])->name('timelog_report.export');
            Route::get('export/finance_report', [ReportController::class, 'FinanceExport'])->name('finance_report.export');

            Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave')->middleware(['auth', 'XSS',]);

            Route::get('estimate-report', [ReportController::class, 'estimate'])->name('report.estimate');
            Route::get('invoice-report', [ReportController::class, 'invoice'])->name('report.invoice');
            Route::get('lead-report', [ReportController::class, 'lead'])->name('report.lead');
            Route::get('deal-report', [ReportController::class, 'deal'])->name('report.deal');
            Route::get('client-report', [ReportController::class, 'client'])->name('report.client');
            Route::get('attendance-report', [ReportController::class, 'attendance'])->name('report.attendance');
            Route::get('stock-report', [ReportController::class, 'productStock'])->name('report.product.stock.report');
            Route::get('export/invoice_report', [ReportController::class, 'InvoiceReportExport'])->name('invoice_report.export');
            Route::get('export/lead_report', [ReportController::class, 'LeadExport'])->name('lead_report.export');
            Route::get('export/deal_report', [ReportController::class, 'DealExport'])->name('deal_report.export');

            Route::get('export/client_report', [ReportController::class, 'ClientExport'])->name('client_report.export');
            Route::get('export/estimate_report', [ReportController::class, 'EstimateExport'])->name('estimate_report.export');
            Route::get('export/stock_report', [ReportController::class, 'StockReportExport'])->name('stock_report.export');
        }
    );
    Route::get('report/attendance/{month}/{department}', [ReportController::class, 'exportCsv'])->name('report.attendance.monthly');

    Route::get('report/attendance/{month}/{department}', [ReportController::class, 'exportCsv'])->name('report.attendance.monthly')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::group(
        ['middleware' => ['auth', 'XSS', 'revalidate',],],
        function () {
            Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language')->middleware(['auth', 'XSS', 'revalidate',]);
            Route::get('manage-language/{lang}', [LanguageController::class, 'manageLanguage'])->name('manage.language')->middleware(['auth', 'XSS', 'revalidate',]);
            Route::post('store-language-data/{lang}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data')->middleware(['auth', 'XSS', 'revalidate',]);
            Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language')->middleware(['auth', 'XSS', 'revalidate',]);
            Route::post('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language')->middleware(['auth', 'XSS', 'revalidate',]);
            Route::delete('/lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy')->middleware(['auth', 'XSS', 'revalidate',]);
        }
    );
    Route::get('user/{id}/plan', [UserController::class, 'upgradePlan'])->name('plan.upgrade')->middleware(['auth', 'XSS', 'revalidate',]);
    Route::get('user/{id}/plan/{pid}', [UserController::class, 'activePlan'])->name('plan.active')->middleware(['auth', 'XSS', 'revalidate',]);



    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware(['auth', 'XSS']);
    Route::post('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language')->middleware(['auth']);
    // Route::post('email_template_status/{id}', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language')->middleware(['auth']);
    Route::post('email_template_status', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language')->middleware(['auth', 'XSS',]);
    Route::resource('email_template', EmailTemplateController::class)->middleware(['auth', 'XSS', 'revalidate',]);

    Route::resource('email_template_lang', EmailTemplateLangController::class)->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );

    // Form Builder

    Route::resource('form_builder', FormBuilderController::class)->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    // Form link base view

    Route::post('/form_view_store', [FormBuilderController::class, 'formViewStore'])->name('form.view.store');


    // Form Field

    Route::get('/form_builder/{id}/field', [FormBuilderController::class, 'fieldCreate'])->name('form.field.create')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::post('/form_builder/{id}/field', [FormBuilderController::class, 'fieldStore'])->name('form.field.store')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::get('/form_builder/{id}/field/{fid}/show', [FormBuilderController::class, 'fieldShow'])->name('form.field.show')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::get('/form_builder/{id}/field/{fid}/edit', [FormBuilderController::class, 'fieldEdit'])->name('form.field.edit')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::post('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldUpdate'])->name('form.field.update')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::delete('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldDestroy'])->name('form.field.destroy')->middleware(
        [
            'auth',
            'XSS',
        ]
    );



    // Form Response
    Route::get('/form_response/{id}', [FormBuilderController::class, 'viewResponse'])->name('form.response')->middleware(['auth', 'XSS',]);
    Route::get('/response/{id}', [FormBuilderController::class, 'responseDetail'])->name('response.detail')->middleware(['auth', 'XSS',]);



    // Form Field Bind
    Route::get('/form_field/{id}', [FormBuilderController::class, 'formFieldBind'])->name('form.field.bind')->middleware(['auth', 'XSS',]);
    Route::post('/form_field_store/{id}', [FormBuilderController::class, 'bindStore'])->name('form.bind.store')->middleware(['auth', 'XSS',]);


    // end Form Builder


    //================================= Custom Landing Page ====================================//
    Route::get('/landingpage', [LandingPageSectionController::class, 'index'])->name('custom_landing_page.index')->middleware(['auth', 'XSS',]);

    // Route::get('/LandingPage/show/{id}', 'LandingPageSectionController@show');
    // Route::post('/LandingPage/setConetent', 'LandingPageSectionController@setConetent')->middleware(['auth','XSS']);
    // Route::get('/get_landing_page_section/{name}', function($name) {
    //     $plans = \DB::table('plans')->get();
    //     return view('custom_landing_page.'.$name,compact('plans'));
    // });
    // Route::post('/LandingPage/removeSection/{id}', 'LandingPageSectionController@removeSection')->middleware(['auth','XSS']);
    // Route::post('/LandingPage/setOrder', 'LandingPageSectionController@setOrder')->middleware(['auth','XSS']);
    // Route::post('/LandingPage/copySection', 'LandingPageSectionController@copySection')->middleware(['auth','XSS']);



//================================= Referral Page ====================================//
Route::get('/referral-program', [referralController::class, 'index'])->name('referral.index')->middleware(['auth', 'XSS',]);

Route::post('/referral-program/setting/store', [referralController::class, 'store'])->name('setting.store')->middleware(['auth', 'XSS',]);
Route::post('/referral-program/setting/payout/store', [referralController::class, 'payoutstore'])->name('payout.store')->middleware(['auth', 'XSS',]);
Route::post('/referral-program/setting/payout/store/status', [referralController::class, 'storestatus'])->name('referral_store.status')->middleware(['auth', 'XSS',]);


// ========  company side ========//
Route::get('/referral-program/company/guideline', [referralController::class, 'guideline'])->name('guideline.index')->middleware(['auth', 'XSS',]);



    //================================= Plan Payment Gateways  ====================================//

    Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack')->middleware(['auth', 'XSS']);
    Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack');

    Route::post('/plan-pay-with-flaterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name('plan.pay.with.flaterwave')->middleware(['auth', 'XSS']);
    Route::get('/plan/flaterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name('plan.flaterwave');

    Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name('plan.pay.with.razorpay')->middleware(['auth', 'XSS']);
    Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name('plan.razorpay');

    Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name('plan.pay.with.paytm')->middleware(['auth', 'XSS']);
    Route::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name('plan.paytm');

    Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name('plan.pay.with.mercado')->middleware(['auth', 'XSS']);
    Route::any('/plan/mercado/{plan}', [MercadoPaymentController::class, 'getPaymentStatus'])->name('plan.mercado.callback');


    Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name('plan.pay.with.mollie')->middleware(['auth', 'XSS']);
    Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name('plan.mollie');

    Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name('plan.pay.with.skrill')->middleware(['auth', 'XSS']);
    Route::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name('plan.skrill');

    Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name('plan.pay.with.coingate')->middleware(['auth', 'XSS']);
    Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name('plan.coingate');

    Route::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name('plan.paymentwallpayment')->middleware(['auth', 'XSS']);
    Route::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name('plan.pay.with.paymentwall')->middleware(['auth', 'XSS']);
    Route::get('/plans/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');

    Route::Post('plan-pay-with-toyyibpay', [ToyyibpayController::class, 'charge'])->name('plan.pay.with.toyyibpay')->middleware(['auth', 'XSS']);
    Route::get('/plan/toyyibpay/{plan}/{coupon}/{amount}', [ToyyibpayController::class, 'status'])->name('plan.toyyibpay');

    Route::post('payfast-plan', [PayfastController::class, 'index'])->name('payfast.payment')->middleware(['auth']);
    Route::get('payfast-plan/{success}', [PayfastController::class, 'success'])->name('payfast.payment.success')->middleware(['auth']);


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('order/index', [StripePaymentController::class, 'index'])->name('order.index');
            Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
            Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
	        Route::any('/stripe-payment-status', [StripePaymentController::class, 'planGetStripePaymentStatus'])->name('stripe.payment.status')->middleware(['XSS']);
            //Order Action
            Route::get('order/{id}/action', [StripePaymentController::class, 'orderAction'])->name('order.action');
            Route::delete('order/delete/{id}', [StripePaymentController::class, 'deleteOrder'])->name('order.destroy');
            Route::get('/refund/{id}/{user_id}', [StripePaymentController::class, 'refund'])->name('order.refund');
        }
    );


    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );
    Route::get('{id}/{amount}{coupon?}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(
        [
            'auth',
            'XSS',
            'revalidate',
        ]
    );


    Route::get('/invoices/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');

    //Double entry
    Route::post('chart-of-account/subtype', [ChartOfAccountController::class, 'getSubType'])->middleware('XSS', 'auth', 'revalidate')->name('charofAccount.subType');



    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS', 'revalidate',
            ],
        ],
        function () {

            Route::resource('chart-of-account', ChartOfAccountController::class);
        }
    );




    //==

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS', 'revalidate',
            ],
        ],
        function () {

            Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy');
            Route::resource('journal-entry', JournalEntryController::class);
        }
    );


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS', 'revalidate',
            ],
        ],
        function () {

            Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy');
            // Route::post('journal-entry/account/edit', [JournalEntryController::class, 'accountEdit'])->name('journal.account.edit');
            Route::resource('journal-entry', JournalEntryController::class);
        }
    );

    //add module double entry
    Route::get('report/ledger', [ReportController::class, 'ledgerSummary'])->name('report.ledger');
    Route::get('report/balance-sheet', [ReportController::class, 'balanceSheet'])->name('report.balance.sheet');
    Route::get('report/trial-balance', [ReportController::class, 'trialBalanceSummary'])->name('trial.balance')->middleware(['auth','XSS']);
    Route::get('report/ledger', [ReportController::class, 'ledgerSummary'])->name('report.ledger');


        //Goal Tracking
    ;

    Route::resource('goaltracking', GoalTrackingController::class)->middleware(
        [
            'auth',
            'XSS',
        ]
    );


    Route::resource('branch', BranchController::class)->middleware(['auth', 'XSS',]);

    Route::resource('goaltype', GoalTypeController::class)->middleware(
        ['auth', 'XSS',]
    );

    // Plan Request Module
    Route::get('plan_request', [PlanRequestController::class, 'index'])->name('plan_request.index')->middleware(['auth', 'XSS',]);
    Route::get('request_frequency/{id}', [PlanRequestController::class, 'requestView'])->name('request.view')->middleware(['auth', 'XSS',]);
    Route::get('request_send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request')->middleware(['auth', 'XSS',]);
    Route::get('request_response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request')->middleware(['auth', 'XSS',]);
    Route::get('request_cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel')->middleware(['auth', 'XSS',]);

    // End Plan Request Module

    //Invoice Copy Button

    Route::get('invoice/pay/pdf/{id}', [InvoiceController::class, 'pdffrominvoice'])->name('invoice.download.pdf');


    Route::get('estimate/pay/pdf/{id}', [EstimateController::class, 'pdffromestimate'])->name('estimate.download.pdf');

    //Import/Export
    Route::get('export/employee', [EmployeeController::class, 'export'])->name('employee.export');


    Route::get('import/employee/file', [EmployeeController::class, 'importFile'])->name('employee.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/employee', [EmployeeController::class, 'import'])->name('employee.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::get('import/client/file', [ClientController::class, 'importFile'])->name('client.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::post('import/client', [ClientController::class, 'import'])->name('client.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );


    Route::get('import/attendance/file', [AttendanceController::class, 'importFile'])->name('attendance.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::any('import/attendance', [AttendanceController::class, 'import'])->name('attendance.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::get('import/holiday/file', [HolidayController::class, 'importFile'])->name('holiday.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/holiday', [HolidayController::class, 'import'])->name('holiday.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::any('event/get_event_data', [EventController::class, 'get_event_data'])->name('event.get_event_data')->middleware(['auth', 'XSS']);
    Route::any('holiday/get_holiday_data', [HolidayController::class, 'get_holiday_data'])->name('holiday.get_holiday_data')->middleware(['auth', 'XSS']);
    Route::any('meeting/get_holiday_data', [MeetingController::class, 'get_holiday_data'])->name('meeting.get_holiday_data')->middleware(['auth', 'XSS']);
    Route::any('zoom-meeting/get_holiday_data', [ZoommeetingController::class, 'get_holiday_data'])->name('zoom-meeting.get_holiday_data')->middleware(['auth', 'XSS']);
    Route::any('leave/get_holiday_data', [LeaveController::class, 'get_holiday_data'])->name('leave.get_holiday_data')->middleware(['auth', 'XSS']);
    Route::any('task/get_holiday_data', [ProjectController::class, 'get_holiday_data'])->name('task.get_holiday_data')->middleware(['auth', 'XSS']);


    Route::get('import/assets/file', [AssetController::class, 'importFile'])->name('asset.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/assets', [AssetController::class, 'import'])->name('assets.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::get('export/item', [ItemController::class, 'export'])->name('item.export');

    Route::get('import/asset/file', [ItemController::class, 'importFile'])->name('item.file.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    Route::post('import/item', [ItemController::class, 'import'])->name('item.import')->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::get('export/meeting', [MeetingController::class, 'export'])->name('meeting.export');

    Route::get('export/award', [AwardController::class, 'export'])->name('award.export');

    Route::get('export/invoice', [InvoiceController::class, 'export'])->name('invoice.export');

    Route::get('export/creditnote', [CreditNoteController::class, 'export'])->name('creditnote.export');

    Route::get('export/goal', [GoalController::class, 'export'])->name('goal.export');



    //================================Budget Plan======================================//
    Route::resource('budget', BudgetController::class)->middleware(
        [
            'auth',
            'XSS',
        ]
    );

    //================================Time Tracker======================================//

    Route::resource('timetracker', TimeTrackerController::class)->middleware(
        [
            'auth',
            'XSS',
        ]
    );
    Route::post('tracker/image-view', [TimeTrackerController::class, 'getTrackerImages'])->name('tracker.image.view');
    Route::delete('tracker/image-remove', [TimeTrackerController::class, 'removeTrackerImages'])->name('tracker.image.remove');
    Route::any('time-Tracker', [SettingController::class, 'timeTracker'])->name('setting.timeTracker')->middleware(['auth', 'XSS']);

    //==============================Zoom Meeting ========================================//
    Route::any('/setting/saveZoomSettings', [SettingController::class, 'saveZoomSettings'])->name('setting.ZoomSettings')->middleware(['auth', 'XSS']);

    Route::group(['middleware' => ['auth', 'XSS', 'revalidate',],], function () {
        Route::get('zoommeeting/calendar', [ZoommeetingController::class, 'calendar'])->name('zoommeeting.calendar');
        Route::resource('zoommeeting', ZoommeetingController::class);
    });

    Route::get('/zoom/project/select/{id}', [ZoommeetingController::class, 'projectwiseuser'])->name('zoom.project.select');
    Route::post('setting/slack', [SettingController::class, 'slack'])->name('slack.setting');

    //==============================telegram===============================
    Route::post('setting/telegram', [SettingController::class, 'telegram'])->name('telegram.setting');

    //==============================twilio===============================
    Route::post('setting/twilio', [SettingController::class, 'twilio'])->name('twilio.setting');

    //==================================Recaptcha================================

    Route::post('/recaptcha-settings', [SettingController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store')->middleware(['auth', 'XSS']);
    Route::get('{image}/payment/attachment/{extention}', [PaymentController::class, 'download'])->name('payment.receipt');
    Route::get('{image}/invoice/attachment/{extention}', [InvoiceController::class, 'download'])->name('invoice.receipt');
    Route::get('{image}/expense/attachment/{extention}', [ExpenseController::class, 'download'])->name('expense.receipt');
    Route::get('{image}/support/attachment/{extention}', [SupportController::class, 'download'])->name('support.receipt');
    Route::get('{image}/note/attachment/{extention}', [NoteController::class, 'download'])->name('note.receipt');

    //==========================ItemStock===================================//
    Route::resource('itemstock', ItemStockController::class)->middleware(['auth', 'XSS']);
    //Clear Config cache:
    Route::get('/config-cache', function () {
        $exitCode = \Artisan::call('config:cache');
        return '<h1>Clear Config cleared</h1>';
    });
    Route::get('/config-clear', function () {
        $exitCode = \Artisan::call('config:clear');
        return '<h1>Clear Config cleared</h1>';
    });
});


//-=======appricalStar==========
Route::post('/appraisals', [AppraisalController::class, 'empByStar'])->name('empByStar')->middleware(['auth', 'XSS']);
Route::post('/appraisals1', [AppraisalController::class, 'empByStar1'])->name('empByStar1')->middleware(['auth', 'XSS']);
Route::post('/getemployee', [AppraisalController::class, 'getemployee'])->name('getemployee');
// cache clear
Route::get('/config-cache', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Clear Cache successfully.');
});

//SEO Setting
Route::post('seo-setting', [SettingController::class, 'saveseo'])->name('seo.settings');

//=========Webhook Settings===============
Route::get('webhook-create', [SettingController::class, 'webhooksettings'])->name('webhook.create');
Route::post('webhook-store', [SettingController::class, 'webhookstore'])->name('webhook.store');
Route::get('webhook-edit/{id}', [SettingController::class, 'editwebhook'])->name('webhook.edit');
Route::post('webhook-update/{id}', [SettingController::class, 'updatewebhook'])->name('webhook.update');
Route::delete('webhook/delete/{id}', [SettingController::class, 'webhookdestroy'])->name('webhook.destroy');
// Route::post('webhook/response/get', [SettingController::class, 'WebhookResponse'])->name('webhooks.response.get');

// cookie consent setting
Route::any('setting/cookie-consent', [SettingController::class, 'saveCookieSettings'])->name('cookie.setting');
Route::any('/cookie-consent', [SettingController::class, 'CookieConsent'])->name('cookie-consent');
//Notification template

Route::resource('notification-templates', NotificationTemplatesController::class)->middleware(['auth', 'XSS',]);
Route::get('notification-templates/{id?}/{lang?}/', [NotificationTemplatesController::class, 'index'])->name('notification-templates.index')->middleware(['auth', 'XSS']);


//User Log
Route::get('/userlogs', [UserController::class, 'userLog'])->name('user.userlog')->middleware(['auth', 'XSS']);
Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name('user.userlogview')->middleware(['auth', 'XSS']);
Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name('user.userlogdestroy')->middleware(['auth', 'XSS']);

//admin hub

Route::get('login-with-company/exit', [UserController::class, 'ExitCompany'])->name('exit.company');
Route::get('admin-info/{id}', [UserController::class, 'CompanyInfo'])->name('company.info');
Route::post('user-unable', [UserController::class, 'UserUnable'])->name('user.unable');
Route::get('users/{id}/login-with-company', [UserController::class, 'LoginWithCompany'])->name('login.with.company');

Route::get('/clientlogs', [ClientController::class, 'clientLog'])->name('client.clientlog')->middleware(['auth', 'XSS']);


// Bank Transfer
Route::post('bankDetail', [BankTransferController::class, 'store'])->name('banktrasfer.store');
Route::any('order/approve/{id?}/{approval?}', [BankTransferController::class, 'approve'])->name('order.approve');
Route::any('order/reject/{id?}/{reject?}', [BankTransferController::class, 'reject'])->name('order.reject');

Route::get('bankpaymenttransfer/approve/{id?}/{approval?}', [BankTransferController::class, 'invoiceApprove'])->name('bankpaymenttransfer.approve');
Route::any('bankpaymenttransfer/reject/{id?}/{reject?}', [BankTransferController::class, 'invoiceReject'])->name('bankpaymenttransfer.reject');

//chatGpt
Route::post('chatgptkey', [SettingController::class, 'chatgptkey'])->name('settings.chatgptkey');
Route::get('generate/{template_name?}', [AiTemplateController::class, 'create'])->name('generate');
Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');

//Message AI
Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware(['auth', 'XSS']);;
Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware(['auth', 'XSS']);;


//iyzipay
Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('plan.pay.with.iyzipay');
Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

Route::post('{id}/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name('client.pay.with.iyzipay');
Route::post('iyzipay/callback/{invoice}/{amount}', [IyzipayController::class, 'getInvoiceiyzipayCallback'])->name('iyzipay.invoicepayment.callback');

//sspay route
Route::post('sspay-prepare-plan', [SspayController::class, 'SspayPaymentPrepare'])->name('plan.pay.with.sspay')->middleware(['auth', 'XSS']);
Route::get('sspay-payment-plan/{plan_id}/{amount}/{frequency}/{couponCode?}', [SspayController::class, 'getPaymentStatus'])->name('plan.sspay')->middleware(['auth', 'XSS']);

//==================================== Manually added Routes ====================================//
Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage')->middleware(['auth', 'XSS']);

//-------paytabs payment
Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware(['auth'])->name('plan.pay.with.paytab');
Route::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->middleware(['auth'])->name('plan.paytab.success');

Route::post('pay-with-paytab/{slug}', [PaytabController::class, 'PayWithpaytab'])->name('pay.with.paytab');
Route::any('invoice-paytab-status/{invoice}/{amount}', [PaytabController::class, 'PaytabGetPaymentCallback'])->name('invoice.paytab.status');

//-------benefit payment
Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name('benefit.initiate');
Route::any('call_back', [BenefitPaymentController::class, 'call_back'])->name('benefit.call_back');

Route::any('invoice-with-benefit/', [BenefitPaymentController::class, 'paywithinvoicebenefit'])->name('pay.with.benefit');
Route::any('invoice-benefit-status/{invoice_id}/{amount}', [BenefitPaymentController::class, 'invoiceCall_back'])->name('invoice.benefit.status');


//--------cashfree payment
Route::post('cashfree/payments/', [CashfreeController::class, 'planPayWithcashfree'])->name('plan.pay.with.cashfree');
Route::any('cashfree/payments/success', [CashfreeController::class, 'getPaymentStatus'])->name('plan.cashfree');

Route::post('invoice-with-cashfree/', [CashfreeController::class, 'invoicePayWithcashfree'])->name('pay.with.cashfree');
Route::any('invoice-cashfree-status/{invoice_id}/{amount}', [CashfreeController::class, 'getInvociePaymentStatus'])->name('invoice.cashfree.status');

//--------aamarpay payment
Route::post('/aamarpay/payment', [AamarpayController::class, 'planPayWithpay'])->name('plan.pay.with.aamarpay');
Route::any('/aamarpay/success/{data}', [AamarpayController::class, 'getPaymentStatus'])->name('plan.aamarpay');

Route::post('invoice-with-aamarpay/', [AamarpayController::class, 'invoicePayWithaamarpay'])->name('pay.with.aamarpay');
Route::any('invoice-aamarpay-status/{data}', [AamarpayController::class, 'getInvociePaymentStatus'])->name('invoice.aamarpay.status');

// PayTr
Route::post('/paytr/payment/', [PaytrController::class, 'PlanpayWithPaytr'])->name('plan.pay.with.paytr');
Route::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('plan.pay.paytr.success');

Route::post('invoice-with-paytr/', [PaytrController::class, 'invoicePayWithpaytr'])->name('pay.with.paytr');
Route::any('invoice-paytr-status/', [PaytrController::class, 'getInvociePaymentStatus'])->name('invoice.paytr.status');


Route::any('/plan/yookassa/payment', [YooKassaController::class,'planPayWithYooKassa'])->name('plan.pay.with.yookassa');
Route::any('/plan/yookassa/{plan}', [YooKassaController::class,'planGetYooKassaStatus'])->name('plan.get.yookassa.status');

Route::any('/midtrans', [MidtransController::class, 'planPayWithMidtrans'])->name('plan.pay.with.midtrans');
Route::any('/midtrans/callback', [MidtransController::class, 'planGetMidtransStatus'])->name('plan.get.midtrans.status');

Route::any('/xendit/payment', [XenditPaymentController::class, 'planPayWithXendit'])->name('plan.pay.with.xendit');
Route::any('/xendit/payment/status', [XenditPaymentController::class, 'planGetXenditStatus'])->name('plan.xendit.status');

Route::post('invoice-with-yookassa/', [YooKassaController::class, 'invoicePayWithYookassa'])->name('invoice.with.yookassa');
Route::any('invoice-yookassa-status/{invoice_id}', [YooKassaController::class, 'getInvociePaymentStatus'])->name('invoice.yookassa.status');

Route::any('/invoice-with-xendit', [XenditPaymentController::class, 'invoicePayWithXendit'])->name('invoice.with.xendit');
Route::any('/invoice-xendit-status', [XenditPaymentController::class, 'getInvociePaymentStatus'])->name('invoice.xendit.status');

Route::any('invoice-with-midtrans/', [MidtransController::class, 'invoicePayWithMidtrans'])->name('invoice.with.midtrans');
Route::any('invoice-midtrans-status/', [MidtransController::class, 'getInvociePaymentStatus'])->name('invoice.midtrans.status');

Route::post('/invoice-pay-with/paiementpro',[PaiementProController::class,'invoicePayWithpaiementpro'])->name('invoice.pay.with.paiementPro');
Route::get('/invoice/paiementpro/{invoice_id}/{amount}/{type}',[PaiementProController::class,'getInvoicePaymentStatus'])->name('invoice.paiementpro');


// PaiementPro Payment

Route::group(['middleware' => 'PlanModuleCheck:PaiementPro'], function ()
{

Route::post('/paiementpro-setting/store', [PaiementProController::class ,'setting_store'])->name('paiementpro.setting.save');
});

Route::post('plan-pay-with/paiementpro', [PaiementProController::class, 'planPayWithpaiementpro'])->name('plan.pay.with.paiementpro');
Route::get('plan-get-paiementpro-status/{plan_id}',[PaiementProController::class,'planGetpaiementproStatus'])->name('plan.get.paiementpro.status');





// Nepalste pay

Route::post('/nepalste/payment', [NepalstePaymnetController::class, 'planPayWithnepalste'])->name('plan.pay.with.nepalste');
Route::get('nepalste/status/',[NepalstePaymnetController::class,'planGetNepalsteStatus'])->name('nepalste.status');
Route::get('nepalste/cancel/',[NepalstePaymnetController::class,'planGetNepalsteCancel'])->name('nepalste.cancel');


Route::post('/invoice-nepalste/payment', [NepalstePaymnetController::class, 'invoicePayWithnepalste'])->name('customer.with.nepalste');
Route::get('invoice-nepalste/status/',[NepalstePaymnetController::class,'invoiceGetNepalsteStatus'])->name('invoice.nepalste.status');
Route::get('invoice-nepalste/cancel/',[NepalstePaymnetController::class,'invoiceGetNepalsteCancel'])->name('invoice.nepalste.cancel');

Route::post('plan-pay-with/Clinet', [CinetPayController::class, 'planPayWithcinetpay'])->name("plan.pay.with.CinetPay");
Route::match(['get','post'],'/notify_url', [CinetPayController::class, 'notify_url'])->name('notify_url');
Route::match(['get','post'],'/return_url', [CinetPayController::class, 'return_url'])->name('return_url');
Route::any('Clinet/status/',[CinetPayController::class,'planGetCinetpayStatus'])->name('plan.cinetpay.status');
Route::get('Clinet/cancel/',[CinetPayController::class,'planGetClinetCancel'])->name('Clinet.cancel');


Route::post('invoice-pay-with/Clinet', [CinetPayController::class, 'invoicePayWithpaiementpro'])->name("customer.with.CinetPay");
Route::post('invoice-pay-with/Clinet/status/{data}', [CinetPayController::class, 'getInvoicePaymentStatus'])->name("customer.status.with.CinetPay");





Route::post('/plan/company/fedapay', [ControllersFedapayController::class, 'planPayWithFedapay'])->name('plan.pay.with.Fedapay');
Route::any('plan-get-fedapay-status/{plan_id}', [ControllersFedapayController::class, 'planGetFedapayStatus'])->name('plan.get.fedapay.status');

Route::post('/invoice-pay-with/fedapay',[ControllersFedapayController::class,'invoicePayWithFedapay'])->name('invoice.pay.with.fedapay');
Route::any('/invoice/fedapay/{invoice_id}/{amount}/{type}', [ControllersFedapayController::class,'getInvoicePaymentStatus'])->name('invoice.fedapay');



Route::post('/plan/company/payhere', [PayHereController::class, 'planPayWithPayHere'])->name('plan.pay.with.payhere')->middleware('auth');
Route::any('plan-get-payhere-status/{plan_id}', [PayHereController::class, 'planGetPayHereStatus'])->name('plan.get.payhere.status')->middleware('auth');

Route::post('/invoice-pay-with/payhere',[PayHereController::class,'invoicePayWithPayHere'])->name('invoice.pay.with.payhere');
Route::any('/invoice/payhere/{invoice_id}/{amount}/{type}',[PayHereController::class,'getInvoicePaymentStatus'])->name('invoice.payhere');