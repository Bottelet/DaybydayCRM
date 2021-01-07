@extends('layouts.master')
@section('heading')
    {{ __('All Roles') }}
@stop
@section('content')
    <div class="col-lg-12 currenttask">
        <table class="table table-hover " id="roles-table">
            <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Permissions') }}</th>
                <th class="action-header"></th>
                <th class="action-header"></th>
            </tr>
            </thead>
        </table>
        <a href="{{ route('roles.create')}}">
            <button class="btn btn-md btn-brand">{{ __('Add new Role') }}</button>
        </a>
    </div>
@stop


@push('scripts')
    <script>
        $(function () {
            $('#roles-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('roles.data') !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                drawCallback: function(){
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                autoWidth: false,
                columns: [
                    {data: 'namelink', name: 'name', width: "40%"},
                    {data: 'permissions', name: 'permissions', orderable: false, searchable: false,},
                    { data: 'view', name: 'view', orderable: false, searchable: false, class:'fit-action-delete-th'},
                    @if(auth()->user()->roles->first()->name == "owner" || auth()->user()->roles->first()->name == "administrator")
                    { data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th'}
                    @endif
                ]
            });
        });


    </script>
@endpush


