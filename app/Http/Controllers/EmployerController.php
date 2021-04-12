<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Employer;
use App\Models\Job;
use App\Models\Jobseeker;
use Auth;
use Illuminate\Support\Facades\DB;

class EmployerController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $employer = Auth::user();
        $employerId = $employer->id;
        $jobApplicantsCount = Jobseeker::whereHas('jobs', function ($query) use ($employerId) {
            return $query->where([['status', true], ['employer_id', $employerId]]);
        })->count();

        $data['total_applicants'] = $jobApplicantsCount;
        $data['total_jobs_posted'] = $employer->jobs()->where('status', true)->count();
        $data['logo'] =  $employer->logo;
        $data['cover'] =  $employer->cover;
        return response()->json(['resp' => 1, 'result' => $data]);
    }

    public function show(Request $request)
    {
        return response()->json(['resp' => 1, 'user' =>  Auth::user()]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'logo' => 'max:1000|mimes:jpg,png',
            'cover' => 'max:1000|mimes:jpg,png'
        ]);

        $attributes = $request->all();

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = md5(microtime()) . '.' . $file->getClientOriginalExtension();
            $path = base_path() . '/public/images/employer';

            if (!file_exists($path)) {
                mkdir($path, 0777);
            }

            $file->move($path, $filename);
            $attributes['logo'] = $filename;
        }

        if ($request->hasFile('cover')) {
            $file = $request->file('cover');
            $filename = md5(microtime()) . '.' . $file->getClientOriginalExtension();

            $file->move($path, $filename);
            $attributes['cover'] = $filename;
        }

        $employer = Auth::user();
        if ($employer->update($attributes)) {
            return response()->json(['resp' => 1, 'user' => [
                'email' => $employer->email,
                'entity' => 'employer',
                'token' => $employer->api_key,
                'logo' => $employer->logo,
                'cover' => $employer->cover
            ]]);
        }
        return response()->json(['resp' => 0]);
    }

    public function viewPostedJobs(Request $request)
    {
        $employer = Auth::user();
        $jobs = $employer->jobs()->where('status', true)->get();
        return response()->json(['resp' => 1, 'jobs' => $jobs]);
    }

    public function viewJobApplicants(Request $request)
    {
        $employer = Auth::user();
        $employerId = $employer->id;
        $jobApplicants = Jobseeker::with('jobs')->whereHas('jobs', function ($query) use ($employerId) {
            return $query->where([['status', 1], ['employer_id', $employerId]]);
        })->get();
        return response()->json(['resp' => 1, 'jobseekers' => $jobApplicants]);
    }

    public function createJob(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'expiry_date' => 'required|date',
            'category' => 'required'
        ]);

        $attributes = $request->all();

        $slug = Str::slug($request->title);
        if (Job::where('slug', $slug)->exists()) {
            $count = Job::where('title', $request->title)->count();
            $attributes['slug'] = $slug . '-' . $count;
        } else {
            $attributes['slug'] = $slug;
        }

        $employer = Auth::user();
        if ($employer->jobs()->create($attributes)) {
            return response()->json(['resp' => 1]);
        } else {
            return response()->json(['resp' => 0]);
        }
    }

    public function destroyJob($id)
    {
        $job = Job::find($id);
        if ($job->update(["status" => false])) {
            return response()->json(['resp' => 1]);
        } else {
            return response()->json(['resp' => 0]);
        }
    }
}
