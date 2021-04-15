<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\JobseekerJob;
use Auth;
use Illuminate\Database\Eloquent\Builder;

class PageController extends Controller

{
    public function __construct(Job $job)
    {
        $this->model = $job;
        $this->paginate = 8;
    }

    public function home(Request $request)
    {
        $recentJobs = Job::with('employer')
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->where('status', 1)
            ->whereDate('expiry_date', '>', date('Y-m-d'))
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();
        $recentJobs->map(function ($job) {
            $job->posted_time = $this->getPostedTimeAttribute($job->created_at);
            return $job;
        });

        $hotJobs = Job::with('employer')->withCount('jobseekers')
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->where('status', 1)
            ->whereDate('expiry_date', '>', date('Y-m-d'))
            ->orderBy('jobseekers_count', 'desc')
            ->limit(6)
            ->get();
        $filteredHotJobs = $hotJobs->filter(function ($job) {
            return $job->jobseekers_count >= 1;
        });
        $filteredHotJobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });

        $expiringJobs = Job::with('employer')->where('status', 1)
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->whereDate('expiry_date', '>', date('Y-m-d'))
            ->orderBy('expiry_date', 'asc')
            ->limit(6)
            ->get();
        $expiringJobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });
        return response()->json(['resp' => 1, 'hot_jobs' => $filteredHotJobs, 'recent_jobs' => $recentJobs, 'expiring_jobs' => $expiringJobs]);
    }

    public function mobileAppHome(Request $request)
    {
        $jobs = Job::with('employer')
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->where('status', 1)
            ->whereDate('expiry_date', '>', date('Y-m-d'))
            ->orderBy('id', 'desc')
            ->get();
        $jobs->map(function ($job) {
            $job->deadline = $this->getDeadlineAttribute($job->expiry_date);
            return $job;
        });
        return response()->json(['resp' => 1, 'jobs' => $jobs]);
    }

    public function viewJob(Request $request, $slug)
    {
        $job = Job::with('employer')
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();
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
        $query = Job::with('employer')
            ->whereHas('employer', function (Builder $query) {
                $query->where('status', 'active');
            })
            ->where('status', 1)
            ->whereDate('expiry_date', '>', date('Y-m-d'));

        // if ($request->has('keyword') && !empty($request->keyword)) {
        //     $query->where('title', 'like', '%' . trim($request->keyword) . '%');
        // }

        if ($request->has('keyword') && !empty($request->keyword)) {
            $wordsArr = explode(" ", trim($request->keyword));

            if(count($wordsArr) > 0){
                $query->where(function(Builder $query) use ($wordsArr) {
                    for ($i = 0; $i < count($wordsArr); $i++) {
                        if($i== 0){
                            $query->where('title', 'like', '%' . $wordsArr[$i] . '%');
                        }else{
                            $query->orWhere('title', 'like', '%' . $wordsArr[$i] . '%');
                        }
                    }
                });
            } 

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

        if ($request->has('salary') && !empty($request->salary)) {
            $salary = $request->salary;
            switch ($salary) {
                case '0-30000':
                    $query->where('salary', '>', 0)->where('salary', '<=', 30000);
                    break;
                case '30000-50000':
                    $query->where('salary', '>', 30000)->where('salary', '<=', 50000);
                    break;
                case '50000-70000':
                    $query->where('salary', '>', 50000)->where('salary', '<=', 70000);
                    break;
                case '70000-':
                    $query->where('salary', '>', 70000);
                    break;
            }
        }

        $query->orderBy('id', 'desc');

        //paginate only for web not for mobile
        $jobs = $request->is('*/mobile-search') ? $query->get() : $query->paginate($this->paginate);

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
        return $date->diff($now)->format("%d days from now");
        // return $date->diff($now)->format(" %m months %d days from now");
    }

    public function getPostedTimeAttribute($value)
    {
        $date = new \DateTime($value);
        $now = new \DateTime();

        // if ($now >= $date) {
        //     return null;
        // }

        if ($date->diff($now)->format("%d") == 0) {
            return $date->diff($now)->format("%i minutes ago");
        }
        return $date->diff($now)->format("%d days ago");
        // return $date->diff($now)->format(" %m months %d days from now");
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
