@extends('layouts.master')

@section('content')
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name" class="control-label">{{ __('Name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label for="description" class="control-label">{{ __('Description') }}</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>
        <input type="submit" value="{{ __('Add new Role') }}" class="btn btn-md btn-brand">
    </form>

@endsection