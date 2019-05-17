<div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        {!! Form::label('company_name', __('Company name'), ['class' => 'control-label']) !!} <span class="text-danger">*</span>
        {!! Form::text('company_name', $data['company_name'] ?? null, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group col-sm-6 removeleft removeright">
        {!! Form::label('vat', __('Vat'), ['class' => 'control-label']) !!}
        {!! Form::text('vat', $data['vat'] ?? null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('primay_contact_name', __('Primary Contact'), ['class' => 'control-label']) !!}
    {!! Form::text('primary_contact_name', $data['primary_contact_name'] ?? null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('email', __('Email'), ['class' => 'control-label']) !!}
    {!! Form::email('email', $data['email'] ?? null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('address', __('Address'), ['class' => 'control-label']) !!}
    {!! Form::text('address', $data['address'] ?? null, ['class' => 'form-control']) !!}
</div>

<div class="form-inline">
    <div class="form-group col-sm-4 removeleft">
        {!! Form::label('zipcode', __('Zipcode'), ['class' => 'control-label']) !!}
        {!! Form::text('zipcode',  $data['zipcode'] ?? null,  ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-sm-8 removeleft removeright">
        {!! Form::label('city', __('City'), ['class' => 'control-label']) !!}
        {!! Form::text('city', $data['city'] ?? null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        {!! Form::label('primary_number', __('Primary number'), ['class' => 'control-label']) !!}
        {!! Form::text('primary_number', $data['phone'] ?? null, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group col-sm-6 removeleft removeright">
        {!! Form::label('secondary_number', __('Secondary number'), ['class' => 'control-label']) !!}
        {!! Form::text('secondary_number', $data['secondary_number'] ?? null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('company_type', __('Company type'), ['class' => 'control-label']) !!}
    {!! Form::text('company_type', $data['company_type'] ?? null, ['class' => 'form-control']) !!}
</div>

<div class="form-group">
    {!! Form::label('industry', __('Industry'), ['class' => 'control-label']) !!}  <span class="text-danger">*</span>
    {!! Form::select('industry_id', $industries, $data['industry_id'] ?? null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select', 'placeholder' => 'Select an industry...']) !!}
</div>

<div class="form-group">
    {!! Form::label('user_id', __('Assign user'), ['class' => 'control-label']) !!}
    {!! Form::select('user_id', $users, $data['user_id'] ?? null, ['class' => 'form-control ui search selection top right pointing search-select', 'id' => 'search-select', 'placeholder' => 'Assign a user...']) !!}
</div>

{!! Form::submit($submitButtonText, ['class' => 'btn btn-primary']) !!}