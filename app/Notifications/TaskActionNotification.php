<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskActionNotification extends Notification
{
    use Queueable;


    private $task;
    private $action;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task, $action)
    {
        $this->task = $task;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
       /* return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', 'https://laravel.com')
                    ->line('Thank you for using our application!'); */
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        switch ($this->action) {
            case 'created':
                $text = $this->task->title .
                ' was created by '. $this->task->taskCreator->name .
                ' and assigned to you';
                break;
            case 'updated_status':
                $text = 'Task was completed by '. Auth()->user()->name;
                break;
            case 'updated_time':
                $text = Auth()->user()->name.' Inserted a new time for ' . $this->task->title;
                break;
            case 'updated_assign':
                $text = auth()->user()->name.' Assigned a task to you';
                break;
            default:
                break;
        }
        return [
            'assigned_user' => $notifiable->id, //Assigned user ID
            'created_user' => $this->task->fk_user_id_created,
            'message' => $text,
            'type' => 'task',
            'type_id' =>  $this->task->id,
            'url' => url('tasks/' . $this->task->id),
            'action' => $this->action
        ];
    }
}
