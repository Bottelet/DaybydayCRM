@extends('layouts.master')
    @section('content')
    @include('partials.userheader')
<div class="col-sm-8">
  <el-tabs active-name="tasks" style="width:100%">
    <el-tab-pane label="Tasks" name="tasks">
        <table class="table table-hover" id="tasks-table">
        <h3>@lang('task.headers.tasks_assigned')</h3>
            <thead>
                    <th>@lang('task.headers.title')</th>
                    <th>@lang('task.headers.client')</th>
                    <th>@lang('task.headers.created_at')</th>
                    <th>@lang('task.headers.deadline')</th>
                    <th>
                        <select name="status" id="status-task">
                        <option value="" disabled selected>@lang('Status')</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="all">All</option>
                        </select>
                    </th>
                </tr>
            </thead>
        </table>
    </el-tab-pane>
    <el-tab-pane label="Leads" name="leads">
      <table class="table table-hover">
        <table class="table table-hover" id="leads-table">
                <h3>@lang('lead.headers.leads_assigned')</h3>
                <thead>
                <tr>
                    <th>@lang('lead.headers.title')</th>
                    <th>@lang('lead.headers.client')</th>
                    <th>@lang('lead.headers.created_at')</th>
                    <th>@lang('lead.headers.deadline')</th>
                    <th>
                        <select name="status" id="status-lead">
                        <option value="" disabled selected>@lang('Status')</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="all">All</option>
                        </select>
                    </th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
    <el-tab-pane label="Clients" name="clients">
         <table class="table table-hover" id="clients-table">
                <h3>@lang('client.status.assigned')</h3>
                <thead>
                <tr>
                    <th>@lang('client.headers.name')</th>
                    <th>@lang('client.headers.company')</th>
                    <th>@lang('client.headers.primary_number')</th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
  </el-tabs>
  </div>
  <div class="col-sm-4">
  <h4>Tasks</h4>
<doughnut :statistics="{{$task_statistics}}"></doughnut>
<h4>Leads</h4>
<doughnut :statistics="{{$lead_statistics}}"></doughnut>
  </div>

   @stop 
@push('scripts')
        <script>
        $('#pagination a').on('click', function (e) {
            e.preventDefault();
            var url = $('#search').attr('action') + '?page=' + page;
            $.post(url, $('#search').serialize(), function (data) {
                $('#posts').html(data);
            });
        });

            $(function () {
              var table = $('#tasks-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{!! route('users.taskdata', ['id' => $user->id]) !!}',
                    columns: [

                        {data: 'titlelink', name: 'title'},
                        {data: 'client_id', name: 'Client', orderable: false, searchable: false},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'deadline', name: 'deadline'},
                        {data: 'status', name: 'status', orderable: false},
                    ]
                });

                $('#status-task').change(function() {
                selected = $("#status-task option:selected").val();
                    if(selected == 'open') {
                        table.columns(4).search(1).draw();
                    } else if(selected == 'closed') {
                        table.columns(4).search(2).draw();
                    } else {
                         table.columns(4).search( '' ).draw();
                    }
              });  

          });
            $(function () {
                $('#clients-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{!! route('users.clientdata', ['id' => $user->id]) !!}',
                    columns: [

                        {data: 'clientlink', name: 'name'},
                        {data: 'company_name', name: 'company_name'},
                        {data: 'primary_number', name: 'primary_number'},

                    ]
                });
            });

            $(function () {
              var table = $('#leads-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{!! route('users.leaddata', ['id' => $user->id]) !!}',
                    columns: [

                        {data: 'titlelink', name: 'title'},
                        {data: 'client_id', name: 'Client', orderable: false, searchable: false},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'contact_date', name: 'contact_date'},
                        {data: 'status', name: 'status', orderable: false},
                    ]
                });

              $('#status-lead').change(function() {
                selected = $("#status-lead option:selected").val();
                    if(selected == 'open') {
                        table.columns(4).search(1).draw();
                    } else if(selected == 'closed') {
                        table.columns(4).search(2).draw();
                    } else {
                         table.columns(4).search( '' ).draw();
                    }
              });  
          });
        </script>
@endpush


