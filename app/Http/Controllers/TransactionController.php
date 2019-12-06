<?php

namespace App\Http\Controllers;

use App\Http\Traits\CustomTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
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
     * View all transactions 
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @param \App\Account $account_number
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $transactions = $this->user->transactions()->with('account');

        //transaction filters
        if ($request->filters) {

            $filters = json_decode($request->filters, true);

            if ($filters['type']) {
                $transactions->where('type', ucfirst($filters['type']));
            }

            if ($filters['account_number']) {
                $account = $this->user->accounts()->where('account_number', $filters['account_number'])->first();

                $transactions = $transactions->where('account_id', $account->id);
            }
        }

        return response()->json([
            'status' => 'success',
            'transactions' => $transactions->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(20)
        ]);
    }

    /**
     * View a transaction
     * 
     * @param \App\Account $account_number
     * 
     * @param \App\Transaction $transaction_ref
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function show($transaction_ref)
    {
        $transaction = $this->user->transactions()->where('transaction_ref', $transaction_ref)->with('account')->first();

        if (!$transaction) {
            return $this->notFound('Transaction');
        }

        return response()->json([
            'status' => 'success',
            'transaction' => $transaction
        ]);
    }
}
