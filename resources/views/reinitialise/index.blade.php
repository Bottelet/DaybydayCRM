@extends('layouts.master')


@section('content')
<h1>Reinitialisation de données</h1>
<hr>
    {!! Form::open(['route' => 'reinitialise.reset'])  !!}
    {!! Form::submit('Click me',['class' => 'btn btn-md btn-brand']) !!}
    {!! Form::close() !!}

@stop


