@extends('errors::illustrated-layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('image')
    <img src="{{asset('images/403image.jpg')}}" alt="403" class="img-fluid w-75">
    @endsection
@section('message', __($exception->getMessage() ?: 'Forbidden'))
