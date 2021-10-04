@extends('errors::illustrated-layout')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('image')
    <img src="{{asset('images/503Error.jpg')}}" alt="403" class="img-fluid w-75">
@endsection
@section('message', __('Service Unavailable'))
