@extends('layouts.app')

@section('content')
    <div class="container" >
        <div class="row">
            <div class="col-md-5 col-md-offset-4">
                <div class="tablet">
                 <form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
                            {!! csrf_field() !!}

                            @if(isDemo()) 
                            <div class="alert alert-info">
                                <strong>Demo login info</strong> 
                                <p>Email: demo@daybydaycrm.com</p>
                                <p>Password: Daybydaycrm123</p>
                            </div>
                            @endif
                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <div class="inner-addon right-addon">
                                <div class="col-md-12 input-group-lg">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <input type="email" value="{{isDemo() ? 'demo@daybydaycrm.com' : ''}}" class="form-control" style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18); padding-right:40px; " name="email" value="{{ old('email') }}" placeholder="E-mail address">

                                </div>
                                </div>
                            </div>
                            
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <div class="inner-addon right-addon">
                                <div class="col-md-12 input-group-lg">
                                <i class="fa fa-lock" aria-hidden="true"></i>
                                    <input type="password" class="form-control" value="{{isDemo() ? 'Daybydaycrm123' : ''}}"  style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18);" name="password" placeholder="Password">
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                                <div class="col-md-12">
                                    <div class="checkbox">
                                        <label style="font-weight: 300; color:#333;">
                                            <input type="checkbox" name="remember"> Remember Me
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                
                                    <button type="submit" class="btn btn-success btn-lg btn-block" style="border-radius: 2px; box-shadow: 0px 2px 4px rgba(0,0,0,0.18);   background: #536be2; border: none;">
                                        <i class="fa fa-btn fa-sign-in"></i>Login
                                    </button>
                                </div>
                                <div class="col-md-6 col-md-offset-2" style="padding-left: 60px;">
                                    <a class="btn btn-link" href="{{ url('/password/reset') }}" style="color:#333; margin-top:8px;">Forgot Your
                                        Password?</a>
                                </div>
                            </div>
                            <div class="col-md-12">
                               
                                 @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                 @if ($errors->has('password'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                                @if (Session::has('message'))
                                        <span class="help-block">
                                        <strong>{{ Session::get('message') }}</strong>
                                    </span>
                                @endif
                            </div>
                    </form>
                    </div>
            </div>
        </div>
    </div>
@endsection
