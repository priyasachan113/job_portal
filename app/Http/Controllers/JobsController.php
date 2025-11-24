<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;

use Illuminate\Http\Request;

class JobsController extends Controller
{
    // this method will show jobs page
    public function index(Request $request)
    {
        $categories = Category::where('status', 1)->get();
        $JobTypes = JobType::where('status', 1)->get();

        $Jobs = Job::where('status', 1);

        //search using keyword
        if (!empty($request->keyword)) {
            $Jobs = $Jobs->where(function ($query) use ($request) {
                $query->orWhere('title', 'like', '%' . $request->keyword . '%');
                $query->orWhere('keywords', 'like', '%' . $request->keyword . '%');
            });
        }

        // Search using location
        if (!empty($request->location)) {
            $Jobs = $Jobs->where('location', $request->location);
        }

        // Search using category
        if (!empty($request->category)) {
            $Jobs = $Jobs->where('category_id', $request->category);
        }

        // Search using job Type
        if (!empty($request->JobType)) {

            $jobTypeArray = explode(',', $request->JobType);

            $Jobs = $Jobs->whereIn('job_type_id', $jobTypeArray);
        }
        // Search using experience
        if (!empty($request->experience)) {
            $Jobs = $Jobs->where('experience', $request->experience);
        }

        $Jobs = $Jobs->with(['JobType', 'category'])->orderBy('created_at', 'DESC')->paginate(9);

        return view('front.jobs', [
            'categories' => $categories,
            'JobTypes' => $JobTypes,
            'Jobs' => $Jobs,
        ]);
    }
}
