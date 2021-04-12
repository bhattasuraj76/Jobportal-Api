<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use Auth;

class PageController extends Controller

{
    public function __construct(Job $job)
    {
        $this->model = $job;
        $this->paginate = 5;
    }

    public function home(Request $request)
    {
        $jobs = Job::with('employer')->where('status', 1)->whereDate('expiry_date', '>', date('Y-m-d'))->orderBy('id', 'desc')->limit(9)->get();
        $jobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });
        return response()->json(['resp' => 1, 'hot_jobs' => $jobs]);
    }

    public function mobileAppHome(Request $request)
    {
        $jobs = Job::with('employer')->where('status', 1)->whereDate('expiry_date', '>', date('Y-m-d'))->orderBy('id', 'desc')->get();
        $jobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });
        return response()->json(['resp' => 1, 'jobs' => $jobs]);
    }

    public function viewJob(Request $request, $slug)
    {
        $job = Job::with('employer')->where('slug', $slug)->where('status', 1)->first();
        $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
        if (empty($job)) {
            return respose()->json(['resp' => 0, 'message' => "No job found"]);
        }
        return response()->json(['resp' => 1, 'job' => $job]);
    }

    public function applyForJob(Request $request)
    {
        $job = Job::find($request->job_id);
        if (empty($job)) {
            return response()->json(['resp' => 0, 'message' => "No job found"]);
        }

        if (Auth::user()->jobs()->where('job_id', $job->id)->exists()) {
            return response()->json(['resp' => 0, 'message' => "You have already applied to this job."]);
        }

        Auth::user()->jobs()->sync($job, false);
        return response()->json(['resp' => 1]);
    }


    public function filterJobs(Request $request)
    {
        $query = Job::with('employer')->where('status', 1)->whereDate('expiry_date', '>', date('Y-m-d'));

        if ($request->has('keyword') && !empty(trim($request->keyword))) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->has('category') && !empty($request->category)) {
            $query->whereIn('category', $request->category);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->whereIn('type', $request->type);
        }

        if ($request->has('level') && !empty($request->level)) {
            $query->whereIn('level', $request->level);
        }

        if ($request->has('location') && !empty($request->location)) {
            $query->whereIn('location', $request->location);
        }

        $query->orderBy('id', 'desc');
       
        //paginate only for web not for mobile
        $jobs = $request->is('*/mobile-search') ? $query->get() : $query->paginate($this->paginate) ;

        $jobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });

        return response()->json(['resp' => 1, 'jobs' => $jobs]);
    }

    public function getDeadlineAttribute($value)
    {
        $date = new \DateTime($value);
        $now = new \DateTime();

        if ($now >= $date) {
            return null;
        }
        return $date->diff($now)->format(" %m months %d days from now");
    }

    public function getExpiryDayAttribute($value)
    {
        $expirydate = strtotime($value);
        $d = strtotime("today");

        if ($d >= $expirydate) {
            return null;
        }
        return ($expirydate - $d) / 60 / 60 / 24;
    }
}
