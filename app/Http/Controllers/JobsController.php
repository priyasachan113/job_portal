<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;

use Illuminate\Http\Request;

class JobsController extends Controller
{
    // this method will show jobs page
    public function index(){

     $categories = Category::where('status',1)->get();
     $JobTypes = JobType::where('status',1)->get();

     $Jobs = Job::where('status',1)->orderBy('created_at','DESC')->paginate(5);

        return view('front.jobs',[
            'categories' => $categories,
            'JobTypes' => $JobTypes,
            'Jobs' => $Jobs,
        ]);

    }
}
