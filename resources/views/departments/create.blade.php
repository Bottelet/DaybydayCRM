@extends('layouts.master')
@section('heading')
    {{__('Create department')}}
@stop

@section('content')
    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name" class="control-label thin-weight">{{ __('Department name') }}:</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label for="description" class="control-label thin-weight">{{ __('Department description') }}:</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>
        <input type="submit" value="{{ __('Create Department') }}" class="btn btn-md btn-brand">
    </form>

@endsection