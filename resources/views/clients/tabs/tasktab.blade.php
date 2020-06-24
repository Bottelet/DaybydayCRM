<table class="table table-hover" id="tasks-table">
    <h3>{{ __('Tasks assigned') }}</h3>
    <thead>
    <th>{{ __('Title') }}</th>
    <th>{{ __('Assigned') }}</th>
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
    <th><a href="{{route('client.task.create', $client->external_id)}}" class="btn btn-md btn-brand float-right">@lang('New task')</a></th>
    </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(function () {
            var table = $('#tasks-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{!! route('clients.taskDataTable',  $client->external_id) !!}',
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
                    {data: 'assigned', name: 'assigned', orderable: false, searchable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'deadline', name: 'deadline'},
                    {data: 'status_id', name: 'status.title', orderable: false},
                    {defaultContent: ''}
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
    </script>
@endpush