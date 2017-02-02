<div class="col-lg-6">
    <div class="profilepic"><img class="profilepicsize"
                                 @if($contact->image_path != "")
                                 src="../images/{{$companyname}}/{{$contact->image_path}}"
                                 @else
                                 src="../images/default_avatar.jpg"
                @endif />
    </div>

    <h1>{{ $contact->nameAndDepartment }} </h1>


    <!--MAIL-->
    <p><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
        <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
    <!--Work Phone-->
    <p><span class="glyphicon glyphicon-headphones" aria-hidden="true"></span>
        <a href="tel:{{ $contact->work_number }}">{{ $contact->work_number }}</a></p>

    <!--Personal Phone-->
    <p><span class="glyphicon glyphicon-phone" aria-hidden="true"></span>
        <a href="tel:{{ $contact->personal_number }}">{{ $contact->personal_number }}</a></p>

    <!--Address-->
    <p><span class="glyphicon glyphicon-home" aria-hidden="true"></span>
        {{ $contact->address }}  </p>
</div>