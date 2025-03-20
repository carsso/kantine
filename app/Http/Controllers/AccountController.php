<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tokens = Auth::user()->tokens()->get();
        return view('account', compact('tokens'));
    }

    public function storeToken(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = Auth::user()->createToken($request->name)->plainTextToken;
        return back()->with('success', 'Token créé avec succès')->with('new_token', $token);
    }

    public function destroyToken(Request $request, $tokenId)
    {
        Auth::user()->tokens()->where('id', $tokenId)->delete();
        return back()->with('success', 'Token révoqué avec succès');
    }
}
