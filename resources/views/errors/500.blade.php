@extends('errors::illustrated-layout')

@section('title', __('Server Error'))
@section('code', '500')
@section('image')
    <img src="{{asset('images/500error.jpg')}}" alt="403" class="img-fluid w-75">
    @endsection
@section('message', __('Server Error'))
