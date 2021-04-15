<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerJob extends Model
{
    protected $table = 'jobseeker_jobs';
    protected $fillable = ['jobseeker_id', 'job_id'];

}
