@extends('layouts.master')
@section('heading')
    {{ __('All users') }}
@stop

@section('content')
    <table class="table table-hover" id="users-table">
        <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Mail') }}</th>
            <th>{{ __('Work number') }}</th>
            <th class="action-header"></th>
            <th class="action-header"></th>
{{--            <th class="action-header"></th>--}}
        </tr>
        </thead>
    </table>


        <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header" style="padding:35px 50px;">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4><span class="ion ion-md-lock" style="margin-right: 1em;"></span> Handle deletion of user</h4>
        </div>
        <div class="modal-body" style="padding:40px 50px;">
          <form role="form">

           <!--HANDLE TASKS-->
            <div class="form-group">
          <label for="tasks"><span class=""></span> {{ __('How to handle the user tasks?') }}</label>
        <select name="handle_tasks" id="handle_tasks" class="form-control">
            <option value="delete_all_tasks">{{ __('Delete all tasks') }}</option>
            <option value="move_all_tasks"> {{ __('Move all tasks') }}</option>
        </select>   
     </div>
            <div class="form-group" id="assign_tasks" style="display:none">
          <label for="user_tasks"><span class="glyphicon glyphicon-user"></span> {{ __('Choose a new user to assign the tasks') }}</label>
        <select name="user_tasks" id="user_tasks" class="form-control">
            <option value="null" disabled selected> {{ __('Select a user') }} </option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>   
            </div>

             <!--HANDLE LEADS-->
            <div class="form-group">
          <label for="handle_leads"><span class=""></span> {{ __('How to handle the user leads?') }}</label>
        <select name="leads" id="handle_leads" class="form-control">
            <option value="delete_all_leads">{{ __('Delete all leads') }}</option>
            <option value="move_all_leads"> {{ __('Move all leads') }}</option>
        </select>   
        </div>
            <div class="form-group" id="assign_leads" style="display:none">
          <label for="user_leads"><span class="glyphicon glyphicon-user"></span> {{ __('Choose a new user to assign the leads') }}</label>
        <select name="user_leads" id="user_leads" class="form-control">
            <option value="null" disabled selected> {{ __('Select a user') }} </option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>   
            </div>

            <!--HANDLE CLIENTS-->
        <div class="form-group">
          <label for="handle_clients"><span class=""></span> {{ __('How to handle the user clients?') }}</label>
        <select name="clients" id="handle_clients" class="form-control">
            <option value="delete_all_clients">{{ __('Delete all clients') }}</option>
            <option value="move_all_clients"> {{ __('Move all clients') }}</option>
        </select>   
        </div>
            <div class="form-group" id="assign_clients" style="display:none">
          <label for="user_clients"><span class="glyphicon glyphicon-user"></span> {{ __('Choose a new user to assign the clients') }}</label>
        <select name="user_clients" id="user_clients" class="form-control">
            <option value="null" disabled selected> {{ __('Select a user') }} </option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>   
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> {{ __('Cancel') }}</button>
          <button type="submit" id="confirm_delete" class="btn btn-success"><span class="glyphicon glyphicon-off"></span> {{ __('Delete') }}</button>
        </div>
      </div>
      
    </div>
  </div> 

@stop

@push('scripts')
<style type="text/css">
    .table > tbody > tr > td {
        border-top:none !important;
    }
    .table-actions {
       opacity: 0;
    }
    #users-table tbody tr:hover .table-actions{
      opacity: 1;
    }
</style>

<script>
    $(function () {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: '{!! route('users.data') !!}',
            language: {
                url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
            },
            drawCallback: function(){
                var length_select = $(".dataTables_length");
                var select = $(".dataTables_length").find("select");
                select.addClass("tablet__select");
            },
            columns: [

                {data: 'namelink', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'primary_number', name: 'primary_number'},
                {
                    data: 'view', name: 'view', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'
                },
                @if(Entrust::can('user-update'))
                {
                    data: 'edit', name: 'edit', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'
                },
                @endif
              /**  @if(Entrust::can('user-delete'))
                {
                    data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'
                },
                @endif**/
            ]
        });
    });


     function openModal(client_id) {
        $("#confirm_delete").attr('delete-id', client_id);
        $("#myModal").modal();
    }
    
    $("#handle_tasks").click(function () {
    
    if($("#handle_tasks").val() == "move_all_tasks") {
        $("#assign_tasks").css('display', 'block');
    } else 
    {
        $("#assign_tasks").css('display', 'none');
    }

    });


    $("#handle_clients").click(function () {

   if($("#handle_clients").val() == "move_all_clients") {
            $("#assign_clients").css('display', 'block');
    } else {
        $("#assign_clients").css('display', 'none');
    }
    });
    
    $("#handle_leads").click(function () {

   if($("#handle_leads").val() == "move_all_leads") {
            $("#assign_leads").css('display', 'block');
    } else {
        $("#assign_leads").css('display', 'none');
    }
    });

    $("#confirm_delete").click(function () {
        external_id = $(this).attr("delete-id"); 
       handle_leads = $("#handle_leads").val();
       handle_tasks =  $("#handle_tasks").val();
       handle_clients =  $("#handle_clients").val()
       leads_user = $("#user_leads").val();
       tasks_user = $("#user_tasks").val();
       clients_user = $("#user_clients").val();
        $.ajax({
            url: "/users/" + external_id,
            type: 'DELETE',
                headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
        data: {
        tasks: handle_tasks,
        leads: handle_leads,
        clients: handle_clients,
        task_user: tasks_user,
        lead_user: leads_user,
        client_user: clients_user,
       },   
        complete: function (jqXHR, textStatus) {
                location.reload();
            },
            success: function (data, textStatus, jqXHR) {
                // success callback
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // error callback
            }
        });
    });
</script>
@endpush
