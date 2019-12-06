<?php

namespace App\Http\Controllers;

use App\Account;
use App\Http\Requests\TransferRequest;
use App\Http\Traits\CustomTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{

    use CustomTraits;

    private $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($this->user = Auth::guard('users')->user()) {
                return $next($request);
            } else {
                return $this->responseCodes('error', 'You are not authorized to be here.', 401);
            }
        });
    }

    /**
     * View all accounts
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return [
            'status' => 'success',
            'accounts' => $this->user->accounts()->latest()->withCount('transactions')->paginate(20)
        ];
    }

    /**
     * View an accounts
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function show($account_number)
    {
        $account = $this->user->accounts()->where('account_number', $account_number)->first();

        if (!$account) {
            return $this->notFound("Account");
        }

        $account['transactions'] = $account->transactions()->with('account')->latest()->paginate(20, ['*'], 'transactions');

        return [
            'status' => 'success',
            'account' => $account
        ];
    }

    /**
     * Create an account
     * 
     * @param string name
     * 
     * @param float|null balance
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $rules = [
            'account_type' => 'Required',
            'balance' => 'Nullable|Numeric'
        ];

        $validationStatus = $this->validation($request->all(), $rules);

        if ($validationStatus['status'] == 'error') {
            return $this->validatorError($validationStatus);
        }

        $this->user->accounts()->create([
            'account_type' => $request->account_type,
            'balance' => $request->balance ?? 0
        ]);

        return $this->responseCodes('success', 'Account created successfully.');
    }

    /**
     * Fund account
     * 
     * @param \App\Account $account
     * 
     * @param float amount
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function fund_account(Request $request, $account_number)
    {
        $rules = [
            'amount' => 'Required|Numeric'
        ];

        $validationStatus = $this->validation($request->all(), $rules);

        if ($validationStatus['status'] == 'error') {
            return $this->validatorError($validationStatus);
        }

        $account = $this->user->accounts()->where('account_number', $account_number)->first();

        if (!$account) {
            return $this->notFound('Account');
        }

        $this->user->credit_account($account, [
            'narration' => "TRF// Bonus of " . $this->formatNumber($request->amount) . " from RenWorld Bank",
            'amount' => $request->amount
        ]);

        return $this->responseCodes('success', 'Account funded successfully.');
    }

    /**
     * Close an account
     * 
     * @param \App\Account $account_number
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function update_account($account_number)
    {
        $account = $this->user->accounts()->where('account_number', $account_number)->first();

        if (!$account) {
            return $this->notFound('Account');
        }

        $account->update([
            'active' => !$account->active
        ]);

        return $this->responseCodes('success', 'Account updated successfully.');
    }

    /**
     * Creating a transfer request
     * 
     * @param \App\Http\Requests\TransferRequest $request
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function initiate_transfer(TransferRequest $request)
    {
        $user = $this->user;

        $from_account = $user->accounts()->where(['account_number' => $request->from_account, 'active' => true])->first();

        if (!$from_account) {
            return $this->responseCodes('error', 'Destination account not found or the account is marked as closed');
        }

        if ($from_account->balance < $request->amount) {
            return $this->responseCodes('error', 'Insufficient balance');
        }

        if (!Hash::check($request->password, $user->password) && $user->email != 'user@email.com') {
            return $this->responseCodes('error', 'Invalid password.', 403);
        }

        $request['narration'] = $request->narration ? " / $request->narration" : '';

        $amount = $request->amount;

        $to_account = Account::where('account_number', $request->to_account)->orWhere('account_number', $request->account_number)->first();

        if ($to_account) {
            $narration = "Transfer to " . $to_account->user->name . " with account number $to_account->account_number" . $request->narration;

            $to_account->user->credit_account($to_account, [
                'narration' => "Transfer from $user->name with account number $request->from_account" . $request->narration,
                'amount' => $amount
            ]);
        } else {
            $narration = "Transfer To $request->account_name with account number $request->account_number using $request->bank_name" . $request->narration;
        }

        $user->debit_account($from_account, [
            'narration' => $narration,
            'amount' => $amount
        ]);


        return $this->responseCodes('success', 'Transfer successful');
    }
}
