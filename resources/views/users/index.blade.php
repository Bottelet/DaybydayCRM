@extends('layouts.master')
@section('heading')
    <h1>{{ __('All users') }}</h1>
@stop

@section('content')

    <table class="table table-striped" id="users-table">
        <thead>
        <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Mail') }}</th>
            <th>{{ __('Work number') }}</th>
            <th></th>
            <th></th>
        </tr>
        </thead>
    </table>


    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        {!! Form::open(['route' => ['users.destroy', 'delete'], 'method' => 'delete']) !!} <!-- and invalid ID is intentionally set here -->
        {!! Form::hidden('id', '', ['id' => 'client-id']) !!}
        <div class="modal-dialog" role="document">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header" style="padding:35px 50px;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><span class="glyphicon glyphicon-lock"></span> Handle deletion of user</h4>
                </div>
                <div class="modal-body" style="padding:40px 50px;">

                    <!--HANDLE TASKS-->
                    <div class="form-group">
                        {{ Form::label('user_clients', __('Choose a new user to assign the clients')) }} <span class="glyphicon glyphicon-exclamation-sign text-danger" data-toggle="tooltip" title="Deleting all clients also deletes ALL tasks and leads assigned to that client, regardless of the user they are assigned to."></span>
                        {{ Form::select('user_clients', $users, null, ['class' => 'form-control', 'placeholder' => 'Delete All Clients']) }}
                    </div>

                    <!--HANDLE LEADS-->
                    <div class="form-group">
                        {{ Form::label('user_leads', __('Choose a new user to assign the leads')) }}
                        {{ Form::select('user_leads', $users, null, ['class' => 'form-control', 'placeholder' => 'Delete All Leads']) }}
                    </div>

                    <!--HANDLE CLIENTS-->
                    <div class="form-group">
                        {{ Form::label('user_tasks', __('Choose a new user to assign the tasks')) }}
                        {{ Form::select('user_tasks', $users, null, ['class' => 'form-control', 'placeholder' => 'Delete All Tasks']) }}
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button>
                    <button type="submit" id="confirm_delete" class="btn btn-success"><span class="glyphicon glyphicon-off"></span> Delete</button>
                </div>
            {!! Form::close() !!}
            </div>
        </div>
    </div> 

@stop

@push('scripts')
<script>

$(function () {
    $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{!! route('users.data') !!}',
        columns: [
            {data: 'namelink', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'work_number', name: 'work_number'},
            @if(Entrust::can('user-update'))
                {data: 'edit', name: 'edit', orderable: false, searchable: false},
            @endif
            @if(Entrust::can('user-delete'))
                {data: 'delete', name: 'delete', orderable: false, searchable: false},
            @endif
        ]
    });
});

$(function() {
    $('#myModal').on("show.bs.modal", function (e) {
         $("#client-id").val($(e.relatedTarget).data('client_id'));
    });
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})

</script>
@endpush
