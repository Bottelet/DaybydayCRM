@extends('layouts.master')

@section('heading')
    <h1>{{ __('Edit user') }}</h1>
@stop

@section('content')


    {!! Form::model($user, [
            'method' => 'PATCH',
            'route' => ['users.update', $user->id],
            'files'=>true,
            'enctype' => 'multipart/form-data'
            ]) !!}

    @include('users.form', ['submitButtonText' =>  __('Update user')])

    {!! Form::close() !!}

@stop