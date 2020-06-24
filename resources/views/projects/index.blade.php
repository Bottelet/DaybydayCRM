@extends('layouts.master')
@section('heading')
    {{ __('All projects')}}
@stop

@section('content')
    <table class="table table-hover" id="projects-table">
        <thead>
        <tr>

            <th>{{ __('Title') }}</th>
            <th>{{ __('Created at') }}</th>
            <th>{{ __('Deadline') }}</th>
            <th>{{ __('Assigned') }}</th>
            <th>
                <select name="status_id" id="status-task" class="table-status-input">
                    <option value="" disabled>{{ __('Status') }}</option>
                    @foreach($statuses as $status)
                        <option class="table-status-input-option" {{ $status->title == 'Open' ? 'selected' : ''}} value="{{$status->title}}">{{$status->title}}</option>
                    @endforeach
                    <option value="all">All</option>
                </select>
            </th>
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
    #projects-table tbody tr:hover .table-actions{
      opacity: 1;
    }
</style>
    <script>
        $(function () {
            var table = $('#projects-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{!! route('projects.index.data') !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                drawCallback: function(){
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                columns: [
                    {data: 'titlelink', name: 'title'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'deadline', name: 'deadline'},
                    {data: 'user_assigned_id', name: 'user_assigned_id'},
                    {data: 'status_id', name: 'status.title', orderable: false},
                    {data: 'view', name: 'view', orderable: false, searchable: false, class: 'table-actions'},
                ]
            });
            table.columns(4).search('^' + 'Open' + '$', true, false).draw();
            $('#status-task').change(function () {
                selected = $("#status-task option:selected").val();
                if (selected == "all") {
                    table.columns(4).search('').draw();
                } else {
                    table.columns(4).search(selected ? '^' + selected + '$' : '', true, false).draw();
                }
            });
        });

    </script>
@endpush