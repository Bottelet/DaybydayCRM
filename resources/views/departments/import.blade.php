{!! Form::open(['route' =>'departments.import', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
@csrf
<div class="form-group">
    <label for="csv_file">Téléchargez un fichier CSV</label>
    {!! Form::file('csv_file', ['class' => 'form-control', 'accept' => '.csv', 'required']) !!}
</div>
{!! Form::submit('Importer', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}