<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    public function Job(){
        return $this->belongsTo(Job::class);
    }

    //  public function Job(){
    //     return $this->belongsTo(Job::class);
    // }
    
    public function user(){
        return $this->belongsTo(User::class);

    }
}
