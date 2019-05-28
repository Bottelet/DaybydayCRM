@extends('layouts.master')

@section('heading')
	<h1>Contact: ({{ $contact->name }})</h1>
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
						@if ($contact->html_formatted_address)
							<p>{!! $contact->html_formatted_address !!}</p>
						@endif
						</p>
						@if ($contact->primary_number)
							<p><a href="tel:{{ $contact->primary_number }}">{{ $contact->primary_number }}</a></p>
						@endif
						@if ($contact->secondary_number)
							<p><a href="tel:{{ $contact->secondary_number }}">{{ $contact->secondary_number }}</a></p>
						@endif
						@if ($contact->email)
							<p><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
						@endif
					</div>
				</div>
			</div>
			<div class="col-md-3">
				<div class="panel panel-success">
					<div class="panel-heading">
						{{ __('Actions') }}
					</div>
					<div class="panel-body">
						<a href="{{ route('contacts.edit', ['contact' => $contact]) }}">Edit Contact</a
>					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">{{ __('Client Information') }}</div>
					<div class="panel-body">
						{{ $contact->client->company_name }}
						@if ($contact->client->html_formatted_address)
							<p>{!! $contact->client->html_formatted_address !!}</p>
						@endif
						</p>
						@if ($contact->client->primary_number)
							<p><a href="tel:{{ $contact->client->primary_number }}">{{ $contact->client->primary_number }}</a></p>
						@endif
						@if ($contact->client->secondary_number)
							<p><a href="tel:{{ $contact->client->secondary_number }}">{{ $contact->client->secondary_number }}</a></p>
						@endif
						@if ($contact->client->email)
							<p><a href="mailto:{{ $contact->client->email }}">{{ $contact->client->email }}</a></p>
						@endif
						
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