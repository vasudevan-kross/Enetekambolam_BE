@extends('layouts.blank')
@section('content')
<div class="container center-content">
        <div class="card mt-6">
            <div class="card-body">
                <div class="row pt-5">
                    <div class="col-md-12">
                        <div class="pad-btm text-center">
                            <h1 class="h3-bg">All Done, Great Job.</h1>
                            <p>Your software is ready to run.</p>
                           
                        </div>
                        <!-- <div class="text-center pt-3">
                            <a href="{{ env('APP_URL') }}/admin/auth/login" target="_blank" class="btn btn-info">Admin
                                Panel</a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="{{ asset('css/css.css') }}">
    </div>
@endsection
