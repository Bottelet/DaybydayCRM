@extends('errors::illustrated-layout')

@section('title', __('Page Expired'))
@section('code', '419')
@section('image')
    <img src="{{asset('images/419error.jpg')}}" alt="419" class="img-fluid w-75">
@endsection
@section('message', __('Page Expired'))
