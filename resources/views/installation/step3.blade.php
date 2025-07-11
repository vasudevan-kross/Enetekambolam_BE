@extends('layouts.blank')
@section('content')
<div class="container center-content">
        <div class="card mt-6">
            <div class="card-body">
                <div class="card-header">
                    <div class="row" style="width: 100%">
                        <div class="col-12">
                            <div class="mar-ver pad-btm text-center">
                                <h1 class="h3-bg">Configure Database</h1>
                                <h4>Provide database information correctly.</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row pt-5">
                    <div class="col-3"></div>
                    <div class="col-md-6">
                        @if (session()->has('error'))
                            <div class="row" style="margin-top: 20px;">
                                <div class="col-md-12">
                                    <div class="alert alert-danger">
                                        <strong>Invalid Database Credentials!! </strong>Please check your database
                                        credentials
                                        carefully
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="text-muted font-13">
                            <form class="form-section" method="POST" action="{{ route('install.db') }}">
                                @csrf
                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="db_host">Database Host</label>
                                    <input type="text" class="form-control input-section" id="db_host" name="DB_HOST" required
                                           autocomplete="off" placeholder="Ex: localhost">
                                    <input type="hidden" name="types[]" value="DB_HOST">
                                </div>
                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="db_name">Database Name</label>
                                    <input type="text" class="form-control input-section" id="db_name" name="DB_DATABASE" required
                                           autocomplete="off" placeholder="Ex: food_database">
                                    <input type="hidden" name="types[]" value="DB_DATABASE">
                                </div>
                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="db_user">Database Username</label>
                                    <input type="text" class="form-control input-section" id="db_user" name="DB_USERNAME" required
                                           autocomplete="off" placeholder="Ex: root">
                                    <input type="hidden" name="types[]" value="DB_USERNAME">
                                </div>
                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="db_pass">Database Password</label>
                                    <input type="password" class="form-control input-section" id="db_pass" name="DB_PASSWORD"
                                           autocomplete="off" placeholder="Ex: password">
                                    <input type="hidden" name="types[]" value="DB_PASSWORD">
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-custom">Continue</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-3"></div>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="{{ asset('css/css.css') }}">

    </div>
@endsection
