<table class="table table-hover" id="invoices-table">
    <h3>{{ __('Invoices assigned') }}</h3>
    <thead>
    <tr>
        <th>{{ __('Invoice number') }}</th>
        <th>{{ __('Total amount') }}</th>
        <th>{{ __('Invoice sent') }}</th>
        <th>{{ __('Status') }}</th>
    </tr>
    </thead>
</table>

@push('scripts')
    <script>
        $(function () {
            var table = $('#invoices-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: '{!! route('clients.invoiceDataTable', $client->external_id) !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                drawCallback: function(){
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                columns: [

                    {data: 'invoice_number', name: 'invoice_number'},
                    {data: 'total_amount', name: 'total_amount', searchable: false},
                    {data: 'invoice_sent', name: 'invoice_sent', searchable: false},
                    {data: 'status', name: 'status'},
                ]
            });

        });
    </script>
@endpush