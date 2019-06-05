<div class="col-md-6">

    <h1 class="moveup">{{$client->name}}</h1>

    <!--Client info leftside-->
    <div class="contactleft">
        @if($client->primary_number != "")
            <p><span class="glyphicon glyphicon-phone-alt" aria-hidden="true" data-toggle="tooltip"
                     title=" {{ __('Primary Number') }} " data-placement="left"> </span>
                <a href="tel:{{$client->primary_number}}">{{$client->primary_number}}</a></p>
        @endif
        @if($client->secondary_number != "")
            <p><span class="glyphicon glyphicon-print" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('Secondary/FAX Number') }}" data-placement="left"> </span>
                <a href="tel:{{$client->secondary_number}}">{{$client->secondary_number}}</a></p>
        @endif
        @if($client->primary_email != "")
            <p><span class="glyphicon glyphicon-envelope" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('mail') }}" data-placement="left"> </span>
                <a href="mailto:{{$client->primary_email}}" data-toggle="tooltip" data-placement="left">{{$client->primary_email}}</a></p>
        @endif
        @if($client->vat != "")
                <!--Company-->
        <p><span class="glyphicon glyphicon-cloud" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('vat') }}" data-placement="left"> </span> {{$client->vat}}</p>
        @endif
        @if($client->industry != "")
                <!--Industry-->
        <p><span class="glyphicon glyphicon-briefcase" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Industry') }}"data-placement="left"> </span> {{$client->industry->name}}</p>
        @endif
        @if($client->company_type!= "")
                <!--Company Type-->
        <p><span class="glyphicon glyphicon-globe" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Company type') }}" data-placement="left"> </span>
            {{$client->company_type}}</p>
        @endif
    </div>
    <!--Client info leftside END-->

    <!--Client info rightside-->
    <div class="contactright">
        @if($client->formatted_billing_address)
            <p>
                <span class="glyphicon glyphicon-home" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('Billing Address') }}" data-placement="left"></span> Billing Address
            </p>
            <p>
                {!! $client->formatted_billing_address !!}
            </p>
        @endif
        @if($client->formatted_shipping_address)
            <p>
                <span class="glyphicon glyphicon-home" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('Shipping Address') }}" data-placement="left"></span> Shipping Address
            </p>
            <p>
                {!! $client->formatted_shipping_address !!}
            </p>
        @endif
    </div>
    <!--Client info rightside END-->

</div>
