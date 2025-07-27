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
            ->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

           // dd(Socialite::driver('google')->user());
            // Check if the user already exists
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                //update the user
                $user->update([
                    'name'              => $googleUser->name,
                    'email'             => $googleUser->email,
                    'email_verified_at' => now(),
                    'isActive'          => true,
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name'              => $googleUser->name,
                    'email'             => $googleUser->email,
                    'google_id'         => $googleUser->id,
                    'avatar'            => $googleUser->avatar,
                    'password'          => bcrypt(Str::random(12)),
                    'email_verified_at' => now(),
                    'role'              => 'Instructor',
                    'isActive'          => true,
                ]);
            }

            $token = $user->createToken('main')->plainTextToken;

            return redirect()->to("http://localhost:5173/google-auth-success?tokenID={$token}&id={$user->id}&name={$user->name}&email={$user->email}");
        } catch (\Exception $e) {
            return redirect()->to("http://localhost:5173/login?error=google_auth_failed");
        }
    }
}
