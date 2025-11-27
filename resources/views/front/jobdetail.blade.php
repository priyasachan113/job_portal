@extends('front.layouts.app')

@section('main')
    <section class="section-4 bg-2">
        <div class="container pt-5">
            <div class="row">
                <div class="col">
                    <nav aria-label="breadcrumb" class=" rounded-3 p-3">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('jobs') }}"><i class="fa fa-arrow-left"
                                        aria-hidden="true"></i>&nbsp;Back to Jobs</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="container job_details_area">
            <div class="row pb-5">
                <div class="col-md-8">
                    @include('front.message')
                    <div class="card shadow border-0">
                        <div class="job_details_header">
                            <div class="single_jobs white-bg d-flex justify-content-between">
                                <div class="jobs_left d-flex align-items-center">

                                    <div class="jobs_conetent">
                                        <a href="#">
                                            <h4>{{ $Job->title }}</h4>
                                        </a>
                                        <div class="links_locat d-flex align-items-center">
                                            <div class="location">
                                                <p> <i class="fa fa-map-marker"></i>{{ $Job->location }}</p>
                                            </div>
                                            <div class="location">
                                                <p> <i class="fa fa-clock-o"></i>{{ $Job->jobType->name }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="jobs_right">
                                    <div class="apply_now">
                                        <a class="heart_mark" href="javascript:void(0);" onclick="saveJob({{ $Job->id }})"> <i class="fa fa-heart-o" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="descript_wrap white-bg">
                            <div class="single_wrap">
                                <h4>Job description</h4>
                                {{ $Job->description }}
                            </div>
                            <div class="single_wrap">

                                @if (!empty($Job->responsibility))
                                    <h4>Responsibility</h4>
                                    {{ $Job->responsibility }}
                                @endif

                            </div>
                            <div class="single_wrap">
                                @if (!empty($Job->qualifications))
                                    <h4>Qualifications</h4>
                                    {{ $Job->qualifications }}
                                @endif

                            </div>
                            <div class="single_wrap">

                                @if (!empty($Job->benefits))
                                    <h4>Benefits</h4>
                                    {{ $Job->benefits }}
                                @endif
                            </div>
                            <div class="border-bottom"></div>
                            <div class="pt-3 text-end">


                                @if (Auth::check())
                                    <a href="#" onclick= "saveJob({{ $Job->id }})" class="btn btn-secondary">Save</a>
                                @else
                                    <a href="javascript:void(0);" class="btn btn-secondary disabled">Login to Save</a>
                                @endif

                                @if (Auth::check())
                                    <a onclick="applyJob({{ $Job->id }})"class="btn btn-primary">Apply</a>
                                @else
                                    <a href="javascript:void(0);" class="btn btn-primary disabled">Login to Apply</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow border-0">
                        <div class="job_sumary">
                            <div class="summery_header pb-1 pt-4">
                                <h3>Job Summery</h3>
                            </div>
                            <div class="job_content pt-3">
                                <ul>
                                    <li>Published on:
                                        <span>{{ \Carbon\Carbon::parse($Job->created_at)->format('d M, Y') }}</span>
                                    </li>
                                    <li>Vacancy: <span>{{ $Job->vacancy }}</span></li>
                                    @if (!empty($Job->salary))
                                        <li>Salary: <span>{{ $Job->salary }}</span></li>
                                    @endif
                                    <li>Location: <span>{{ $Job->location }}</span></li>
                                    <li>Job Nature: <span>{{ $Job->jobType->name }}</span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow border-0 my-4">
                        <div class="job_sumary">
                            <div class="summery_header pb-1 pt-4">
                                <h3>Company Details</h3>
                            </div>
                            <div class="job_content pt-3">
                                <ul>
                                    <li>Name: <span>{{ $Job->company_name }}</span></li>

                                    @if (!empty($company_location))
                                        <li>Locaion: <span>{{ $Job->company_location }}</span></li>
                                    @endif

                                    @if (!empty($company_website))
                                        <li>Webite: <span><a
                                                    href="{{ $Job->company_website }}">{{ $Job->company_website }}</a></span>
                                    @endif

                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJs')
    <script type="text/javascript">
        function applyJob(id) {
            if (confirm("Are you sure you want to apply on this job?")) {
                $.ajax({
                    url: '{{ route("applyJob") }}',
                    type: 'post',
                    data: {id:id},
                    dataType: 'json',
                    success: function(response) {
                        // console.log(response);
                        window.location.href = "{{ url()->current() }}";
                    }
                })
            }
        }

        function saveJob(id) {
            $.ajax({
                url: '{{ route("saveJob") }}',
                type: 'post',
                data: {id:id},
                dataType: 'json',
                success: function(response) {
                    // console.log(response);
                    window.location.href = "{{ url()->current() }}";
                }
            })
        }
    </script>
@endsection
