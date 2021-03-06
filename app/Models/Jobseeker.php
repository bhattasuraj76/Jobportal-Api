<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Jobseeker extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'jobseekers';
    protected $fillable = ['email', 'password', 'first_name',  'last_name', 'gender', 'address', 'phone', 'profile', 'cv', 'description', 'api_key', 'status', 'request_to_activate'];
    protected $hidden = [
        'password'
    ];

    public function jobs()
    {
        return $this->belongsToMany('App\Models\Job', 'jobseeker_jobs');
    }

    public function getRawcvAttribute()
    {
        return $this->attributes['cv'];
    }

    public function getCvAttribute($value)
    {
        $path = $value ? url('public/jobseeker/cv') . "/" . $value : null;
        return $path;
    }

    public function getProfileAttribute($value)
    {
        $path = $value ? url('public/jobseeker/images') . "/" . $value : null;
        return $path;
    }

    public function getNameAttribute($value)
    {
        return $this->attributes['first_name'] . " ". $this->attributes['last_name'];
    }
}
