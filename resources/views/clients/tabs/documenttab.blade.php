<div id="docuemnt" class="tab-pane fade">
    <table class="table">
        <h4>@lang('client.tabs.all_documents')</h4>
        <div class="col-xs-10">
            <div class="form-group">
                <form method="POST" action="{{ url('/clients/upload', $client->id)}}" class="dropzone" id="dropzone"
                      files="true" data-dz-removea
                      enctype="multipart/form-data"
                >
                    <meta name="csrf-token" content="{{ csrf_token() }}">
                </form>
                <p><b>@lang('client.tabs.max_size')</b></p>
            </div>
        </div>
        <thead>
        <thead>
        <tr>
            <th>@lang('client.tabs.headers.file')</th>
            <th>@lang('client.tabs.headers.size')</th>
            <th>@lang('client.tabs.headers.created_at')</th>

        </tr>
        </thead>
        <tbody>
        @foreach($client->documents as $document)
            <tr>
                <td><a href="../files/{{$companyname}}/{{$document->path}}"
                       target="_blank">{{$document->file_display}}</a></td>
                <td>{{$document->size}} <span class="moveright"> MB</span></td>
                <td>{{$document->created_at}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>