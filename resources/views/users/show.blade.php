@extends('layouts.master')
    @section('content')
    @include('partials.userheader')
<div class="col-sm-8">
  <el-tabs active-name="tasks" style="width:100%">
    <el-tab-pane label="{{__('Tasks')}}" name="tasks">
        <table class="table table-striped" id="tasks-table">
        <h3>{{ __('Tasks assigned') }}</h3>
            <thead>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Deadline') }}</th>
                    <th>
                        <select name="status" id="status-task">
                        <option value="" disabled selected>{{ __('Status') }}</option>
                            <option value="open">{{__('Open')}}</option>
                            <option value="closed">{{__('Closed')}}</option>
                            <option value="all">{{__('All')}}</option>
                        </select>
                    </th>
                </tr>
            </thead>
        </table>
    </el-tab-pane>
    <el-tab-pane label="{{__('Leads')}}" name="leads">
      <table class="table table-striped">
        <table class="table table-striped" id="leads-table">
                <h3>{{ __('Leads assigned') }}</h3>
                <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Deadline') }}</th>
                    <th>
                        <select name="status" id="status-lead">
                        <option value="" disabled selected>{{ __('Status') }}</option>
                            <option value="open">Open</option>
                            <option value="closed">Closed</option>
                            <option value="all">All</option>
                        </select>
                    </th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
    <el-tab-pane label="{{__('Clients')}}" name="clients">
         <table class="table table-striped" id="clients-table">
                <h3>{{ __('Clients assigned') }}</h3>
                <thead>
                <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Primary number') }}</th>
                    <th>{{ __('Email') }}</th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
  </el-tabs>
  </div>
  <div class="col-sm-4">
  <h4>{{ __('Tasks') }}</h4>
<doughnut :statistics="{{$task_statistics}}"></doughnut>
<h4>{{ __('Leads') }}</h4>
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
                        {data: 'primary_number', name: 'primary_number'},
                        {data: 'emaillink', name: 'primary_email'},

                    ],
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
