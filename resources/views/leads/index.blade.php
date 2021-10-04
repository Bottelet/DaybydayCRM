@extends('layouts.master')
@section('heading')
    {{__('All Leads')}}
    @if(Entrust::can('lead-create'))
    <a href="{{ route('leads.create')}}" class="btn btn-brand float-right">{{ __('New Lead') }}</a>
    @endif
@stop

@section('content')
    <dynamictable dateFormat="{{frontendDate()}}"></dynamictable>
@stop

