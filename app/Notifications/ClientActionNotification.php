<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;
use Lang;
use App\Models\Client;

class ClientActionNotification extends Notification
{
    use Queueable;

    private $client;
    private $action;

    /**
     * Create a new notification instance.
     * ClientActionNotification constructor.
     * @param $client
     * @param $action
     */
    public function __construct($client, $action)
    {
        $this->client = $client;
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
                $text = __('Client :company was assigned to you', [
                    'company' => $this->client->company_name,
                ]);
                break;
            case 'updated_assign':
                $text = __(':username assigned :company to you', [
                    'company' => $this->client->company_name,
                    'username' => Auth()->user()->name
                ]);
                break;
            default:
                break;
        }

        return [
            'assigned_user' => $notifiable->id, //Assigned user ID
            'created_user' => auth()->user()->id,
            'message' => $text,
            'type' => Client::class,
            'type_id' =>  $this->client->id,
            'url' =>  url('clients/' . $this->client->external_id),
            'action' => $this->action
        ];
    }
}
