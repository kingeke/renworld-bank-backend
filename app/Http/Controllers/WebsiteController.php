<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * Get website defaults
     * 
     * @return Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'banks' => config('website.banks'),
            'account_types' => config('website.account_types'),
        ]);
    }
}
