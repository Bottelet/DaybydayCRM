<table class="table table-hover dataTable no-footer" id="payments-table">
    <h3>{{ __('Payments') }}</h3>
    <thead>
    <tr>
        <th>{{ __('Payment date') }}</th>
        <th>{{ __('Payment source') }}</th>
        <th>{{ __('Amount') }}</th>
        <th>{{ __('Description') }}</th>
        <th></th>
    </tr>
    </thead>
</table>

@push('scripts')
    <style type="text/css">
        .table > tbody > tr > td {
            border-top:none !important;
        }
        .table-actions {
            opacity: 0;
        }
        #payments-table tbody tr:hover .table-actions{
            opacity: 1;
        }
    </style>
    <script>
        $(function () {
            var table = $('#payments-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                bFilter:false,
                bPaginate: false,
                ajax: '{!! route('invoice.paymentsDataTable', $invoice->external_id) !!}',
                language: {
                    url: '{{ asset('lang/' . (in_array(\Lang::locale(), ['dk', 'en']) ? \Lang::locale() : 'en') . '/datatable.json') }}'
                },
                drawCallback: function(){
                    var length_select = $(".dataTables_length");
                    var select = $(".dataTables_length").find("select");
                    select.addClass("tablet__select");
                },
                columns: [

                    {data: 'payment_date', name: 'payment_date', searchable: false},
                    {data: 'payment_source', name: 'payment_source', searchable: false},
                    {data: 'amount', name: 'amount', searchable: false},
                    {data: 'description', name: 'description', orderable: false, searchable: false},
                    @if(Entrust::can('payment-delete'))
                    { data: 'delete', name: 'delete', orderable: false, searchable: false, class:'fit-action-delete-th table-actions'},
                    @endif
                ]
            });

        });
    </script>
@endpush