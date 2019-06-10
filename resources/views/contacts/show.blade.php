@extends('layouts.master')

@section('heading')
	<h1>Contact: {{ $contact->name }}</h1>
@endsection

@section('content')

	<div class="container-fluid">
		<div class="row">
			<div class="col-sm-9">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<strong>{{ __('Contact Information') }}</strong>
					</div>
					<div class="panel-body">
						@if ($contact->formatted_address)
							<p>{!! $contact->formatted_address !!}</p>
						@endif
						</p>
						@if ($contact->primary_number)
							<p><strong>Primary #:</strong> <a href="tel:{{ $contact->primary_number }}">{{ $contact->primary_number }}</a></p>
						@endif
						@if ($contact->secondary_number)
							<p><strong>Secondary #:</strong> <a href="tel:{{ $contact->secondary_number }}">{{ $contact->secondary_number }}</a></p>
						@endif
						@if ($contact->email)
							<p><strong>Email: </strong> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
						@endif
					</div>
				</div>
			</div>
			<div class="col-sm-3">
				<div class="panel panel-success">
					<div class="panel-heading">
						<strong>{{ __('Actions') }}</strong>
					</div>
					<div class="panel-body">
						@if (Auth::user()->can('contact-update'))
						<a href="{{ route('contacts.edit', ['contact' => $contact]) }}" class="btn btn-success btn-xs">Edit Contact</a>
						@endif
						@if (Auth::user()->can('contact-delete'))
						<form action="{{ route('clients.destroy', $contact->id) }}" method="POST">
							<input type="hidden" name="_method" value="DELETE">
                <input type="submit" name="submit" value="Delete Contact" class="btn btn-danger btn-xs" onClick="return confirm('Are you sure?')">
                {{ csrf_field() }}
            </form>
						@endif
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<strong>{{ __('Client Information') }}</strong>
					</div>
					<div class="panel-body">
						<p><a href="{{ route('clients.show', ['client' => $contact->client]) }}">{{ $contact->client->name }}</a></p>
						@if ($contact->client->formatted_billing_address)
							<p>
								<strong>Billing Address:</strong><br/>
								{!! $contact->client->formatted_billing_address !!}
							</p>
						@endif
						@if ($contact->client->formatted_shipping_address)
							<p>
								<strong>Shipping Address:</strong><br/>
								{!! $contact->client->formatted_shipping_address !!}
							</p>
						@endif
						</p>
						@if ($contact->client->primary_number)
							<p><strong>Primary #:</strong> <a href="tel:{{ $contact->client->primary_number }}">{{ $contact->client->primary_number }}</a></p>
						@endif
						@if ($contact->client->secondary_number)
							<p><strong>Secondary #:</strong> <a href="tel:{{ $contact->client->secondary_number }}">{{ $contact->client->secondary_number }}</a></p>
						@endif
						@if ($contact->client->primary_email)
							<p><strong>Primary Email:</strong> <a href="mailto:{{ $contact->client->primary_email }}">{{ $contact->client->primary_email }}</a></p>
						@endif
						
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="panel panel-default">
					<div class="panel-heading">
						<strong>{{ __('Salesperson Information') }}</strong>
					</div>
					<div class="panel-body">
						<p><a href="{{ route('users.show', ['user' => $contact->client->user]) }}">{{ $contact->client->user->name }}</a></p>
						@if ($contact->client->user->email)
							<p><a href="mailto:{{ $contact->client->user->email }}">{{ $contact->client->user->email }}</a></p>
						@endif
						@if ($contact->client->user->personal_number)
							<p><a href="tel:{{ $contact->client->user->personal_number }}">{{ $contact->client->user->personal_name }}</a></p>
						@endif
						@if ($contact->client->user->work_number)
							<p><a href="tel:{{ $contact->client->user->work_number }}">{{ $contact->client->user->work_number }}</a></p>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection