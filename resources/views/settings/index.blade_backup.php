@extends('layouts.master')
@section('heading')
<h1>Settings</h1>
@stop
@section('content')



<div class="row">
  @foreach($roles as $role) 
  <div class="col-lg-4"> 
  {!! Form::model($permission, [
  'method' => 'PATCH',
  'url'    => 'settings/permissionsUpdate',
  ]) !!}
    <br />  <h4>{{$role->name}} </h4><br />
    <input type="hidden" name="role_id" value="{{ $role->id }}" />
          @foreach($permission as $perm)
                <?php $isEnabled = !current(
            array_filter(
                    $role->permissions->toArray(), 
                    function($element) use($perm) { 
                        return $element['id'] === $perm->id; 
                    }
            )
        );  ?>
                  <input type="checkbox" <?php if (!$isEnabled) echo 'checked' ?> name="permissions[ {{ $perm->id }} ]"  value="1" >
      <span class="perm-name">{{$perm->name}}</span><br />


         
       
    
      <!--<select name="permissions[ {{ $perm->id }} ]" class="form-control">
  
      <option value="1">Allowed</option>
      <option value="0" <?php /*if ($isEnabled) echo 'selected' */?>>Not Allowed</option>
      </select> <br />
-->
      @endforeach

    {!! Form::submit('Save Settings for Role', ['class' => 'btn btn-primary']) !!}
  {!! Form::close() !!}
  </div>
  @endforeach
</div>



  
<div class="row">
    <div class="col-lg-12"><div class="sidebarheader movedown"><p>Overall settings</p></div>

     
     {!! Form::model($settings, [
        'method' => 'PATCH',
        'url' => 'settings/overall'
        ]) !!}
        
         <!-- *********************************************************************
     *                     Task complete       
     *********************************************************************-->
         <div class="panel panel-default movedown">
          <div class="panel-heading">Task completion</div>
          <div class="panel-body">

            If <b>Allowed</b> only user who are assigned the task &amp; the admin can complete the task. <br />
            If <b>Not allowed</b> anyone, can complete all tasks.
          </div>
        </div>
            {!! Form::select('task_complete_allowed', [1 => 'Allowed', 2 => 'Not Allowed'], $settings->task_complete_allowed, ['class' => 'form-control']) !!}
  <!-- *********************************************************************
     *                     Task assign       
     *********************************************************************-->
         <div class="panel panel-default movedown">
          <div class="panel-heading">Task Assigned user</div>
          <div class="panel-body">

            If <b>Allowed</b> only user who are assigned the task &amp; the admin can assign another user. <br />
            If <b>Not allowed</b> anyone, can assign another user.
          </div>
        </div>
            {!! Form::select('task_assign_allowed', [1 => 'Allowed', 2 => 'Not Allowed'], $settings->task_assign_allowed, ['class' => 'form-control']) !!}
  <!-- *********************************************************************
     *                     Lead complete       
     *********************************************************************-->

         <div class="panel panel-default movedown">
          <div class="panel-heading">Lead completion</div>
          <div class="panel-body">

            If <b>Allowed</b> only user who are assigned the task &amp; the admin can complete the Lead. <br />
            If <b>Not allowed</b> anyone, can complete all Leads.
          </div>
        </div>
            {!! Form::select('lead_complete_allowed', [1 => 'Allowed', 2 => 'Not Allowed'], $settings->lead_complete_allowed, ['class' => 'form-control']) !!}
  <!-- *********************************************************************
     *                     Lead assign       
     *********************************************************************-->
         <div class="panel panel-default movedown">
          <div class="panel-heading">Lead Assigned user</div>
          <div class="panel-body">

            If <b>Allowed</b> only user who are assigned the lead &amp; the admin can assign another user. <br />
            If <b>Not allowed</b> anyone, can assign another user.
          </div>
        </div>
         {!! Form::select('lead_assign_allowed', [1 => 'Allowed', 2 => 'Not Allowed'], $settings->lead_assign_allowed, ['class' => 'form-control']) !!}
         <br />
{!! Form::submit('Save Overall Settings', ['class' => 'btn btn-primary']) !!}
           {!! Form::close() !!}
     </div>
</div>
</div>
<div class="row">
        <div class="col-lg-6"><div class="sidebarheader"><p>Change plan</p></div>
        <form action="settings/stripe" method="POST">
        {{ csrf_field() }}
  <script
    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
    data-key="pk_test_nSzUkjVDExrB0xcW2XBzGe9r"
    data-amount="11000"
    data-currency="dkk"
    data-quantity="5"
    data-name="Demo Site"
    data-description="WOOOW"
    data-image="/128x128.png"
    data-locale="auto">
  </script>
</form>

<script type="text/javascript">
  // This identifies your website in the createToken call below
  Stripe.setPublishableKey('pk_test_nSzUkjVDExrB0xcW2XBzGe9r');
  // ...
  // jQuery(function($) {
  $('#payment-form').submit(function(event) {
    var $form = $(this);

    // Disable the submit button to prevent repeated clicks
    $form.find('button').prop('disabled', true);

    Stripe.card.createToken($form, stripeResponseHandler);

    // Prevent the form from submitting with the default action
    return false;
  });
});

function stripeResponseHandler(status, response) {
  var $form = $('#payment-form');

  if (response.error) {
    // Show the errors on the form
    $form.find('.payment-errors').text(response.error.message);
    $form.find('button').prop('disabled', false);
  } else {
    // response contains id and card, which contains additional card details
    var token = response.id;
    // Insert the token into the form so it gets submitted to the server
    $form.append($('<input type="hidden" name="stripeToken" />').val(token));
    // and submit
    $form.get(0).submit();
  }
};
</script>

<form action="settings/stripe" method="POST" id="payment-form">
 {{ csrf_field() }}
  <span class="payment-errors"></span>
  

  <div class="form-row">
    <label>
      <span>Card Number</span>
      <input type="text" size="20" data-stripe="number"/>
    </label>
  </div>

  <div class="form-row">
    <label>
      <span>CVC</span>
      <input type="text" size="4" data-stripe="cvc"/>
    </label>
  </div>

  <div class="form-row">
    <label>
      <span>Expiration (MM/YYYY)</span>
      <input type="text" size="2" data-stripe="exp-month"/>
    </label>
    <span> / </span>
    <input type="text" size="4" data-stripe="exp-year"/>
  </div>

  <button type="submit">Submit Payment</button>
</form>
        </div>

     <div class="col-lg-6"><div class="sidebarheader"><p>Feedback</p></div></div>
</div>
@stop

