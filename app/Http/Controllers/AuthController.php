<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;

class AuthController extends Controller
{

    public function loginGoogle() : JsonResponse {
        return response()->json([
            'url' => Socialite::driver('google')
                         ->stateless()
                         ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                         ->redirect()
                         ->getTargetUrl(),
        ]);
    }

    public function handleAuthCallback(): JsonResponse
    {
        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver('google')->stateless()->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->user();
        } catch (ClientException $e) {
            return response()->json(['error' => 'Invalid credentials provided.'], 422);
        }

        /** @var User $user */
        $user = User::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteUser->getName(),
                    'google_id' => $socialiteUser->getId(),
                    'password'  => Hash::make('123')
                ]
            );

            $token = $user->createToken('google-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 201);
    }
    public function register(Request $request) {
        // return $request->all();

        $fields = $request->validate([
            'name'  => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password'  => 'required|confirmed',
        ]);
        
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'errors' => [
                    'email' => ['The provided credentials are incorrect.']
                ]
            ];
        }

        $token = $user->createToken('auth_token');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    function logout(Request $request) {
        $request->user()->tokens()->delete();

        return [
            'message'   => 'you are logout',
        ];
    }
}
