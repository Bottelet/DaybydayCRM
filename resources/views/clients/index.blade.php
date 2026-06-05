@extends('layouts.master')
@section('heading')
    {{ __('All clients') }}
@stop

@section('content')
    <table class="table table-hover" id="clients-table">
        <thead>
        <tr>
            <th>{{ __('Company') }}</th>
            <th>{{ __('Vat') }}</th>
            <th>{{ __('Address') }}</th>
            <th class="action-header"></th>
            <th class="action-header"></th>
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
    #clients-table tbody tr:hover .table-actions{
      opacity: 1;
    }
</style>
<script>
    $(function () {
        $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('clients.data') !!}',
            language: {
                url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
            },
            name:'search',
            drawCallback: function(){
                var length_select = $(".dataTables_length");
                var select = $(".dataTables_length").find("select");
                select.addClass("tablet__select");
            },
            autoWidth: false,
            columns: [
                {data: 'namelink', name: 'company_name'},
                {data: 'vat', name: 'vat'},
                {data: 'address', name: 'address'},

                { data: 'view', name: 'view', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},

                @if(Entrust::can('client-update'))
                { data: 'edit', name: 'edit', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},
                @endif
                @if(Entrust::can('client-delete'))
                { data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},
                @endif

            ]
        });

    });
    @if(!config('app.tour_disabled'))
    $(document).ready(function () {
        if(!getCookie("step_client_index")) {
            var canCreateTask = '{{ auth()->user()->can('task-create') }}';
            var canCreateProject = '{{ auth()->user()->can('project-create') }}';

            $("#projects").addClass("in");
            $("#tasks").addClass("in");

            var TOUR_TEMPLATE = ''+
                '<div class="popover tour" role="dialog">'+
                '  <div class="arrow"></div>'+
                '  <button type="button" data-role="end" aria-label="{{ trans("Close tour") }}" '+
                '    style="position:absolute;top:6px;right:10px;background:none;border:none;'+
                '           font-size:22px;line-height:1;cursor:pointer;color:#555;z-index:1;" '+
                '    title="{{ trans("Close tour") }}">&#215;</button>'+
                '  <h3 class="popover-title"></h3>'+
                '  <div class="popover-content"></div>'+
                '  <div class="popover-navigation" style="padding:8px 14px 10px;display:flex;gap:6px;align-items:center;">'+
                '    <button class="btn btn-sm btn-default" data-role="prev">&#8592; {{ trans("Prev") }}</button>'+
                '    <button class="btn btn-sm btn-primary" data-role="next">{{ trans("Next") }} &#8594;</button>'+
                '    <button class="btn btn-sm btn-danger" data-role="end" style="margin-left:auto;">&#10005; {{ trans("Don\'t show again") }}</button>'+
                '  </div>'+
                '</div>';

            var tour = new Tour({
                storage: false,
                backdrop: true,
                template: TOUR_TEMPLATE,
                onEnd: function () {
                    setCookie("step_client_index", '1', 3650);
                },
            });
            tour.addStep({
                element: "#clients-table",
                title: "{{trans("Client overview")}}",
                content: "{{trans("All your active clients will be shown here")}}",
                placement: 'top'
            });
            if(canCreateTask) {
                tour.addStep({
                    element: "#newTask",
                    title: "{{trans("Create task")}}",
                    content: "{{trans("Same as with clients you can create a new task. Tasks has a primary user assigned, and a client, it can also be related to a project")}}"
                });
            }
            if (canCreateProject) {
                tour.addStep({
                    element: "#newProject",
                    title: "{{trans("Create project")}}",
                    content: "{{trans("Projects are used to keep track of tasks that might be related to a bigger assignment for the client. And gives the possibility of multiple people working various tasks and keep track of the tasks.")}}"
                });
            }

            tour.init();
            tour.start();
        }
        function setCookie(key, value, expiry) {
            var expires = new Date();
            expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
            document.cookie = key + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
        }
        function getCookie(key) {
            var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
            return keyValue ? keyValue[2] : null;
        }
    });
    @endif
</script>
@endpush


