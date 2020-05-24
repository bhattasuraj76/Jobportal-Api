<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobseekerController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $jobseeker = Auth::user();
        $data['jobs'] = $jobseeker->jobs()->where('status', 1)->get();
        $data['profile'] = $jobseeker->profile;
        return response()->json(['resp' => 1, 'result' => $data]);
    }

    public function show(Request $request)
    {
        return response()->json(['resp' => 1, 'user' =>  Auth::user()]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required',
            "last_name" => 'required',
            'address' => 'required',
            'profile' => 'max:1000|mimes:jpg,png',
            'cv' => 'max:1000|mimes:pdf',
        ]);

        $jobseeker = Auth::user();
        $attributes = $request->all();

        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = md5(microtime()) . '.' . $file->getClientOriginalExtension();
            $path = base_path() . '/public/images/jobseeker';

            if (!file_exists($path)) {
                mkdir($path, 0777);
            }

            $file->move($path, $filename);
            $attributes['profile'] = $filename;
        }

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $filename = md5(microtime()) . '.' . $file->getClientOriginalExtension();
            $path = base_path() . '/public/images/jobseeker';

            $file->move($path, $filename);
            $attributes['cv'] = $filename;
        }

        if ($jobseeker->update($attributes)) {
            return response()->json(['resp' => 1, 'user' => [
                'profile' => $jobseeker->profile 
            ]]);
        }
        return response()->json(['resp' => 0]);
    }

}
