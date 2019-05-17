    <div class="form-group">
        {!! Form::label('name', __('Name'), ['class' => 'control-label']) !!}  <span class="text-danger">*</span>
        {!! Form::text('name', $data['name'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('job_title', __('Job Title'), ['class' => 'control-label']) !!}
        {!! Form::text('job_title', $data['job_title'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('address', __('Address'), ['class' => 'control-label']) !!}
        {!! Form::text('address', $data['address'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('city', __('City'), ['class' => 'control-label']) !!}
        {!! Form::text('city', $data['city'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('zipcode', __('Zip Code'), ['class' => 'control-label']) !!}
        {!! Form::text('zipcode', $data['zipcode'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('email', __('Email'), ['class' => 'control-label']) !!}
        {!! Form::text('email', $data['email'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('primary_number', __('Primary Number'), ['class' => 'control-label']) !!}
        {!! Form::text('primary_number', $data['primary_number'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('secondary_number', __('Secondary Number'), ['class' => 'control-label']) !!}
        {!! Form::text('secondary_number', $data['secondary_number'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        @if(Request::get('client') != "")
            {!! Form::hidden('client_id', Request::get('client')) !!}
        @else
            {!! Form::label('client_id', __('Assign Client'), ['class' => 'control-label']) !!}  <span class="text-danger">*</span>
            {!! Form::select('client_id', $clients, $data['client_id'] ?? null, ['class' => 'form-control']) !!}
        @endif
    </div>

    {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary']) !!}