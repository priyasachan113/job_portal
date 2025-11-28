<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;

class JobsController extends Controller
{
    // this method will show jobs page
    public function index(Request $request){

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
    public function detail($id){

        $Job = Job::where(['id' => $id, 'status' => 1])->with(['jobType', 'category'])->first();

        if ($Job == null) {
            abort(404);
        }
        $count = 0;
        if (Auth::user()) {
            $count = SavedJob::where([
                'user_id' => Auth::user()->id,
                'Job_id' => $id
            ])->count();
        }
        // fetch applicants

        $applications = JobApplication::where('job_id', $id)->with('user')->get();

        // dd($applications);

        return view('front.jobdetail', ['Job' => $Job, 
                                        'count' => $count, 
                                        'applications' => $applications]);
    }

    public function applyJob(Request $request){
        $id = $request->id;
        // dd($id);

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

        //Send Notification Email to Employer
        $employer = User::where('id', $employer_id)->first();

        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'Job' => $Job,
        ];
        // dd($mailData, $employer);
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        $message = 'You have successfully applied';

        session()->flash('success', $message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
    public function saveJob(Request $request){
        $id = $request->id;

        $Job = Job::find($id);

        if ($Job == null) {
            session()->flash('error', 'Job not found');
            return response()->json([
                'status' => false,
            ]);
        }

        // Check if user already saved the Job
        $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'Job_id' => $id
        ])->count();

        if ($count > 0) {
            session()->flash('error', 'You already saved this job');
            return response()->json([
                'status' => false,
            ]);
        }
        $SavedJob = new SavedJob;
        $SavedJob->job_id = $id;
        $SavedJob->user_id = Auth::user()->id;
        $SavedJob->save();

        session()->flash('success', 'You have successfully saved the job');
        return response()->json([
            'status' => true,
        ]);
    }
}
