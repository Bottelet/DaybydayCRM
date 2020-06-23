@extends('layouts.master')
@section('heading')
    {{ __('Edit Client :client' , ['client' => '(' . $client->name. ')']) }}
@stop

@section('content')
    {!! Form::model($client, [
            'method' => 'PATCH',
            'route' => ['clients.update', $client->external_id],
            ]) !!}
    @include('clients.form', ['submitButtonText' => __('Update client')])

    {!! Form::close() !!}

@stop