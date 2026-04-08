<div class="col-sm-3">
    <p class="calm-header">{{ __('Primary contact person')}}</p>
</div>
<div class="col-sm-9" id="primaryContact">
    <div class="form-group">
        <label for="name" class="control-label thin-weight">{{ __('Name') }}:</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', isset($data['owners']) ? $data['owners'][0]['name'] : (isset($client) ? $client->name : '')) }}">
    </div>
    <div class="form-group">
        <label for="email" class="control-label thin-weight">{{ __('Email') }}:</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', isset($data['email']) ? $data['email'] : (isset($client) ? $client->email : '')) }}">
    </div>
    <div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        <label for="primary_number" class="control-label thin-weight">{{ __('Primary number') }}:</label>
        <input type="text" name="primary_number" class="form-control" value="{{ old('primary_number', isset($data['phone']) ? $data['phone'] : (isset($client) ? $client->primary_number : '')) }}">
    </div>

    <div class="form-group col-sm-6 removeleft removeright">
        <label for="secondary_number" class="control-label thin-weight">{{ __('Secondary number') }}:</label>
        <input type="text" name="secondary_number" class="form-control" value="{{ old('secondary_number', isset($client) ? $client->secondary_number : '') }}">
    </div>
</div>
</div>
<hr>

<div class="col-sm-3">
    <p class="calm-header">{{ __('Business information') }}</p>
</div>
<div class="col-sm-9" id="businessInfo">
    <div class="form-inline">
    <div class="form-group col-sm-6 removeleft">
        <label for="vat" class="control-label thin-weight">{{ __('Vat') }}:</label>
        <input type="text" name="vat" class="form-control" value="{{ old('vat', isset($data['vat']) ? $data['vat'] : (isset($client) ? $client->vat : '')) }}">
    </div>

    <div class="form-group col-sm-6 removeleft removeright">
        <label for="company_name" class="control-label thin-weight">{{ __('Company name') }}:</label>
        <input type="text" name="company_name" class="form-control" value="{{ old('company_name', isset($data['name']) ? $data['name'] : (isset($client) ? $client->company_name : '')) }}">
    </div>
</div>
<div class="form-group">
    <label for="address" class="control-label thin-weight">{{ __('Address') }}:</label>
    <input type="text" name="address" class="form-control" value="{{ old('address', isset($data['address']) ? $data['address'] : (isset($client) ? $client->address : '')) }}">
</div>

<div class="form-inline">
    <div class="form-group col-sm-4 removeleft">
        <label for="zipcode" class="control-label thin-weight">{{ __('Zipcode') }}:</label>
        <input type="text" name="zipcode" class="form-control" value="{{ old('zipcode', isset($data['zipcode']) ? $data['zipcode'] : (isset($client) ? $client->zipcode : '')) }}">
    </div>

    <div class="form-group col-sm-8 removeleft removeright">
        <label for="city" class="control-label thin-weight">{{ __('City') }}:</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', isset($data['city']) ? $data['city'] : (isset($client) ? $client->city : '')) }}">
    </div>
</div>
<div class="form-group">
    <label for="company_type" class="control-label thin-weight">{{ __('Company type') }}:</label>
    <input type="text" name="company_type" class="form-control" value="{{ old('company_type', isset($data['companydesc']) ? $data['companydesc'] : (isset($client) ? $client->company_type : '')) }}">
</div>
<div class="form-group">
    <label for="industry" class="control-label thin-weight">{{ __('Industry') }}:</label>
    <select name="industry_id" class="form-control ui search selection top right pointing search-select" id="search-select">
        @foreach($industries as $id => $name)
            <option value="{{ $id }}" {{ old('industry_id', isset($client) ? $client->industry_id : '') == $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>
</div>
<hr>
<div class="col-sm-3">
    <p class="calm-header">{{ __('User') }}</p>
</div>
<div class="col-sm-9" id="assignUser">
    <div class="form-group">
        <label for="user_id" class="control-label thin-weight">{{ __('Assign user') }}:</label>
        <select name="user_id" class="form-control ui search selection top right pointing search-select" id="search-select">
            @foreach($users as $id => $name)
                <option value="{{ $id }}" {{ old('user_id', isset($client) ? $client->user_id : '') == $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<hr>
<div class="col-sm-10">
    <input type="submit" value="{{ $submitButtonText }}" class="btn btn-md btn-brand" id="submitClient">
</div>
<div class="col-sm-2">

</div>