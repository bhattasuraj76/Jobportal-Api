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

class AdminController extends Controller

{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $jobApplicantsCount = Jobseeker::get()->count();
        $employersCount = Employer::get()->count();
        $data['total_jobseekers'] = $jobApplicantsCount;
        $data['total_employers'] = $employersCount;
        return response()->json(['resp' => 1, 'result' => $data]);
    }

    public function show(Request $request)
    {
        return response()->json(['resp' => 1, 'user' =>  Auth::user()]);
    }

    public function viewJobApplicants(Request $request)
    {
        $jobseekers = Jobseeker::get();
        return response()->json(['resp' => 1, 'jobseekers' => $jobseekers]);
    }

    public function viewEmployers(Request $request)
    {
        $employers = Employer::get();
        return response()->json(['resp' => 1, 'employers' => $employers]);
    }

    public function changeJobApplicantStatus(Request $request, $jobApplicantId)
    {
        $jobseeker = Jobseeker::where('id', $jobApplicantId)->first();
        if ($jobseeker->status == "active") {
            $jobseeker->update(['status' => 'suspended', 'request_to_activate' => false]);
        } else {
            $jobseeker->update(['status' => 'active', 'request_to_activate' => false]);
        }

        return response()->json(['resp' => 1, 'status' => $jobseeker->status]);
    }

    public function changeEmployerStatus(Request $request, $employerId)
    {
        $employer = Employer::where('id', $employerId)->first();
        if ($employer->status == "active") {
            $employer->update(['status' => 'suspended', 'request_to_activate' => false]);
        } else {
            $employer->update(['status' => 'active', 'request_to_activate' => false]);
        }

        return response()->json(['resp' => 1, 'status' => $employer->status]);
    }
}
