@extends('layouts.master')
@section('heading')
    {{ __('Edit Client :client' , ['client' => '(' . $client->name. ')']) }}
@stop

@section('content')
    <form action="{{ route('clients.update', $client->external_id) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('clients.form', ['submitButtonText' => __('Update client')])
    </form>

@stop