<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserInfo;
use App\Models\User;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $request->password = \Hash::make($request->password);
        $credentials = null;
        if ($request->username) {
            $credentials = $request->only('username', 'password');
        } else {
            $credentials = $request->only('email', 'password');
        }
        try {
            $signIn = Auth::attempt($credentials);
            if ($signIn) {
                $user = Auth::user();
                    return response()->json(
                        [
                            'access_token' => \Str::random(60),
                            'user_id' => $user->id,
                        ],
                    );
            } else {
                return response()->json(
                    ['error' => 'The credentials provided are invalid!'],
                    406
                );
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createUser(Request $request)
    {
        try {
            \DB::beginTransaction();
            $userInfo = UserInfo::create(['firstname' => $request->firstname,'middlename' => $request->middlename,'lastname' => $request->lastname,'address' => $request->address,'country_code' => $request->country_code,'mobile_number' => $request->mobile_number]);
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => \Hash::make($request->password),
            ]);
            if ($userInfo) {
                \DB::commit();
                return response()->json(['success' => true, 'message' => 'Account created successfully!']);
            }
        } catch (\Throwable $th) {
            \DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request, $id)
    {
        try {
            \DB::beginTransaction();


            $user = User::where('id', $id)->first();

            if ($user->password !== $request->current_password) {
                return response()->json(['error' => 'Current password is incorrect!'],400);
            } else if ($request->new_password === $request->confirm_new_password) {
                return response()->json(['error' => 'New password and password confirmation does not match!'],400);
            } else if ($request->new_password === $user->password) {
                return response()->json(['error' => 'New password must be different from current password!'], 400);
            }

            $user->update([
                'password' => \Hash::make($request->new_password)
            ]);
            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
        } catch (\Throwable $th) {
            \DB::rollBack();
            return response()->json(['error' => 'Something went wrong!'], 500);
        }        
    }

    public function updateDetails(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Details updated successfully.']);
        } catch (\Throwable $th) {
            \DB::rollBack();
            return response()->json(['error' =>$th->getMessage()], 500);
        }
    }
}
