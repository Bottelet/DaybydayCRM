@extends('layouts.master')

@section('heading')
    {{ __('Edit user') }}
@stop

@section('content')


    {!! Form::model($user, [
            'method' => 'PATCH',
            'route' => ['users.update', $user->external_id],
            'files'=>true,
            'enctype' => 'multipart/form-data'
            ]) !!}
            {!! Form::close() !!}
            <form   action="{{route('users.update', [$user->external_id])}}"
                    method="POST"
                    enctype="multipart/form-data"
                    data-file="true"
            >
            @method('PATCH')
            {{csrf_field()}}
    @include('users.form', ['submitButtonText' =>  __('Update user')])
            </form>

@stop

@push('scripts')
@include('images._uploadAvatarPreview')
@endpush
