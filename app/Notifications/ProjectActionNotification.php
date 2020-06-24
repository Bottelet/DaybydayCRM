<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Lang;
use App\Models\Task;

class ProjectActionNotification extends Notification
{
    use Queueable;


    private $project;
    private $action;

    /**
     * Create a new notification instance.
     * ProjectActionNotification constructor.
     * @param $project
     * @param $action
     */
    public function __construct($project, $action)
    {
        $this->project = $project;
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
                $text = __(':title was created by :creator, and assigned to you', [
                    'title' =>  $this->project->title,
                    'creator' => $this->project->creator->name,
                    ]);
                break;
            case 'updated_status':
                $text = __(':title was completed by :username', [
                    'title' =>  $this->project->title,
                    'username' =>  Auth()->user()->name,
                    ]);
                break;
            case 'updated_time':
                $text = __(':username inserted a new time for :title', [
                    'title' =>  $this->project->title,
                    'username' =>  Auth()->user()->name,
                    ]);
                break;
            case 'updated_assign':
                $text = __(':username assigned a project to you', [
                    'title' =>  $this->project->title,
                    'username' =>  Auth()->user()->name,
                    ]);
                break;
            case 'updated_deadline':
                $text = __(':username updated the deadline for this :title', [
                'title' => $this->project->title,
                'username' =>  Auth()->user()->name
                ]);
                break;
            default:
                break;
        }
        return [
            'assigned_user' => $notifiable->id, //Assigned user ID
            'created_user' => $this->project->creator->id,
            'message' => $text,
            'type' =>  Project::class,
            'type_id' =>  $this->project->id,
            'url' => url('projects/' . $this->project->external_id),
            'action' => $this->action
        ];
    }
}
