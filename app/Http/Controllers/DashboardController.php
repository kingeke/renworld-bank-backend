<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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
     * Get users dashboard data
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = $this->user;

        $transactions = $user->transactions()->latest()->with('account')->count();
        $accounts = $user->accounts()->count();
        $balance = $user->accounts()->sum('balance');
        $recent_transactions = $user->transactions()->latest()->with('account')->take(15)->get();

        return response()->json([
            'status' => 'success',
            'transactions' => $transactions,
            'accounts' => $accounts,
            'balance' => $balance,
            'recent_transactions' => $recent_transactions
        ]);
    }
}
