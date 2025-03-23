@extends('layouts.master')

@section('content')
    <div class="tablet">
        <div class="tablet__head">
            <div class="tablet__head-label">
                <h3 class="tablet__head-title">{{ __('Reset data') }}</h3>
            </div>
        </div>
        <div class="tablet__body">
            <div class="tablet__items">
                <h4>{{ __('Reset all tables data') }}</h4>
                <p></p>
                <a href="{{ route('database.reset')}}"><button class="btn btn-md btn-brand">Reset Data</button></a>
            </div>
        </div>
        <div class="tablet__footer">

        </div>
    </div>
@Stop