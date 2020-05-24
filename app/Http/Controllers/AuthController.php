<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Employer;
use App\Models\Jobseeker;
use Auth;

class AuthController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth')->only(['changePassword']);
    }

    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
        // return $request->all();
        if ($request->entity == "jobseeker") {
            $user = Jobseeker::where('email', $request->input('email'))->first();
            if (empty($user)) {
                return response()->json(['resp' => 0, 'message' => 'Invalid email or password'], 401);
            }

            if (Hash::check($request->input('password'), $user->password)) {
                //generate unique api key an update record
                $apikey = base64_encode(Str::random(40));
                Jobseeker::where('email', $request->input('email'))->update(['api_key' => $apikey]);
                //return token and user 
                return response()->json(['resp' => 1, 'user' => ['email' => $user->email, 'entity' => "jobseeker", 'token' => $apikey]]);
            } else {
                return response()->json(['resp' => 0, 'message' => 'Invalid email or password'], 401);
            }
        }

        if ($request->entity ==  "employer") {
            $user = Employer::where('email', $request->input('email'))->first();
            if (empty($user)) {
                return response()->json(['resp' => 0, 'message' => 'Invalid email or password'], 401);
            }

            if (Hash::check($request->input('password'), $user->password)) {
                //generate unique api key an update record
                $apikey = base64_encode(Str::random(40));
                Employer::where('email', $request->input('email'))->update(['api_key' => $apikey]);
                //return token and user 
                return response()->json(['resp' => 1, 'user' => ['email' => $user->email, 'entity' => "employer", 'token' => $apikey]]);
            } else {
                return response()->json(['resp' => 0, 'message' => 'Invalid email or password'], 401);
            }
        }
    }


    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|unique:employers',
            'password' => 'required|same:password',
            'password_confirmation' => 'same:password'
        ]);

        $attributes = [
            'first_name'     => $request->first_name,
            'last_name'     => $request->last_name,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ];

        // return $request->all();
        if ($request->entity == "jobseeker") {
            if (Jobseeker::create($attributes)) {
                return response()->json(['resp' => 1]);
            } else {
                return response()->json(['resp' => 0]);
            }
        }

        if ($request->entity ==  "employer") {
            if (Employer::create($attributes)) {
                return response()->json(['resp' => 1]);
            } else {
                return response()->json(['resp' => 0]);
            }
        }
    }


    public function logout(Request $request)
    {
        Auth::user()->update(['api_key' => null]);
        return response()->json(['resp' => 1]);
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'old_password'     => 'required',
            'password'     => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return response()->json(['old_password' => 'Old password did not match'], 422);
        } else {
            Auth::user()->update(['password' => Hash::make($request->password)]);
            return response()->json(['resp' => 1]);
        }
        
        return response()->json(['resp' => 0]);
    }
}
