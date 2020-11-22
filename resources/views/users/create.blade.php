@extends('layouts.master')
@section('heading')
    {{ __('Create user') }}
@stop

@section('content')
    {!! Form::open([
            'route' => 'users.store',
            'files'=>true,
            'enctype' => 'multipart/form-data'

            ]) !!}
    @include('users.form', ['submitButtonText' => __('Create user')])

    {!! Form::close() !!}


@stop

@push('scripts')
@include('images._uploadAvatarPreview')
@endpush