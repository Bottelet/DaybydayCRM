@extends('layouts.master')
@section('heading')
    {{__('All Leads')}}
    <a href="{{ route('leads.create')}}" class="btn btn-brand float-right">{{ __('New Lead') }}</a>
@stop

@section('content')
    <dynamictable dateFormat="{{frontendDate()}}"></dynamictable>
@stop

