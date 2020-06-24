@extends('layouts.master')
@section('heading')
    {{__('All Unqualified Leads')}}
@stop

@section('content')
    <dynamictable dateFormat="{{frontendDate()}}"></dynamictable>
@stop

@push('scripts')
    <style type="text/css">
        .table > tbody > tr > td {
            border-top: none !important;
        }
        .table-actions {
            opacity: 0;
        }

        #leads-table tbody tr:hover .table-actions {
            opacity: 1;
        }
    </style>
    <script>
        $(function () {
            var table = $('#leads-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{!! route('leads.data') !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                drawCallback: function () {
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                columns: [
                    {data: 'titlelink', name: 'title'},
                    {data: 'user_created_id', name: 'user_created_id'},
                    {data: 'deadline', name: 'deadline',},
                    {data: 'user_assigned_id', name: 'user_assigned_id'},
                    {data: 'status_id', name: 'status.title', orderable: false},
                    {data: 'view', name: 'view', orderable: false, searchable: false, class: 'table-actions'},
                ]
            });

            $('#leads-table').on('click', function(){
                console.log( table.cell(this))
            });

            table.columns(4).search('^' + 'Open' + '$', true, false).draw();
            $('#status-lead').change(function () {
                selected = $("#status-lead option:selected").val();
                if (selected == "all") {
                    table.columns(4).search('').draw();
                } else {
                    table.columns(4).search(selected ? '^' + selected + '$' : '', true, false).draw();
                }
            });
        });
    </script>
@endpush
