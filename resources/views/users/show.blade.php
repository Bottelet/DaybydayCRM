@extends('layouts.master')

    @section('content')
    @include('partials.userheader')
<div class="col-sm-8">
        <div class="tablet">
            <div class="tablet__head">
                <div class="tablet__head-label">
                    <h3 class="tablet__head-title">@lang('Overview')</h3>
                </div>
            </div>
            <div class="tablet__body">
  <el-tabs active-name="tasks" style="width:100%">
    <el-tab-pane label="{{ __('Tasks') }}" name="tasks">
        <table class="table table-hover" id="tasks-table">
        <h3>{{ __('Tasks assigned') }}</h3>
            <thead>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Deadline') }}</th>
                    <th>
                        <select name="status_id" id="status-task" class="table-status-input">
                        <option value="" disabled selected>{{ __('Status') }}</option>
                            @foreach($task_statuses as $task_status)
                                <option value="{{$task_status->title}}">{{$task_status->title}}</option>
                            @endforeach
                            <option value="all">All</option>
                        </select>
                    </th>
                </tr>
            </thead>
        </table>
    </el-tab-pane>
    <el-tab-pane label="{{ __('Leads') }}" name="leads">
        <table class="table table-hover" id="leads-table">
                <h3>{{ __('Leads assigned') }}</h3>
                <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Client') }}</th>
                    <th>{{ __('Created at') }}</th>
                    <th>{{ __('Deadline') }}</th>
                    <th>
                        <select name="status_id" id="status-lead" class="table-status-input">
                        <option value="" disabled selected>{{ __('Status') }}</option>
                              @foreach($lead_statuses as $lead_status)
                                <option value="{{$lead_status->title}}">{{$lead_status->title}}</option>
                            @endforeach
                            <option value="all">All</option>
                        </select>
                    </th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
    <el-tab-pane label="{{ __('Clients') }}" name="clients">
         <table class="table table-hover" id="clients-table">
                <h3>{{ __('Clients assigned') }}</h3>
                <thead>
                <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Vat') }}</th>
                    <th>{{ __('Address') }}</th>
                </tr>
                </thead>
            </table>
    </el-tab-pane>
  </el-tabs>
  </div>
</div>
</div>
  <div class="col-sm-4">
      <div class="tablet">
          <div class="tablet__head">
              <div class="tablet__head-label">
                  <h3 class="tablet__head-title">@lang('Tasks')</h3>
              </div>
          </div>
          <div class="tablet__body">
<doughnut :statistics="{{$task_statistics}}" closed="{{ __('Closed') }}" open="{{ __('Open') }}"></doughnut>
          </div>
      </div>
        <div class="tablet">
            <div class="tablet__head">
                <div class="tablet__head-label">
                    <h3 class="tablet__head-title">@lang('Leads')</h3>
                </div>
            </div>
            <div class="tablet__body">
                <doughnut :statistics="{{$lead_statistics}}" closed="{{ __('Closed') }}" open="{{ __('Open') }}"></doughnut>
            </div>
        </div>
  </div>
<div class="col-sm-12">
  <!--<passportPersonalAccessTokens></passportPersonalAccessTokens>-->
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
                    autoWidth: false,
                    ajax: '{!! route('users.taskdata', ['id' => $user->id]) !!}',
                    drawCallback: function(){
                        var length_select = $(".dataTables_length");
                        var select = $(".dataTables_length").find("select");
                        select.addClass("tablet__select");
                  },
                  language: {
                      url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                  },
                    columns: [
                        {data: 'titlelink', name: 'title'},
                        {data: 'client_id', name: 'Client', orderable: false, searchable: false},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'deadline', name: 'deadline'},
                        {data: 'status_id', name: 'status.title', orderable: false},
                    ]
                });

                $('#status-task').change(function() {
                selected = $("#status-task option:selected").val();
                if(selected == "all") {
                     table.columns(4).search( '' ).draw();
                } else {
                    table.columns(4).search( selected ? '^'+selected+'$' : '', true, false ).draw();
                }
              });  

          });
            $(function () {
                $('#clients-table').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: '{!! route('users.clientdata', ['id' => $user->id]) !!}',
                    language: {
                        url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                    },
                    drawCallback: function(){
                        var length_select = $(".dataTables_length");
                        var select = $(".dataTables_length").find("select");
                        select.addClass("tablet__select");
                    },
                    columns: [

                        {data: 'clientlink', name: 'company_name'},
                        {data: 'vat', name: 'vat'},
                        {data: 'address', name: 'address'},

                    ]
                });
            });

            $(function () {
              var table = $('#leads-table').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ajax: '{!! route('users.leaddata', ['id' => $user->id]) !!}',
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
                        {data: 'client_id', name: 'Client', orderable: false, searchable: false},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'deadline', name: 'deadline'},
                        {data: 'status_id', name: 'status.title', orderable: false},
                    ]
                });

              $('#status-lead').change(function() {
                selected = $("#status-lead option:selected").val();
                if(selected == "all") {
                     table.columns(4).search( '' ).draw();
                } else {
                    table.columns(4).search( selected ? '^'+selected+'$' : '', true, false ).draw();
                }
              });  
          });
        </script>
@endpush


