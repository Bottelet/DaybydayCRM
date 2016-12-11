@extends('layouts.master')

@section('content')
    <div class="col-lg-12 currenttask">

        <table class="table table-hover">
            <h3>@lang('role.headers.roles')</h3>
            <thead>
            <thead>
            <tr>
                <th>@lang('role.headers.name')</th>
                <th>@lang('role.headers.description')</th>
                <th>@lang('role.headers.action')</th>
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
            <button class="btn btn-success">@lang('role.headers.add_new')e</button>
        </a>

    </div>

@stop