@extends('errors::illustrated-layout')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('image')
    <img src="{{asset('images/401error.jpg')}}" alt="403" class="img-fluid w-75">
@endsection
@section('message', __('Unauthorized'))
