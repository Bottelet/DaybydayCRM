@extends('layouts.master')
@section('heading')
    <h1>{{ __('Create Contact') }}</h1>
@stop

@section('content')

    {!! Form::open(['route' => 'contacts.store']) !!}

    <div class="form-group">
        {!! Form::label('name', __('Name'), ['class' => 'control-label']) !!}
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('job_title', __('Job Title'), ['class' => 'control-label']) !!}
        {!! Form::text('job_title', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('address', __('Address'), ['class' => 'control-label']) !!}
        {!! Form::text('address', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('city', __('City'), ['class' => 'control-label']) !!}
        {!! Form::text('city', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('zipcode', __('Zip Code'), ['class' => 'control-label']) !!}
        {!! Form::text('zipcode', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('email', __('Email'), ['class' => 'control-label']) !!}
        {!! Form::text('email', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('primary_number', __('Primary Number'), ['class' => 'control-label']) !!}
        {!! Form::text('primary_number', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('secondary_number', __('Secondary Number'), ['class' => 'control-label']) !!}
        {!! Form::text('secondary_number', null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        @if(Request::get('client') != "")
            {!! Form::hidden('client_id', Request::get('client')) !!}
        @else
            {!! Form::label('client_id', __('Assign Client'), ['class' => 'control-label']) !!}
            {!! Form::select('client_id', $clients, null, ['class' => 'form-control']) !!}
        @endif
    </div>

    {!! Form::submit(__('Create New Contact'), ['class' => 'btn btn-primary']) !!}

    {!! Form::close() !!}


@stop
