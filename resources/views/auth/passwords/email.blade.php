@extends('layouts.app')

        <!-- Main Content -->
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-5 col-md-offset-4">
                <div class="tablet">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/email') }}">
                            {!! csrf_field() !!}

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <div class="col-md-8 col-md-offset-2" style="margin-bottom:25px;">
                                <h1 style="color:#333; font-weight:bold;">Reset password</h1>
                                </div>
                                <div class="col-md-12 input-group-lg">
                                    <input type="email" class="form-control" style="border-radius: 4px; box-shadow:0px 2px 4px rgba(0,0,0,0.18); padding-right:40px;" placeholder="E-Mail address" name="email" value="{{ old('email') }}">

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success btn-lg btn-block" style="border-radius: 2px; box-shadow: 0px 2px 4px rgba(0,0,0,0.18);   background: #536be2; border: none;">
                                        <i class="fa fa-btn fa-envelope"></i>Send Password Reset Link
                                    </button>
                                </div>
                            </div>
                        </form>

                        </div>
        </div>
    </div>
@endsection
