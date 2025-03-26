@extends('layouts.master')
@section('heading')
    {{__('Import CSV')}} 
@stop

@section('content')

@if(Session::has('flash_message'))
            <message message="{{ Session::get('flash_message') }}" type="success"></message>
        @endif

<form action="{{ route('base.import_csv') }}" method="POST" enctype="multipart/form-data">
@csrf
<label for="file">Sélectionner premier fichier CSV :</label>
<input type="file" name="file" required>
<label for="file">Sélectionner deuxieme fichier CSV :</label>
<input type="file" name="file2" required>
<label for="file">Sélectionner troisieme fichier CSV :</label>
<input type="file" name="file3" required>


<button type="submit">Importer</button>
</form>

@stop


