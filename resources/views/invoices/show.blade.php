@extends('layouts.master')

@section('content')
    <div class="row">
        @include('partials.clientheader')
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading panel-header">
                    <h3 class="text-center"><strong>Order summary</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>

                            <tr>
                                <td><strong>Item Name</strong></td>
                                <td class="text-center"><strong>Item Price</strong></td>
                                <td class="text-center"><strong>Hours used</strong></td>
                                <td class="text-right"><strong>Total</strong></td>
                            </tr>

                            </thead>
                            <tbody>
                            <?php $finalPrice = 0;?>
                            @foreach($invoice->taskTime as $item)
                                <?php $totalPrice = $item->time * $item->value ?>
                                <tr>
                                    <td>{{$item->title}}</td>
                                    <td class="text-center">{{$item->value}},-</td>
                                    <td class="text-center">{{$item->time}}</td>
                                    <td class="text-right">{{$totalPrice}},-</td>
                                </tr>
                                <?php $finalPrice += $totalPrice;?>
                            @endforeach

                            <tr>
                                <td class="emptyrow"></i></td>
                                <td class="emptyrow"></td>
                                <td class="emptyrow text-center"><strong>Total</strong></td>
                                <td class="emptyrow text-right">{{$finalPrice}},-</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if(!$invoice->sent)
                <button type="button" class="btn btn-primary form-control" data-toggle="modal"
                        data-target="#ModalTimer">
                    Insert new item
                </button>
            @endif
        </div>

        <div class="col-md-4">
            <div class="sidebarbox">
                <div class="sidebarheader">
                    <p>Invoice information</p>
                </div>
                Invoice sent: {{$invoice->sent ? 'yes' : 'no'}} <br/>
                Payment Received: {{$invoice->received ? 'yes' : 'no'}} <br/>


                @if($invoice->received)
                    {{ date('d-m-Y', strtotime($invoice->payment_date))}}
                @endif
                <br/><br/>

                @if(!$invoice->sent)
                    {!! Form::open([
                    'method' => 'post',
                    'route' => ['invoice.sent', $invoice->id],
                    ]) !!}

                    {!! Form::submit('Set invoice as sent', ['class' => 'btn btn-success form-control closebtn']) !!}
            </div>
            {!! Form::close() !!}
            @else
                {!! Form::open([
                 'method' => 'post',
                 'route' => ['invoice.sent.reopen', $invoice->id],
                 ]) !!}
                {!! Form::submit('Set invoice as not sent', ['class' => 'btn btn-danger form-control closebtn']) !!}
                {!! Form::close() !!}
            @endif



            @if(!$invoice->received)
                <div class="sidebarheader">
                    <p>Invoice paid date</p>
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
    </div>
    </div>
    </div>



    <div class="modal fade" id="ModalTimer" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Time Managment For This Invoice ({{$invoice->title}})</h4>
                </div>

                <div class="modal-body">

                    {!! Form::open([
                    'method' => 'post',
                    'route' => ['invoice.new.item', $invoice->id],
                    ]) !!}

                    <div class="form-group">
                        {!! Form::label('title', 'Title:', ['class' => 'control-label']) !!}
                        {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Fx Consultation Meeting']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('comment', 'Description:', ['class' => 'control-label']) !!}
                        {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => 'Short Comment about whats done(Will show on Invoice)']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('value', 'Hourly price(DKK):', ['class' => 'control-label']) !!}
                        {!! Form::text('value', null, ['class' => 'form-control', 'placeholder' => '300']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('time', 'Time spend (Hours):', ['class' => 'control-label']) !!}
                        {!! Form::text('time', null, ['class' => 'form-control', 'placeholder' => '3']) !!}
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default col-lg-6" data-dismiss="modal">Close</button>
                    <div class="col-lg-6">
                        {!! Form::submit('Register time', ['class' => 'btn btn-success form-control closebtn']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection