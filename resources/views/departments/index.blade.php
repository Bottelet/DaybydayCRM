@extends('layouts.master')

@section('content')
                <div class="col-lg-12 currenttask">
          
                    <table class="table table-hover">
         <h3>All Departments</h3>
            <thead>
    <thead>
      <tr>
        <th>Title</th>
        <th>Description</th>
		<th>Action</th>
      </tr>
    </thead>
    <tbody>

@foreach($department as $dep)

       <tr>
<td>{{$dep->name}}</td>
<td>{{Str_limit($dep->description, 50)}}</td>

<td>   {!! Form::open([
            'method' => 'DELETE',
            'route' => ['departments.destroy', $dep->id]
        ]); !!}

            {!! Form::submit('Delete', ['class' => 'btn btn-danger', 'onclick' => 'return confirm("Are you sure?")']); !!}

        {!! Form::close(); !!}</td></td>
</tr>
@endforeach

              </tbody>
              </table>

          </div>
         
@stop