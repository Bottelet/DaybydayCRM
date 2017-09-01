@extends('layouts.master')

@section('content')
    <div class="row">
        @include('partials.clientheader')
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading panel-header">
                    <h3 class="text-center"><strong>{{ __('Order summary') }}</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>

                            <tr>
                                <td><strong>{{ __('Item name') }}</strong></td>
                                <td class="text-center"><strong>{{ __('Item price') }}</strong></td>
                                <td class="text-center"><strong>{{ __('Hours used') }}</strong></td>
                                <td class="text-right"><strong>{{ __('Total') }}</strong></td>
                            </tr>

                            </thead>
                            <tbody>
                            <?php $finalPrice = 0;?>
                            @foreach($invoice->invoiceLines as $item)
                                <?php $totalPrice = $item->quantity * $item->price ?>
                                <tr>
                                    <td>{{$item->title}}</td>
                                    <td class="text-center">{{$item->price}},-</td>
                                    <td class="text-center">{{$item->quantity}}</td>
                                    <td class="text-right">{{$totalPrice}},-</td>
                                </tr>
                                <?php $finalPrice += $totalPrice;?>
                            @endforeach

                            <tr>
                                <td class="emptyrow"></i></td>
                                <td class="emptyrow"></td>
                                <td class="emptyrow text-center"><strong>{{ __('Total') }}</strong></td>
                                <td class="emptyrow text-right">{{$finalPrice}},-</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if(!$invoice->sent_at)
                <button type="button" class="btn btn-primary form-control" data-toggle="modal"
                        data-target="#ModalTimer">
                        {{ __('Insert new item') }}
                    
                </button>
            @endif
        </div>

        <div class="col-md-4">
            <div class="sidebarbox">
                <div class="sidebarheader">
                    <p>Invoice information</p>
                </div>
                {{ __('Invoice sent') }}: {{$invoice->sent_at ? __('yes') : __('no') }} <br/>
                {{ __('Payment Received') }}: {{$invoice->payment_received_at ? __('yes') : __('no') }} <br/>


                @if($invoice->payment_received_at)
                    {{ date('d-m-Y', strtotime($invoice->payment_received_at))}}
                @endif
                <br/><br/>
@if(!$invoice->sent_at)
            <button type="button" class="btn btn-success form-control closebtn" value="add_time_modal" data-toggle="modal" data-target="#SendInvoiceModalConfirm" >
                {{ __('Set invoice as sent') }}
            </button>

                </div>
                 

<div class="modal fade" id="SendInvoiceModalConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                {{ __('Are you sure?') }}
                    </h4>
                    <p>{{ __('Once a invoice has been send, no new invoice lines can be added') }}</p>
                 {!! Form::open([
                    'method' => 'post',
                    'route' => ['invoice.sent', $invoice->id],
                    ]) !!}
                    @if($apiconnected)
                    <p>{{ __('We have found this contact from your, billing integration, do you wish for us to send it to your billing system aswell?') }}</p>
                        @foreach ($contacts as $key => $contact)
                            {!! Form::radio('invoiceContact', $contact['guid']) !!}
                            {{$contact['name']}}
                            <br/>
                        @endforeach
                        {!! Form::label('mail', __('Send mail with invoice to Customer?(Cheked = Yes):'), ['class' => 'control-label']) !!}
                        {!! Form::checkbox('sendMail', true) !!}
                    @endif
                    {!! Form::submit('Set invoice as sent', ['class' => 'btn btn-success form-control closebtn']) !!}
           
            {!! Form::close() !!}
            </div>
            
        </div>
    </div>
</div>
@endif
        @if($invoice->sent_at)

        @if(!$invoice->payment_received_at)
                <div class="sidebarheader">
                    <p>{{ __('Invoice paid date') }}</p>
                </div>
                {!! Form::open([
                'method' => 'post',
                'route' => ['invoice.payment.date', $invoice->id],
                ]) !!}

                {!! Form::date('payment_date', \Carbon\Carbon::now(), ['class' => 'form-control']) !!}

                {!! Form::submit('Set invoice as paid', ['class' => 'btn btn-success form-control closebtn']) !!}
        </div>
        {!! Form::close() !!}
        @else
            {!! Form::open([
             'method' => 'post',
             'route' => ['invoice.payment.reopen', $invoice->id],
             ]) !!}
            {!! Form::submit('Set invoice as not paid', ['class' => 'btn btn-danger form-control closebtn']) !!}
            {!! Form::close() !!}
        @endif
        @endif
    </div>
    </div>
    </div>

@include('invoices._invoiceLineModal', ['title' => $invoice->title, 'id' => $invoice->id, 'type' => 'invoice'])
@endsection