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
    const USER_ENTITIES = ['jobseeker', 'employer'];

    public function __construct()
    {
        // $this->middleware('auth')->only('changePassword');
    }

    public function authenticate(Request $request)
    {
        //return if request no entity or not one of defined entities;
        if (!$this->checkRequestHasUserEntity($request)) return response()->json(['resp' => 0, 'message' => 'Failed to login']);

        //validate input
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        try {
            $user = $request->entity == "jobseeker" ?
                $user = Jobseeker::where('email', $request->input('email'))->first() : Employer::where('email', $request->input('email'))->first();

            //validate user credentials
            if ($user && $this->checkPasswordMatch($request->input('password'), $user)) {
                //generate unique api key an update record
                $apikey = base64_encode(Str::random(40));
                $user->update(['api_key' => $apikey]);

                //return token and user 
                $authUser = [
                    'email' => $user->email,
                    'entity' => $request->entity,
                    'token' => $apikey,
                    // "name" => $user->name,
                    // "profile" => $user->profile ? $user->profile : ""
                ];
                return response()->json(['resp' => 1, 'user' => $authUser]);
            }

            throw new \Exception('Invalid email or password');
        } catch (\Exception $e) {
            return response()->json(['resp' => 0, 'message' => 'Invalid email or password'], 401);
        }
    }


    public function register(Request $request)
    {
        //return if request no entity or not one of defined entities;
        if (!$this->checkRequestHasUserEntity($request)) return response()->json(['resp' => 0, 'message' => 'Failed to register']);

        //validate input
        $table = $request->input('entity') == "employer" ? 'employers' : 'jobseekers';
        $this->validate($request, [
            'email' => 'required|unique:' . $table,
            'password' => 'required',
            'password_confirmation' => 'required|same:password'
        ]);

        try {
            $attributes = [
                'first_name' => $request->has('first_name') ? $request->first_name : "",
                'last_name'  => $request->has('last_name') ? $request->last_name : "",
                'name'     =>  $request->has('name') ? $request->name : "",
                'email'    =>  $request->email,
                'password' => Hash::make($request->password)
            ];

            if ($request->entity == "jobseeker")
                Jobseeker::create($attributes);
            else
                Employer::create($attributes);

            return response()->json(['resp' => 1, 'message' => 'User registered successfully']);
        } catch (\Exception $e) {
            return response()->json(['resp' => 0, 'message' => 'Failed to register user']);
        }
    }


    public function changePassword(Request $request)
    {
        //validate input
        $this->validate($request, [
            'old_password'     => 'required',
            'password'     => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        //validate user password
        if (!$this->checkPasswordMatch($request->input('old_password'), Auth::user())) {
            return response()->json(['old_password' => 'Old password did not match'], 422);
        }

        try {
            Auth::user()->update(['password' => Hash::make($request->password)]);
            return response()->json(['resp' => 1, 'message' => 'Successfully changed user password']);
        } catch (\Exception $e) {
            return response()->json(['resp' => 0, 'message' => 'Failed to change user password']);
        }
    }

    public function logout(Request $request)
    {
        Auth::user()->update(['api_key' => null]);
        return response()->json(['resp' => 1]);
    }

    public function checkPasswordMatch($password, $user)
    {
        return Hash::check($password, $user->password);
    }

    public function checkRequestHasUserEntity($request)
    {
        return $request->has('entity') && in_array($request->entity, self::USER_ENTITIES);
    }
}
