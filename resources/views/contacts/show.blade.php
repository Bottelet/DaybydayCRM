@extends('layouts.master')

@section('heading')
	<h1>Contact Name</h1>
@endsection

@section('content')

	<div class="container-fluid">
		<div class="row">
			<div class="col-md-9">
				<div class="panel panel-primary">
					<div class="panel-heading">
						{{ __('Contact Information') }}
					</div>
					<div class="panel-body">
						<p>Address<br/>
						City, State ZIP</p>
						<p>Primary Phone<br/>
						Secondary Phone</p>
						<p>Email Address</p>
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="panel panel-success">
					<div class="panel-heading">
						{{ __('Actions') }}
					</div>
					<div class="panel-body">
						Edit Contact<br/>
						Reassing Contact
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">{{ __('Client Information') }}</div>
					<div class="panel-body">
						Client Information
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">{{ __('Salesperson Information') }}</div>
					<div class="panel-body">
						Salesperson Information
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection