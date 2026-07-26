@extends('layouts.master')
@section('heading')
    {{__('Edit lead')}}
@stop

@section('content')
    <div class="row">
        <div class="col-sm-8">
            <div class="tablet">
                <div class="tablet__body">
                    <form action="{{route('leads.update', $lead->external_id)}}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="form-group">
                            <label for="title" class="control-label thin-weight">@lang('Title')</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{old('title', $lead->title)}}">
                        </div>
                        <div class="form-group">
                            <label for="description" class="control-label thin-weight">@lang('Description')</label>
                            <textarea name="description" id="description" cols="50" rows="10" class="form-control">{{old('description', $lead->description)}}</textarea>
                        </div>
                        <div class="form-group">
                            <a href="{{route('leads.show', $lead->external_id)}}" class="btn btn-default">@lang('Cancel')</a>
                            <input type="submit" class="btn btn-brand" value="{{__('Save changes')}}">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#description').summernote({
                toolbar: [
                    [ 'fontsize', [ 'fontsize' ] ],
                    [ 'font', [ 'bold', 'italic', 'underline','clear'] ],
                    [ 'color', [ 'color' ] ],
                    [ 'para', [ 'ol', 'ul', 'paragraph'] ],
                    [ 'table', [ 'table' ] ],
                    [ 'insert', [ 'link'] ],
                    [ 'view', [ 'fullscreen' ] ]
                ],
                height: 300,
                disableDragAndDrop: true
            });
        });
    </script>
@endpush
