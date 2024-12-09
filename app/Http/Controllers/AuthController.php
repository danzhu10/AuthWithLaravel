<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\ResponseFormatter;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email'          => ['required', 'string', 'email', 'max:255'],
                'password'       => ['required', 'string']
            ]);

            $responseData = [
                'meta' => [
                    'code'    => 200,
                    'status'  => false,
                    'message' => 'Validation failed',
                ]
            ];

            if ($validator->fails()) {
                $errors = $validator->errors();

                if ($errors->has('email')) {
                    $responseData['meta']['message'] = 'Email harus diisi';
                } else if ($errors->has('password')) {
                    $responseData['meta']['message'] = 'Password harus diisi';
                }
                return response()->json($responseData, 200);
            }

            // Cek apakah pengguna dengan alamat email ada dalam basis data
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                // Email tidak ditemukan dalam basis data
                return response()->json([
                    'meta' => [
                        'code'    => 200,
                        'status'  => false,
                        'message' => 'Email tidak ditemukan',
                    ],
                ]);
            }

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Email atau Password Anda salah',
                    ],
                ]);
            }



            if (!Hash::check($request->password, $user->password, [])) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Invalid Credentials',
                    ],
                ]);
            }

            if ($user->email_verified_at == null) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Akun Belum Terverifikasi, Silahkan Verifikasi Akun Anda',
                    ],
                ]);
            } else {
                $user->update([
                    'aktif'      => 'true',
                    'updated_by' => Auth::user()->name,
                ]);
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'meta' => [
                        'code'    => 200,
                        'status'  => (bool)'success',
                        'message' => 'Authenticated'
                    ],
                    'access_token' => $tokenResult,
                    'token_type'   => 'Bearer',
                    'data'         => $user
                ]);
            }
        } catch (Exception $error) {
            return response()->json([
                'meta' => [
                    'code' => 200,
                    'status' => false,
                    'message' => 'Terjadi kesalahan, Authentication Failed',
                ],
            ]);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'           => ['required', 'string', 'max:255'],
                'email'          => ['required', 'string', 'email', 'max:255'],
                'password'       => ['required', 'string', Password::min(8)],
                'phone'          => ['string', 'max:20'], //optional
                'address'        => ['string'], //optional
            ]);

            $responseData = [
                'meta' => [
                    'code'    => 422,
                    'status'  => false,
                    'message' => 'Validation failed',
                ]
            ];

            $filter_user_email = User::where('email', $request->email)->count();
            if ($filter_user_email > 0) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Maaf Email sudah terdaftar',
                    ],
                ]);
            } else if ($validator->fails()) {
                $errors = $validator->errors();

                if ($errors->has('email')) {
                    $responseData['meta']['message'] = 'Email harus diisi';
                } else if ($errors->has('password')) {
                    $responseData['meta']['message'] = 'Password harus diisi';
                }
                return response()->json($responseData, 422);
            } else {
                $register = User::create([
                    'name'       => $request->name,
                    'email'      => $request->email,
                    'password'   => Hash::make($request->password),
                    'phone'      => $request->phone,
                    'address'    => $request->address,
                    'created_by' => $request->name,
                ]);

                $user = User::where('email', $request->email)->first();
                event(new Registered($user));
                $tokenResult = $user->createToken('authToken')->plainTextToken;

                //Untuk mengirimkan email verifikasi kepada email yang sudah registrasi
                //Jika field email_verified_at terisi DATETIME maka email sudah aktif dan User sudah dapat login
                $user->sendEmailVerificationNotification();

                return response()->json([
                    'meta' => [
                        'code'    => 200,
                        'status'  => true,
                        'message' => 'User Registered, Verifikasi link terkirim'
                    ],
                    'access_token' => $tokenResult,
                    'token_type'   => 'Bearer',
                    'data'         => $user
                ]);
            }
        } catch (Exception $error) {
            return response()->json([
                'meta' => [
                    'code'    => 200,
                    'status'  => false,
                    'message' => 'Terjadi ada kesalahan, Authentication Failed',
                ],
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user_profile = User::where('id', Auth::user()->id)->first();
        $user_profile->update([
            'updated_by' => Auth::user()->name,
        ]);
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Logout Berhasil');
    }

    public function profile()
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            if ($user->email_verified_at == null) {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'status' => false,
                        'message' => 'Akun Belum Terverifikasi, Silahkan Verifikasi Akun Anda',
                    ],
                ]);
            } else {
                $profile = User::select('id', 'name', 'email', 'phone', 'address')
                    ->where('id', Auth::user()->id)->first();

                return response()->json([
                    'meta' => [
                        'code'       => 200,
                        'status'     => true,
                        'message'    => 'Data Ditemukan'
                    ],
                    'data'       => $profile
                ]);
            }
        } catch (Exception $error) {
            return response()->json([
                'meta' => [
                    'code' => 500, // Internal Server Error status code
                    'status' => false,
                    'message' => 'Terjadi ada kesalahan, Authentication Failed',
                ],
            ], 500);
        }
    }
}
