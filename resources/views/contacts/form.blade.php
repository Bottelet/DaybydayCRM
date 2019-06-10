    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('name', __('Name'), ['class' => 'control-label']) !!}  <span class="text-danger">*</span>
            {!! Form::text('name', $data['name'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('job_title', __('Job Title'), ['class' => 'control-label']) !!}
            {!! Form::text('job_title', $data['job_title'] ?? null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {!! Form::label('address1', __('Address 1'), ['class' => 'control-label']) !!}
            {!! Form::text('address1', $data['address1'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-12">
            {!! Form::label('address2', __('Address 2'), ['class' => 'control-label']) !!}
            {!! Form::text('address2', $data['address2'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-4 col-sm-6">
            {!! Form::label('city', __('City'), ['class' => 'control-label']) !!}
            {!! Form::text('city', $data['city'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-4 col-sm-6">
            {!! Form::label('state', __('State/Region'), ['class' => 'control-label']) !!}
            {!! Form::text('state', $data['state'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-4 col-sm-6">
            {!! Form::label('zipcode', __('Zip Code'), ['class' => 'control-label']) !!}
            {!! Form::text('zipcode', $data['zipcode'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-12 col-sm-6">
            {!! Form::label('country', __('Country'), ['class' => 'control-label']) !!}
            {!! Form::text('country', $data['country'] ?? null, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {!! Form::label('email', __('Email'), ['class' => 'control-label']) !!}
            {!! Form::text('email', $data['email'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('primary_number', __('Primary Number'), ['class' => 'control-label']) !!}
            {!! Form::text('primary_number', $data['primary_number'] ?? null, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('secondary_number', __('Secondary Number'), ['class' => 'control-label']) !!}
            {!! Form::text('secondary_number', $data['secondary_number'] ?? null, ['class' => 'form-control']) !!}
        </div>
    </div>
        @if(Request::get('client') != "")
            {!! Form::hidden('client_id', Request::get('client')) !!}
        @else
            <div class="row">
                <div class="form-group col-md-12">
                    {!! Form::label('client_id', __('Assign Client'), ['class' => 'control-label']) !!}  <span class="text-danger">*</span>
                    {!! Form::select('client_id', $clients, $data['client_id'] ?? null, ['class' => 'form-control']) !!}
                </div>
            </div>
        @endif

    {!! Form::submit($submitButtonText, ['class' => 'btn btn-primary']) !!}