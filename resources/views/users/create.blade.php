@extends('layouts.master')
@section('heading')
    {{ __('Create user') }}
@stop

@section('content')
    <form action="{{route('users.store')}}"
          method="POST"
          enctype="multipart/form-data">
        @csrf
        @include('users.form', ['submitButtonText' => __('Create user')])
    </form>

@stop

@push('scripts')
@include('images._uploadAvatarPreview')
@endpush