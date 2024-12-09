<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


class VerifyEmailController extends Controller
{

    //Untuk mengirim notifikasi email kepada user yang baru register
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => false,
                    'message' => 'Sudah diverifikasi',
                ],
            ]);
        }

        $request->user()->sendEmailVerificationNotification();
        return response()->json([
            'meta' => [
                'code'    => 200,
                'status'  => (bool)'success',
                'message' => 'Verifikasi link terkirim'
            ],
        ]);
    }

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => false,
                    'message' => 'Email sudah diverifikasi',
                ],
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        return response()->json([
            'meta' => [
                'code'    => 200,
                'status'  => (bool)'success',
                'message' => 'Email telah diverifikasi'
            ],
        ]);
    }
}
