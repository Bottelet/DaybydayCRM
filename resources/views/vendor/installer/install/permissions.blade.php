@extends('installer::layouts.master')

@section('container')
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-file"></i>
                @lang('installer::installer.permissions.title')
            </h3>
        </div>
        <div class="panel-body">
            <div class="bs-component">
                <ul class="list-group">
                    @foreach($permissions['permissions'] as $permission)
                        <li class="list-group-item">
                            @if($permission['isSet'])
                                <span class="badge badge-success">
                                    @lang('installer::installer.' . $permission['permission'])
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    @lang('installer::installer.' . $permission['permission'])
                                </span>
                            @endif
                            {{ $permission['folder'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @if(!isset($permissions['errors']))
                <a class="btn btn-success" href="{{ route('installer::database') }}">
                    @lang('installer::installer.next')
                </a>
            @endif
        </div>
    </div>
@stop