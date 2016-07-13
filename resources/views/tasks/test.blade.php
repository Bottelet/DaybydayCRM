    @foreach($tasks as $task)
      
                         <tr>
              <td>
              <a href="{{ route('tasks.show', $task->id)}}">
              {{ $task->title }}
              </a> </td>
              <td>{{date('d, F Y, H:i:s', strTotime($task->created_at))}} </td>
              <td>{{date('d, F Y', strTotime($task->deadline))}}({{ $task->days_until_deadline }})</td>
              </tr>

    @endforeach
    {!! $tasks->links()!!}