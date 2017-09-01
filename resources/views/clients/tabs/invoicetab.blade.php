<div id="invoice" class="tab-pane fade">
    <div class="boxspace">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Hours') }}</th>
                <th>{{ __('Totalt amount') }}</th>
                <th>{{ __('Invoice sent') }}</th>
                <th>{{ __('Payment received') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
            </thead>
            <tbody>
            <?php $total = 0; ?>

            @foreach($invoices as $invoice)
               <tr>
                    <td>
                        <a href="{{route('invoices.show', $invoice->id)}}">
                            {{$invoice->id}}
                        </a>
                    </td>
                    <td>
                        <?php $total = 0; ?>
                        @foreach($invoice->invoiceLines as $invoiceLine)
                            <?php $total += $invoiceLine->quantity; ?>
                        @endforeach
                        {{$total}}
                    </td>
                    <td>
                        <?php $totalAmount = 0; ?>
                        @foreach($invoice->invoiceLines as $payment)
                            <?php $totalAmount += $payment->price; ?>
                        @endforeach
                        {{$totalAmount}},-
                    </td>
                    <td>
                        @if($invoice->sent_at == null)
                            <?php $color = "red"; ?>
                        @else
                            <?php $color = "green"; ?>
                        @endif
                        <p style=" color:{{$color}}">{{$invoice->sent_at ? 'yes' : 'no'}}</p>
                    </td>
                    <td>
                        @if($invoice->payment_received_at == null)
                            <?php $color = "red"; ?>
                        @else
                            <?php $color = "green"; ?>
                        @endif
                        <p style=" color:{{$color}}">{{$invoice->payment_received_at ? 'yes' : 'no'}}</p>
                    </td>

                    <td>
                        <p>{{ $invoice->status }} </p>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>