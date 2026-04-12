<div class="form-group">
    <label for="name" class="control-label">Name:</label>
    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
</div>
<div class="form-group">
    <label for="email" class="control-label">Email:</label>
    <input type="email" name="email" class="form-control" value="{{ old('email') }}">
</div>
<div class="form-group">
    <label for="password" class="control-label">Password:</label>
    <input type="password" name="password" class="form-control">
</div>

<div class="form-group">
    <label for="password_confirmation" class="control-label">Password Confirmation:</label>
    <input type="password" name="password_confirmation" class="form-control">
</div>