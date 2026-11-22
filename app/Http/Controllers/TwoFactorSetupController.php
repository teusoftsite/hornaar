<?php

namespace App\Http\Controllers;

class TwoFactorSetupController extends Controller
{
    public function show()
    {
        return view('auth.two-factor-setup', ['user' => auth()->user()]);
    }
}
