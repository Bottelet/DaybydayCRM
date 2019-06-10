@extends('layouts.master')
@section('heading')
    <h1>{{ __('Create Contact') }}</h1>
@stop

@section('content')

    {!! Form::open(['route' => 'contacts.store']) !!}
        @include('contacts.form', ['submitButtonText' => __('Create New Contact')])
    {!! Form::close() !!}

@stop
