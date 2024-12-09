<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'meta' => [
                    'code'    => 200,
                    'status'  => true,
                    'message' => 'Kami telah mengirimkan email tautan untul mereset ulang kata sandi Anda!'
                ],
            ]);
        }

        $credentials = request(['email']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Maaf Email Anda tidak terdaftar',
                    ],
                ]);
            }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();
                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'meta' => [
                    'code'    => 200,
                    'status'  => true,
                    'message' => 'Password berhasil diubah, Silakan Login dengan password baru',
                ],
            ]);
        }

        $credentials = request(['email','password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Kami tidak dapat menemukan pengguna dengan alamat email tersebut',
                    ],
                ]);
            }else{
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Error, Silakan pergi ke menu lupa kata sandi, jika ingin mengajukan perubahan kata sandi'
                ],]);
            }
    }

}