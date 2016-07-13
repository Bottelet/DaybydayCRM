@extends('installer::layouts.master')

@section('container')
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-folder-close"></i>
                @lang('installer::installer.database.title')
            </h3>
        </div>
        <div class="panel-body">

		@if(!$errors->isEmpty())
		<div class="row">
			<div class="alert alert-error">
				<ul class="alert alert-danger">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		</div>
		@endif
		<form action="{{ route('installer::database') }}" method="post">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<div class="panel-group">
				<div class="panel-body">
					<div class="row">
						<div class="form-group col-md-12">
							<div class="col-md-2"><label>@lang('installer::installer.database.host')</label></div>
							<div class="col-md-10"><input type="text" name="host" value="{{ old('host', 'localhost') }}" class="form-control" placeholder="@lang('installer::installer.database.host')"></div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-12">
							<div class="col-md-2"><label>@lang('installer::installer.database.database')</label></div>
							<div class="col-md-10"><input type="text" name="database" value="{{ old('database') }}" class="form-control" placeholder="@lang('installer::installer.database.database')"></div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-12">
							<div class="col-md-2"><label>@lang('installer::installer.database.username')</label></div>
							<div class="col-md-10"><input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="@lang('installer::installer.database.username')"></div>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-12">
							<div class="col-md-2"><label>@lang('installer::installer.database.password')</label></div>
							<div class="col-md-10"><input type="text" name="password" value="{{ old('password') }}" class="form-control" placeholder="@lang('installer::installer.database.password')"></div>
						</div>
					</div>					
				</div>
			</div>
			<button class="btn btn-success" type="submit">
				@lang('installer::installer.next')
			</button>
			</form>
        </div>
    </div>
@stop