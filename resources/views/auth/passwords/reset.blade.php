@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
            <div class="col-md-6 col-md-offset-3" style="margin-bottom:25px;">
                                <h1 style="color:#fff; font-weight:bold;" style="margin-bottom:25px;">Reset password</h1>
                </div>
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
                            {!! csrf_field() !!}

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <div class="col-md-6 col-md-offset-3 input-group-lg">
                                    <input type="email" class="form-control" style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18); padding-right:40px;" placeholder="E-Mail address" name="email"
                                           value="{{ $email ?: old('email') }}">

                    
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <div class="col-md-6 col-md-offset-3 input-group-lg">
                                    <input type="password"class="form-control" style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18); padding-right:40px;" placeholder="Password" name="password">
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <div class="col-md-6 col-md-offset-3 input-group-lg">
                                    <input type="password" class="form-control" style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18); padding-right:40px;" placeholder="Confirm password" name="password_confirmation">

                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="border-radius: 2px; box-shadow: 0px 2px 4px rgba(0,0,0,0.18);   background: linear-gradient(0, hsl(194, 50%, 43%), hsl(208, 60%, 60%));">
                                        <i class="fa fa-btn fa-refresh"></i>Reset Password
                                    </button>
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

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </form>

        </div>
    </div>
@endsection
