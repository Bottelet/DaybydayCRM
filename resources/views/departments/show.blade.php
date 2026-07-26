@extends('layouts.master')
@section('heading')
    {{__('Department')}}
@stop

@section('content')
    <div class="row">
        <div class="col-sm-8">
            <div class="tablet">
                <div class="tablet__head tablet__head__color-brand">
                    <div class="tablet__head-label">
                        <h3 class="tablet__head-title text-white">{{$department->name}}</h3>
                    </div>
                    @if(Entrust::hasRole('administrator') || Entrust::hasRole('owner'))
                        <a href="{{route('departments.edit', $department->external_id)}}" class="tablet__head-icon" title="{{__('Edit')}}">
                            <i class="icon ion-md-create text-white"></i>
                        </a>
                    @endif
                </div>
                <div class="tablet__body">
                    <p>{!! $department->description !!}</p>
                </div>
            </div>
        </div>
    </div>
@stop
