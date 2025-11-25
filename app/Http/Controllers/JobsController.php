<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;

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

        $JobTypeArray = [];
        // Search using job Type
        if (!empty($request->JobType)) {
            $JobTypeArray = explode(',', $request->JobType);

            $Jobs = $Jobs->whereIn('job_type_id', $JobTypeArray);
        }
        // Search using experience
        if (!empty($request->experience)) {
            $Jobs = $Jobs->where('experience', $request->experience);
        }

        $Jobs = $Jobs->with(['JobType', 'category']);

        if ($request->sort == '0') {
            $Jobs = $Jobs->orderBy('created_at', 'ASC');
        } else {
            $Jobs = $Jobs->orderBy('created_at', 'DESC');
        }

        $Jobs = $Jobs->paginate(9);

        return view('front.jobs', [
            'categories' => $categories,
            'JobTypes' => $JobTypes,
            'Jobs' => $Jobs,
            'JobTypeArray' => $JobTypeArray
        ]);
    }
    // This method will show job detail page
    public function detail($id)
    {

        $Job = Job::where(['id' => $id, 'status' => 1])->with(['jobType', 'category'])->first();

        if ($Job == null) {
            abort(404);
        }
        return view('front.jobdetail', ['Job' => $Job]);
    }
    public function applyJob(Request $request)
    {
        $id = $request->id;

        $Job = Job::where('id', $id)->first();

        // if job not found in db
        if ($Job == null) {
            $message = 'Job does not exist';
            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }

        // you can not apply on your own job
        $employer_id = $Job->user_id;

        if ($employer_id == Auth::user()->id) {
            $message = 'you can not apply on your own job';

            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }

        // You can not apply on a job twice
        $JobApplicationCount = JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id'  => $id
        ])->count();

        if ($JobApplicationCount > 0) {

            $message = 'You already applied on this job';

            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }

        $application =  new JobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id = $employer_id;
        $application->applied_date = now();
        $application->save();

        $message = 'You have successfully applied';

        session()->flash('error', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
