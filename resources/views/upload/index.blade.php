@extends('layouts.master')
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upload csv</title>
</head>
@section('content')
<body>

{!!Form::open(['route'=>'import.import','method'=>'POST','enctype'=>'multipart/form-data'])!!}
@csrf
<h4>Upload csv</h4>
<div class="form-group">
    <label>File 1</label>
    {!! Form::file('file1',['class' => 'form-control', 'accept' => '.csv', 'required']) !!}
    <label>File 2</label>
    {!! Form::file('file2',['class'=>'form-control','accept'=>'.csv']) !!}
    <label>File 3</label>
    {!! Form::file('file3',['class'=>'form-control','accept'=>'.csv']) !!}
    {!! Form::submit('Download', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
</div>
</body>
@endsection
</html>