@extends('layouts.master')
@section('heading')
    Edit Client ({{$client->name}})
@stop

@section('content')
    {!! Form::model($client, [
            'method' => 'PATCH',
            'route' => ['clients.update', $client->id],
            ]) !!}
    @include('clients.form', ['submitButtonText' => Lang::get('client.titles.update')])

    {!! Form::close() !!}

@stop