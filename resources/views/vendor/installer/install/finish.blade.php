@extends('installer::layouts.master')

@section('container')
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="glyphicon glyphicon-home"></i>
                @lang('installer::installer.final.title')
            </h3>
        </div>
        <div class="panel-body">
			@if (Session::has('message'))
            <div class="alert alert-success">
                {{ Session::get('message') }}
            </div>
			@endif
			aaaaaaaaaaa
        </div>
    </div>
@stop