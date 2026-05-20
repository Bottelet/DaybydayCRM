@extends('layouts.master')

@section('content')
   <h1>{{ $role->display_name }} <small>{{ __('Permission management') }}</small></h1>
	<div class="row">
	<div class="col-xs-12">
    <form action="{{ url('roles/update', $role->external_id) }}" method="POST">
        @csrf
        @method('PATCH')
   @foreach($permissions_grouping as $permissions)
   <div class="row">
   @if($permissions->first)
   <div class="col-md-2">
   	<p class="calm-header">{{ucfirst(__($permissions->first()->grouping))}} </p>
   </div>
   @endif
   <div class="col-md-9">
	@foreach($permissions as $permission)
   <div class="col-xs-6 col-md-6">
	    <?php $isEnabled = !current(
    array_filter(
                    $role->permissions->toArray(),
                    function ($element) use ($permission) {
                            return $element['id'] === $permission->id;
                        }
                )
);  ?>
	<div class="white-box">
	    <input type="checkbox"
	    			{{ !$isEnabled ? 'checked' : ''}} name="permissions[ {{ $permission->id }} ]"
	               value="1" data-role="{{ $role->id }}">

	        <span class="perm-name lead"><small>{{ $permission->display_name }}</small></span><br/>
	        {{ $permission->description }}
    </div>
    </div>
    @endforeach
    </div>
    </div>
    <hr>
	@endforeach
	<div class="col-xs-12">
		<input type="submit" value="{{ __('Update Role') }}" class="btn btn-primary">
    </form>
	
	@if($role->name == "owner" || $role->name == "administrator")
	@else
		<form action="{{ route('roles.destroy', $role->id) }}" method="POST">
            @csrf
            @method('DELETE')
            <input type="submit" value="{{ __('Delete') }}" class="btn btn-danger" onclick="return confirm('Are you sure?')">
        </form>
    @endif
	</div>
		<div class="col-sm-12">
			<h3>{{ __('Users with this role') }} ({{ $role->users->count() }}):</h3>
			@foreach($role->users as $user)
				{{ $user->name }} <br />
			@endforeach
		</div>
		</div>
</div>
@endsection