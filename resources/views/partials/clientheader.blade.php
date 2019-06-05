<div class="col-md-6">

    <h1 class="moveup">{{$client->name}}</h1>

    <!--Client info leftside-->
    <div class="contactleft">
        @if($client->primary_email != "")
            <p><span class="glyphicon glyphicon-envelope" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('mail') }}" data-placement="left"> </span>
                <a href="mailto:{{$client->primary_email}}" data-toggle="tooltip" data-placement="left">{{$client->primary_email}}</a></p>
        @endif
        @if($client->primary_number != "")
            <p><span class="glyphicon glyphicon-headphones" aria-hidden="true" data-toggle="tooltip"
                     title=" {{ __('Primary number') }} " data-placement="left"> </span>
                <a href="tel:{{$client->primary_number}}">{{$client->primary_number}}</a></p>
        @endif
        @if($client->secondary_number != "")
            <p><span class="glyphicon glyphicon-phone" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('Secondary number') }}" data-placement="left"> </span>
                <a href="tel:{{$client->secondary_number}}">{{$client->secondary_number}}</a></p>
        @endif
        @if($client->billing_address1 || $client->billing_zipcode || $client->billing_city != "")
            <p><span class="glyphicon glyphicon-home" aria-hidden="true" data-toggle="tooltip"
                     title="{{ __('Full address') }}" data-placement="left"> </span> {{$client->billing_address1}}
                <br/>{{$client->billing_city}}, {{$client->billing_state}} {{$client->billing_zipcode}}
            </p>
        @endif
    </div>
    <!--Client info leftside END-->

    <!--Client info rightside-->
    <div class="contactright">
        @if($client->vat != "")
                <!--Company-->
        <p><span class="glyphicon glyphicon-cloud" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('vat') }}" data-placement="left"> </span> {{$client->vat}}</p>
        @endif
        @if($client->industry != "")
                <!--Industry-->
        <p><span class="glyphicon glyphicon-briefcase" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Industry') }}"data-placement="left"> </span> {{$client->industry}}</p>
        @endif
        @if($client->company_type!= "")
                <!--Company Type-->
        <p><span class="glyphicon glyphicon-globe" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Company type') }}" data-placement="left"> </span>
            {{$client->company_type}}</p>
        @endif
    </div>
    <!--Client info rightside END-->

</div>
