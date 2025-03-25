@extends('layouts.master')

@section('content')
<div class="col-sm-8">
    <div class="tablet">
        <div class="tablet__head">
            <div class="tablet__head-label">
                <h3 class="tablet__head-title">{{ __('Import CSV') }}</h3>
            </div>
        </div>
        <div class="tablet__body">
            <div class="tablet__items">
                <h4>{{ __('Import CSV to database') }}</h4>
                <p></p>
                <form action="{{route('database.import.csv')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="csv1" class="control-label thin-weight">@lang('CSV 1')</label>
                        <input type="file" name="csv1" id="csv1">
                    </div>
                    <div class="form-group">
                        <label for="csv2" class="control-label thin-weight">@lang('CSV 2')</label>
                        <input type="file" name="csv2" id="csv2">
                    </div>
                    <div class="form-group">
                        <label for="csv3" class="control-label thin-weight">@lang('CSV 3')</label>
                        <input type="file" name="csv3" id="csv3">
                    </div>
                    <button type="submit" class="btn btn-md btn-brand">Import CSV</button>
                </form>
            </div>
        </div>
        <div class="tablet__footer">

        </div>
    </div>
</div>
@if(isset($errorImport) && count($errorImport) > 0)
    <div class="alert alert-danger" style="margin-top: 450px;">
        @foreach($errorImport as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
@Stop