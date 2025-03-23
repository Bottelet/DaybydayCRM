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
                <form action="{{route('tasks.store')}}" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="csv" class="control-label thin-weight">@lang('CSV')</label>
                        <input type="file" name="csv" id="csv">
                    </div>
                    <div class="form-group">
                        <label for="table" class="control-label thin-weight">@lang('Table')</label>
                        <select name="table" id="table" class="form-control">
                            
                        </select>
                    </div>
                    <button type="submit" class="btn btn-md btn-brand">Import CSV</button>
                </form>
            </div>
        </div>
        <div class="tablet__footer">

        </div>
    </div>
</div>
@Stop