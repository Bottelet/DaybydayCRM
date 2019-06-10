@extends('layouts.master')

@section('heading')
    <h1>{{ __('Update Contact') }} ({{ $contact->name }})</h1>
@stop

@section('content')
    {!! Form::model($contact, ['method' => 'PATCH', 'route' => ['contacts.update', $contact->id]]) !!}
      @include('contacts.form', ['submitButtonText' => __('Update Contact')])
    {!! Form::close() !!}
@stop