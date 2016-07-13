<ul class="list-group">
    @foreach ($folders as $folder)
        @php($isChecked = $folder['check'])
        @php($cssClass = $isChecked ? 'list-group-item-success' : 'list-group-item-danger')
        @php($icon = $isChecked ? 'fa-check-circle' : 'fa-times-circle')
        <li class="list-group-item {{ $cssClass }}">
            <span class="fa {!! $icon !!}"></span>&nbsp;&nbsp;{!! $folder['label'] !!}
        </li>
    @endforeach
</ul>