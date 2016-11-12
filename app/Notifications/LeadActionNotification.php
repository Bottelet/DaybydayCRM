<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;

class LeadActionNotification extends Notification
{
    use Queueable;

    private $lead;
    private $action;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($lead, $action)
    {
        $this->lead = $lead;
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
        /*return (new MailMessage)
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
                $text = $this->lead->title .
                ' was created by '. $this->lead->createdBy->name .
                ' and assigned to you';
                break;
            case 'updated_status':
                $text = $this->lead->title . ' was completed by '. Auth()->user()->name;
                break;
            case 'updated_deadline':
                $text = Auth()->user()->name.' updated the deadline for ' . $this->lead->title;
                break;
            case 'updated_assign':
                $text = auth()->user()->name.' Assigned a lead to you';
                break;
            default:
                break;
        }
        return [
            'assigned_user' => $notifiable->id, //Assigned user ID
            'created_user' => $this->lead->fk_user_id_created,
            'message' => $text,
            'type' => 'lead',
            'type_id' =>  $this->lead->id,
            'url' => url('leads/' . $this->lead->id),
            'action' => $this->action
        ];
    }
}