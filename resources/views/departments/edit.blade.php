@extends('layouts.master')
@section('heading')
    {{__('Edit department')}}
@stop

@section('content')
    <form action="{{ route('departments.update', $department->external_id) }}" method="POST">
        @method('PUT')
        @csrf
        <div class="form-group">
            <label for="name" class="control-label thin-weight">{{ __('Department name') }}:</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}">
        </div>

        <div class="form-group">
            <label for="description" class="control-label thin-weight">{{ __('Department description') }}:</label>
            <textarea name="description" class="form-control">{{ old('description', $department->description) }}</textarea>
        </div>
        <a href="{{route('departments.show', $department->external_id)}}" class="btn btn-default">@lang('Cancel')</a>
        <input type="submit" value="{{ __('Save changes') }}" class="btn btn-md btn-brand">
    </form>
@endsection
