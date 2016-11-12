<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Lang;

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
                $text = lang::get('misc.notifications.task.created', [
                    'title' =>  $this->task->title,
                    'creator' => $this->task->taskCreator->name,
                    ]);
                break;
            case 'updated_status':
                $text = lang::get('misc.notifications.task.status', [
                    'title' =>  $this->task->title,
                    'username' =>  Auth()->user()->name,
                    ]); 
                break;
            case 'updated_time':
                $text = lang::get('misc.notifications.task.time', [
                    'title' =>  $this->task->title,
                    'username' =>  Auth()->user()->name,
                    ]);
                break;
            case 'updated_assign':
                $text = lang::get('misc.notifications.task.assign', [
                    'title' =>  $this->task->title,
                    'username' =>  Auth()->user()->name,
                    ]);
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
