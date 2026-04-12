@extends('layouts.master')

@section('heading')
    {{ __('Edit user') }}
@stop

@section('content')

    <form action="{{route('users.update', [$user->external_id])}}"
          method="POST"
          enctype="multipart/form-data"
          data-file="true"
    >
        @method('PATCH')
        @csrf
        @include('users.form', ['submitButtonText' =>  __('Update user')])
    </form>

@stop

@push('scripts')
@include('images._uploadAvatarPreview')
@endpush
