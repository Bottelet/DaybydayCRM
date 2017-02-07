<div class="col-md-6">

    <h1 class="moveup">{{$client->name}} ({{$client->company_name}})</h1>

    <!--Client info leftside-->
    <div class="contactleft">
        @if($client->email != "")
                <!--MAIL-->
        <p><span class="glyphicon glyphicon-envelope" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('mail') }}" data-placement="left"> </span>
            <a href="mailto:{{$client->email}}" data-toggle="tooltip" data-placement="left">{{$client->email}}</a></p>
        @endif
        @if($client->primary_number != "")
                <!--Work Phone-->
        <p><span class="glyphicon glyphicon-headphones" aria-hidden="true" data-toggle="tooltip"
                 title=" {{ __('Primary number') }} " data-placement="left"> </span>
            <a href="tel:{{$client->work_number}}">{{$client->primary_number}}</a></p>
        @endif
        @if($client->secondary_number != "")
                <!--Secondary Phone-->
        <p><span class="glyphicon glyphicon-phone" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Secondary number') }}" data-placement="left"> </span>
            <a href="tel:{{$client->secondary_number}}">{{$client->secondary_number}}</a></p>
        @endif
        @if($client->address || $client->zipcode || $client->city != "")
                <!--Address-->
        <p><span class="glyphicon glyphicon-home" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Full address') }}" data-placement="left"> </span> {{$client->address}}
            <br/>{{$client->zipcode}} {{$client->city}}
        </p>
        @endif
    </div>

    <!--Client info leftside END-->
    <!--Client info rightside-->
    <div class="contactright">
        @if($client->company_name != "")
                <!--Company-->
        <p><span class="glyphicon glyphicon-star" aria-hidden="true" data-toggle="tooltip"
                 title="{{ __('Company') }}" data-placement="left"> </span> {{$client->company_name}}</p>
        @endif
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
</div>

<!--Client info rightside END-->
