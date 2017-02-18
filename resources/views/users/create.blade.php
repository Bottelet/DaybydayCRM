@extends('layouts.master')
@section('heading')
    <h1>{{ __('Create user') }}</h1>
@stop

@section('content')
    {!! Form::open([
            'route' => 'users.store',
            'files'=>true,
            'enctype' => 'multipart/form-data'

            ]) !!}
    @include('users.form', ['submitButtonText' => __('Create user')])

    {!! Form::close() !!}


@stop