<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Employer extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'employers';
    protected $fillable = ['email', 'password', 'name',  'address', 'logo', 'cover', 'description', 'api_key'];
    protected $hidden = [
        'password'
    ];
   
    public function jobs()
    {
        return $this->hasMany('App\Models\Job', 'employer_id');
    }

    public function getLogoAttribute($value)
    {
        $path = $value ? url('public/images/employer') . "/" . $value : null;
        return $path;
    }

    public function getCoverAttribute($value)
    {
        $path = $value ? url('public/images/employer') . "/" . $value : null;
        return $path;
    }
}
