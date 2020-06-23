<table class="table table-hover" id="leads-table">
    <h3>{{ __('Leads assigned') }}</h3>
    <thead>
    <tr>
        <th>{{ __('Title') }}</th>
        <th>{{ __('Assigned') }}</th>
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
        <th><a href="{{route('client.lead.create', $client->external_id)}}" class="btn btn-md btn-brand float-right">@lang('New lead')</a></th>
    </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(function () {
            var table = $('#leads-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{!! route('clients.leadDataTable',  $client->external_id) !!}',
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
                    {data: 'assigned', name: 'assigned', orderable: false, searchable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'deadline', name: 'deadline'},
                    {data: 'status_id', name: 'status.title', orderable: false},
                    {defaultContent: ''}
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