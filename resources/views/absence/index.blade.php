@extends('layouts.master')
@section('content')
    <table class="table table-hover" id="absence-table">
        <thead>
        <tr>
            <th>{{ __('User') }}</th>
            <th>{{ __('Start') }}</th>
            <th>{{ __('End') }}</th>
            <th>{{ __('Reason') }}</th>
            <th class="action-header"></th>
        </tr>
        </thead>
    </table>
@stop

@push('scripts')
    <style type="text/css">
        .table > tbody > tr > td {
            border-top:none !important;
        }
        .table-actions {
            opacity: 0;
        }
        #absence-table tbody tr:hover .table-actions{
            opacity: 1;
        }
    </style>
    <script>
        $(function () {
            $('#absence-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('absence.data') !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                name:'search',
                drawCallback: function() {
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                autoWidth: false,
                columns: [
                    {data: 'user_id', name: 'user_id', orderable: false, searchable: false,},
                    {data: 'start_at', name: 'start_at'},
                    {data: 'end_at', name: 'end_at'},
                    {data: 'reason', name: 'reason'},
                    @if(Entrust::can('absence-manage'))
                    { data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},
                    @endif

                ]
            });

        });
        </script>

@endpush

