<div class="col-md-6">
    <div class="panel panel-primary contact-header-box">
        <div class="panel-body">
           @if(\Route::getCurrentRoute()->getName() != "clients.show")
                <a href="{{route('clients.show', $client->external_id)}}"><i class="ion ion-ios-redo " title="{{ __('Go to client') }}" style="
                    float: right;
                    margin-right: 1em;
                    color:#61788b;
                    "></i></a>
            @endif
            <p class="client-company-text" title="{{ __('Company name') }}">{{$client->company_name}} <span aria-hidden="true" data-toggle="tooltip" title="{{ __('Client number') }}" data-placement="top" style="font-size:10px;"> | {{$client->client_number}}</span> 

            </p>
            <!--Client info leftside-->
            <div class="contactleft">
                <p class="client-name-text"  aria-hidden="true" data-toggle="tooltip"
                   title="{{ __('Contact person name') }}" data-placement="left"> {{$contact_info->name}}</p>
            @if($contact_info->email != "")
                <!--MAIL-->
                    <p class="contact-paragraph">
                        <a href="mailto:{{$contact_info->email}}"  aria-hidden="true" data-toggle="tooltip"
                           title="{{ __('Email') }}" data-placement="left">{{$contact_info->email}}</a></p>
            @endif
            @if($contact_info->primary_number != "")
                <!--Work Phone-->
                    <p class="contact-paragraph">
                        <a href="tel:{{$contact_info->primary_number}}"  aria-hidden="true" data-toggle="tooltip"
                           title="{{ __('Primary number') }}" data-placement="left">{{$contact_info->primary_number}}</a>
            @endif
            @if($contact_info->primary_number != "" && $contact_info->secondary_number != "")
                /
            @endif
            @if($contact_info->secondary_number != "")
                <!--Secondary Phone-->
                        <a href="tel:{{$contact_info->secondary_number}}"  aria-hidden="true" data-toggle="tooltip"
                           title="{{ __('Secondary number') }}" data-placement="left">{{$contact_info->secondary_number}}</a>
                    </p>
            @endif
            </div>

            <!--Client info leftside END-->
            <!--Client info rightside-->
            <div class="contactright">
            @if($client->address || $client->zipcode || $client->city != "")
                <!--Address-->
                    <p class="contact-paragraph"  aria-hidden="true" data-toggle="tooltip"
                       title="{{ __('Company address') }}" data-placement="left"> {{$client->address}}
                        <br/>{{$client->zipcode}} {{$client->city}}
                    </p>
            @endif
            @if($client->company_name != "")
                <!--Company-->
                    <p class="contact-paragraph"  aria-hidden="true" data-toggle="tooltip"
                       title="{{ __('Company name') }}" data-placement="left">{{$client->company_name}}</p>
            @endif
            @if($client->vat != "")
                <!--Company-->
                    <p class="contact-paragraph" aria-hidden="true" data-toggle="tooltip"
                        title="{{ __('Vat') }}" data-placement="left">{{$client->vat}}</p>
            @endif
            @if($client->industry != "")
                <!--Industry-->
                    <p class="contact-paragraph" aria-hidden="true" data-toggle="tooltip"
                       title="{{ __('Industry') }}" data-placement="left">{{$client->industry}}</p>
            @endif
            @if($client->company_type!= "")
                <!--Company Type-->
                    <p class="contact-paragraph" aria-hidden="true" data-toggle="tooltip"
                       title="{{ __('Company type') }}" data-placement="left">
                        {{$client->company_type}}</p>
                @endif
            </div>
        </div>
    </div>
</div>
