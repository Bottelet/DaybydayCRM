@extends('layouts.master')
@section('heading')
    {{__('All Leads')}}
@stop

@section('content')
    <dynamictable dateFormat="{{frontendDate()}}"></dynamictable>
@stop

