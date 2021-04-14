@extends('layouts.master')


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="tablet tablet--tabs tablet--height-fluid">
                <div class="tablet__head ">
                    <div class="tablet__head-toolbar">
                        @lang('Overdue invoices')
                    </div>
                </div>
                <div class="tablet__body">
                    <table class="table table-hover" id="leads-table">
                    <thead>
                        <tr>
                            <th>{{ __('Invoice number') }}</th>
                            <th>{{ __('Due date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                        <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td><a href="{{route('invoices.show', $invoice->external_id)}}">{{$invoice->invoice_number}}</a></td>
                                <td>{{$invoice->due_at->format(carbonDateWithText())}}</td>
                                <td>{{formatMoney($invoice->totalPrice)}}</td>
                                <td><a href="{{route('invoices.show', $invoice->external_id)}}">@lang('View')</a></td>
                            </tr>
                        @endforeach
                        
                        </tbody>
    
                    </table>
                    @if($invoices->isEmpty())
                        <h3 style="text-align: center;">@lang('No overdue invoices')</h3>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
