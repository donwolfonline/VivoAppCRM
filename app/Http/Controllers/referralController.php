<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\ReferralProgram;
use App\Models\transactions;
use App\Models\User;
use Google\Service\Datastore\Sum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class referralController extends Controller
{
    public function index()
    {
        if (Auth::user()->type == 'super admin') {
            $referralProgram = ReferralProgram::first();
            $transaction = transactions::all();
            $payouts = Payout::where('status', '')->get();
            // $userName= DB::table('users')
            // ->where('used_referral_code','referral_code')
            // ->get('name');
            // dd($userName);
            return view('referral.index', compact('referralProgram', 'transaction', 'payouts'));
        }
    }


    public function store(Request $request)
    {

        // $validator = \Validator::make(
        //     $request->all(),
        //     [
        //         'commission' => 'required|integer',
        //         'holdamt' => 'required|numeric',
        //         'guideline' => 'required|string',
        //     ]
        // );

        // if ($validator->fails()) {
        //     $messages = $validator->getMessageBag();
        //     return redirect()->back()->with('error', $messages->first());
        // }

        // Check if a record already exists
        $referralProgram = ReferralProgram::first();


        if ($referralProgram) {
            // If record exists, update it

            $referralProgram->update([
                'commission' => $request->commission,
                'hold_amount' => $request->holdamt,
                'guideline' => $request->guideline,
                'Reffral_enabled'=>$request->Reffral_enabled,
            ]);
        } else {
            // If record doesn't exist, create a new one
            $referralProgram = new ReferralProgram();
            $referralProgram->commission = $request->commission;
            $referralProgram->hold_amount = $request->holdamt;
            $referralProgram->guideline = $request->guideline;
            $referralProgram->Reffral_enabled=$request->Reffral_enabled;
            $referralProgram->save();
        }

        return redirect()->route('referral.index')->with('success', 'Referral program settings saved successfully!');
    }



    public function guideline()
    {


            $referralProgram = ReferralProgram::first();
            $myReferralCode = Auth::user()->referral_code; // Assuming the referral code is stored in the 'referral_code' column of the users table

            $transaction = DB::table('transactions')->where('used_referral_code', $myReferralCode)->get();

            $users = User::where('used_referral_code', $myReferralCode)->get();


            $totalCommission = DB::table('transactions')
                ->where('used_referral_code', $myReferralCode)
                ->sum('commission_amount');

            $paidCommission = DB::table('payouts')
                ->where('refercode', $myReferralCode)
                ->get();

            $totalpaidCommission = DB::table('payouts')
                ->where('refercode', $myReferralCode)
                ->where('status', 'accept')
                ->sum('amount');


                // $userName = DB::table('transactions')
                //     ->select('users.name')
                //     ->join('users', 'transactions.used_referral_code', '=', 'users.referral_code')
                //     ->where('transactions.used_referral_code', $useReferralCode) // Replace $useReferralCode with the referral code you want to search for
                //     ->value('users.name');









            return view('referral.company.index', compact('referralProgram', 'users', 'transaction', 'totalCommission', 'paidCommission', 'totalpaidCommission'));

    }

    public function payoutstore(Request $request)
    {
        $user    = \Auth::user();
        $referral = DB::table('referral_programs')->first();


        $transactions = transactions::where('uid', $user->id)->get();
        $total = count($transactions);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
        ]);

        // Create a new payout instance
        $payout = new Payout();

        // Assign values from the request to the payout object
        $payout->company_name = $user->name;
        $payout->amount = $request->amount;
        $payout->refercode = $user->referral_code;
        $payout->date = date('y-m-d');


        // Save the payout to the database
        $payout->save();

        // Redirect or return a response as needed
        return redirect()->route('guideline.index')->with('success', 'Payout created successfully!');
    }

    public function storestatus(Request $request)
    {

        $myReferralCode = DB::table('payouts')
            ->where('id', $request->id)
            ->value('refercode');


        $totalCommission = DB::table('transactions')
            ->where('used_referral_code', $myReferralCode)
            ->sum('commission_amount');




        $lessCommission = DB::table('payouts')
        ->where('refercode', $myReferralCode)
            ->where('status', 'accept')
            ->sum('amount');





        $aftercommissiomn = $totalCommission - $lessCommission;







        // Validate the incoming request if needed
        $request->validate([
            'status' => 'in:accept,reject', // Validate the status field
        ]);
        $referral = DB::table('referral_programs')->first();
        $amount = DB::table('payouts')->where('id', $request->id)->get('amount')->first();

        if ($request->status == 'accept') {




            // dd($referral->hold_amount);
            // dd($amount->amount);
            // dd($lessCommission);
            // dd($amount->amount <= $lessCommission);
            // dd($aftercommissiomn <= $totalCommission);
            // dd($aftercommissiomn<=$lessCommission );
            // dd($aftercommissiomn);
            // dd($totalCommission >= $lessCommission);
            // dd($amount->amount <= $aftercommissiomn);

            if ($referral->hold_amount < $amount->amount && $amount->amount <= $aftercommissiomn && $lessCommission >= 0) {


                $status = $request->status;
                $upd = DB::table('payouts')
                    ->where('id', $request->id)
                    ->update(['status' => $status]);



                // Redirect back or wherever you need to after storing the data
                if ($upd) {
                    return redirect()->back()->with('success', 'Status stored successfully.');
                } else {
                    return redirect()->back()->with('error', 'Status Not stored.');
                }
            } else {
                return redirect()->back()->with('error', 'Amount Is Invalid');
            }
        }






        $status = $request->status;
        $upd = DB::table('payouts')
            ->where('id', $request->id)
            ->update(['status' => $status]);



        // Redirect back or wherever you need to after storing the data
        if ($upd) {
            return redirect()->back()->with('success', 'Status stored successfully.');
        } else {
            return redirect()->back()->with('error', 'Status Not stored.');
        }
    }
}
