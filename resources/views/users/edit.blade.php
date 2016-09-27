@extends('layouts.master')

@section('heading')

<h1>@lang('user.titles.edit')</h1>
@stop

@section('content')


{!! Form::model($user, [
        'method' => 'PATCH',
        'route' => ['users.update', $user->id],
        'files'=>true,
        'enctype' => 'multipart/form-data'
        ]) !!}
        
@include('users.form', ['submitButtonText' => Lang::get('user.headers.update_submit')])

{!! Form::close() !!}

@stop