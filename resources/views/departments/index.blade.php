@extends('layouts.master')

@section('content')
    <div class="col-lg-12 currenttask">
        <table class="table table-hover">
            <h3>All Departments</h3>
            <thead>
            <thead>
            <tr>
                <th>@lang('department.headers.title')</th>
                <th>@lang('department.headers.description')</th>
                @if(Entrust::hasRole('administrator'))
                    <th>@lang('department.headers.action')</th>
                @endif
            </tr>
            </thead>
            <tbody>

            @foreach($department as $dep)
                <tr>
                    <td>{{$dep->name}}</td>
                    <td>{{Str_limit($dep->description, 50)}}</td>
                    @if(Entrust::hasRole('administrator'))
                        <td>   {!! Form::open([
            'method' => 'DELETE',
            'route' => ['departments.destroy', $dep->id]
        ]); !!}
                            {!! Form::submit(Lang::get('department.titles.delete'), ['class' => 'btn btn-danger', 'onclick' => 'return confirm("Are you sure?")']); !!}

                            {!! Form::close(); !!}</td></td>
                    @endif
                </tr>
            @endforeach

            </tbody>
        </table>

    </div>

@stop