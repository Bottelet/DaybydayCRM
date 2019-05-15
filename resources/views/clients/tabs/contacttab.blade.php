<div id="contact" class="tab-pane fade in active" role="tabpanel">
    <div class="boxspace">
        <table class="table table-striped">
            <h4>{{ __('All Contacts') }}</h4>
            <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Job Title') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Primary Number') }}</th>
                <th>
                    <a href="{{ route('contacts.create', ['client' => $client->id])}}">
                        <button class="btn btn-xs btn-success">{{ __('New Contact') }}</button>
                    </a>
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($client->contacts as $contact)
                <tr>
                    <td>
                        <a href="{{ route('contacts.show', $contact->id) }}">{{$contact->name}}</a>
                    </td>
                    <td>
                        {{$contact->job_title}}
                    </td>
                    <td>
                        <a href="mailto:{{$contact->email}}">{{$contact->email}}</a>
                    </td>
                    <td>
                        <a href="tel:{{$contact->primary_number}}">{{$contact->primary_number}}</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>