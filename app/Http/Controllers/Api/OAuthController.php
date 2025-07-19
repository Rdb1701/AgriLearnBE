<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class OAuthController extends Controller
{

    public function redirect()
    {
        return Socialite::driver("google")
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {

            $googleUser = Socialite::driver('google')->stateless()->user();


            $user = User::updateOrCreate(
                ['google_id' => $googleUser->id],
                [
                    'name'              => $googleUser->name,
                    'email'             => $googleUser->email,
                    'google_id'         => $googleUser->id,
                    'password'          => bcrypt(Str::random(12)),
                    'email_verified_at' => now(),
                    'role'              => 'Student',
                    'isActive'          => true,
                ]
            ); 


            $token = $user->createToken('main')->plainTextToken;


            return redirect()->to("http://localhost:5173/google-auth-success?tokenID={$token}&name={$user->name}&name={$user->name}&email={$user->email}");
        } catch (\Exception $e) {

            return redirect()->to("http://localhost:5173/login?error=google_auth_failed");
        }
    }
}
