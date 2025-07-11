@extends('layouts.blank')

@section('content')
    <div class="container center-content">
        <div class="card mt-6">
            <div class="card-body">
                <div class="card-header">
                   <div class="row" style="width: 100%">
                       <div class="col-12">
                           <div class="text-center">
                               <h1 class="h3-bg">Basket Software Installation</h1>
                               <h4>Provide information which is required.</h4>
                           </div>
                       </div>
                   </div>
                </div>
                <div class="row mt-4">
                    <div class="col-3"></div>
                    <div class="col-md-6">
                        <ol class="list-group">
                            <li class="list-group-item text-semibold"><i class="fa fa-check"></i> Database Name</li>
                            <li class="list-group-item text-semibold"><i class="fa fa-check"></i> Database Username</li>
                            <li class="list-group-item text-semibold"><i class="fa fa-check"></i> Database Password</li>
                            <li class="list-group-item text-semibold"><i class="fa fa-check"></i> Database Hostname</li>
                        </ol>
                        <p  class="pt-5">
                            We will check permission to write several files,proceed..
                        </p>
                        <br>
                        <div class="text-center">
    <a href="{{ route('step1') }}" class="btn btn-custom">
        Ready? Then start <i class="fa fa-forward"></i>
    </a>
</div>
                    </div>
                    <div class="col-3"></div>
                </div>
            </div>
        </div>

        <link rel="stylesheet" href="{{ asset('css/css.css') }}">
    </div>
@endsection
