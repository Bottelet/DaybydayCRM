@extends('layouts.master')

@section('content')
    <div class="col-lg-12 currenttask">

        <table class="table table-hover">
            <h3>{{ __('All Roles') }}</h3>
            <thead>
            <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
            </thead>
            <tbody>

            @foreach($roles as $role)
                <tr>
                    <td>{{$role->display_name}}</td>
                    <td>{{Str_limit($role->description, 50)}}</td>

                    <td>   {!! Form::open([
            'method' => 'DELETE',
            'route' => ['roles.destroy', $role->id]
        ]); !!}
                        @if($role->id !== 1)
                            {!! Form::submit(Lang::get('role.headers.delete'), ['class' => 'btn btn-danger', 'onclick' => 'return confirm("Are you sure?")']); !!}
                        @endif
                        {!! Form::close(); !!}</td>
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>

        <a href="{{ route('roles.create')}}">
            <button class="btn btn-success">{{ __('Add new Role') }}e</button>
        </a>

    </div>

@stop