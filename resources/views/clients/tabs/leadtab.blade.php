<div id="lead" class="tab-pane fade">
    <div class="boxspace">
        <table class="table table-hover">
            <h4>{{ __('All Leads') }}</h4>
            <thead>
            <thead>
            <tr>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Assigned user') }}</th>
                <th>{{ __('Created at') }}</th>
                <th>{{ __('Deadline') }}</th>

                <th><a href="{{ route('leads.create', ['client' => $client->id])}}">
                        <button class="btn btn-success">{{ __('New Lead') }}</button>
                    </a></th>

            </tr>
            </thead>
            <tbody>
            <?php  $tr = ""; ?>
          
            @foreach($client->leads as $lead)
                @if($lead->status == 1)
                    <?php  $tr = '#adebad'; ?>
                @elseif($lead->status == 2)
                    <?php $tr = '#ff6666'; ?>
                @endif
                <tr style="background-color:<?php echo $tr;?>">

                    <td><a href="{{ route('leads.show', $lead->id) }}">{{$lead->title}} </a></td>
                    <td>
                        <div class="popoverOption"
                             rel="popover"
                             data-placement="left"
                             data-html="true"
                             data-original-title="<span class='glyphicon glyphicon-user' aria-hidden='true'> </span> {{$lead->user->name}}">
                            <div id="popover_content_wrapper" style="display:none; width:250px;">
                                <img src='http://placehold.it/350x150' height='80px' width='80px'
                                     style="float:left; margin-bottom:5px;"/>
                                <p class="popovertext">
                                    <span class="glyphicon glyphicon-envelope" aria-hidden="true"> </span>
                                    <a href="mailto:{{$lead->user->email}}">
                                        {{$lead->user->email}}<br/>
                                    </a>
                                    <span class="glyphicon glyphicon-headphones" aria-hidden="true"> </span>
                                    <a href="mailto:{{$lead->user->work_number}}">
                                    {{$lead->user->work_number}}</p>
                                </a>

                            </div>
                            <a href="{{route('users.show', $lead->user->id)}}"> {{$lead->user->name}}</a>

                        </div> <!--Shows users assigned to lead -->
                    </td>
                    <td>{{date('d, M Y, H:i', strTotime($lead->cotact_date))}}  </td>
                    <td>{{date('d, M Y', strTotime($lead->contact_date))}}
                        @if($lead->status == 1)({{ $lead->days_until_contact }})@endif </td>
                    <td></td>
                </tr>

            @endforeach

            </tbody>
        </table>
    </div>
</div>