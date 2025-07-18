@extends('layouts.blank')
@section('content')
<div class="container center-content">
        <div class="card mt-6">
            <div class="card-body">
                <div class="card-header">
                    <div class="row" style="width: 100%">
                        <div class="col-12">
                            @if(session()->has('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{session('error')}}
                                </div>
                            @endif
                            <div class="mar-ver pad-btm text-center">
                                <h1 class="h3-bg">Purchase Code</h1>
                                <h4>
                                    Provide your codecanyon purchase code.</h4>
                                    <br/>
                                    <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code"
                                       class="text-info">Where to get purchase code?</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row pt-5">
                    <div class="col-3"></div>
                    <div class="col-md-6">
                        <div class="text-muted font-13 form-section">
                            <form class="form-section" method="POST" action="{{ route('purchase.code') }}">
                                @csrf
                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="purchase_code">Codecanyon Username</label>
                                    <input type="text" value="{{env('BUYER_USERNAME')}}" class="form-control input-section"
                                           id="username"
                                           name="username" required>
                                </div>

                                <div class="form-group custom-form-group">
                                    <label class="label-section" for="purchase_code">Purchase Code</label>
                                    <input type="text" value="{{env('PURCHASE_CODE')}}" class="form-control input-section"
                                           id="purchase_key"
                                           name="purchase_key" required>
                                </div>
                                <br/>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-custom">Proceed to Install</button>
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
