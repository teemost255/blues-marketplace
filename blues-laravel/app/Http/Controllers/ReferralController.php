<?php
namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function handle(Request $request, string $code)
    {
        $profile = Profile::where('referral_code', strtoupper($code))->first();

        if ($profile) {
            session(['referral_code' => strtoupper($code)]);
        }

        return redirect()->route('register')->with(
            'referral_notice',
            $profile ? 'You were referred! Register to get your welcome bonus.' : null
        );
    }
}
