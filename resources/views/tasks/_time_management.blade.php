<table class="table table_wrapper ">
    <tr>
        <th>{{ __('Title') }}</th>
        <th>{{ __('Time') }}</th>
        <th>{{ __('Type') }}</th>
    </tr>
    <tbody>
    @foreach($invoice_lines as $invoice_line)
        <tr>
            <td style="padding: 5px">{{$invoice_line->title}}</td>
            <td style="padding: 5px">{{$invoice_line->quantity}} </td>
            <td>{{$invoice_line->type}}</td>
        </tr>
    @endforeach

    </tbody>
</table>