<div id="document" class="tab-pane fade">
    <table class="table">
        <h4>{{ __('All Documents') }}</h4>
        <div class="col-xs-10">
            <div class="form-group">
                <form method="POST" action="{{ url('/clients/upload', $client->id)}}" class="dropzone" id="dropzone"
                      files="true" data-dz-removea
                      enctype="multipart/form-data"
                >
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                </form>
                <p><b>{{ __('Max size') }}</b></p>
            </div>
        </div>
        <thead>
        <tr>
            <th>{{ __('File') }}</th>
            <th>{{ __('Size') }}</th>
            <th>{{ __('Created at') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($client->documents as $document)
            <tr>
                <td><a href="../files/{{$companyname}}/{{$document->path}}"
                       target="_blank">{{$document->file_display}}</a></td>
                <td>{{$document->size}} <span class="moveright"> MB</span></td>
                <td>{{$document->created_at}}</td>
		
                <td>
		<form method="POST" action="{{action('DocumentsController@destroy', $document->id)}}">
		<input type="hidden" name="_method" value="delete"/>
		<input type="hidden" name="_token" value="{{csrf_token()}}"/>
		<input type="submit" class="btn btn-danger" value="Delete"/>
		</form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
