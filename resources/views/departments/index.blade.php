@extends('layouts.master')
@section('heading')
    {{__('All Departments')}}
    @if(Entrust::hasRole('administrator') || Entrust::hasRole('owner'))
        <a href="{{ route('departments.create')}}">
            <button class="btn btn-brand cta-btn pull-right">@lang('New Department')</button>
        </a>
    @endif
@stop
@section('content')
    <table class="table table-hover" id="departments-table">
        <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Description') }}</th>
            <th></th>
        </tr>
        </thead>
    </table>

    @push('scripts')
    <style type="text/css">
    .table > tbody > tr > td {
        border-top:none !important;
    }
    .table-actions {
       opacity: 0;
    }
    #departments-table tbody tr:hover .table-actions{
      opacity: 1;
    }
</style>
        <script>
            $(function () {
                var table = $('#departments-table').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: '{!! route('departments.indexDataTable') !!}',
                    language: {
                        url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                    },
                    drawCallback: function(){
                        var length_select = $(".dataTables_length");
                        var select = $(".dataTables_length").find("select");
                        select.addClass("tablet__select");
                    },
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'description', name: 'description', orderable: false, searchable: false},
                        @if(Entrust::can('client-delete'))
                        { data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},
                        @endif
                    ]
                });

            });
        </script>
    @endpush
@stop
