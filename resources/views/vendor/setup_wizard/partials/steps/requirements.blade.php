<ul class="list-group">
    @foreach ($requirements as $requirement)
        @php($isChecked = $requirement['check'])
        @php($cssClass = $isChecked ? 'list-group-item-success' : 'list-group-item-danger')
        @php($icon = $isChecked ? 'fa-check-circle' : 'fa-times-circle')
        <li class="list-group-item {{ $cssClass }}">
            <span class="fa {!! $icon !!}"></span>&nbsp;&nbsp;{!! $requirement['label'] !!}
        </li>
    @endforeach
</ul>