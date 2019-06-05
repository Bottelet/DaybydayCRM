<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Auth;
use App\Models\Contact;

class ContactActionNotification extends Notification
{
    use Queueable;

    private $contact;
    private $action;

    /**
     * Create a new notification instance.
     * ContactActionNotification constructor.
     *
     * @param $contact
     * @param $action
     */
    public function __construct($contact, $action)
    {
        $this->contact = $contact;
        $this->action  = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
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
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        switch ($this->action) {
            case 'created':
                $text = __('Contact :company was assigned to you', [
                    'company' => $this->contact->name,
                ]);
                break;
            case 'updated_assign':
                $text = __(':username assigned :company to you', [
                    'company'  => $this->contact->name,
                    'username' => Auth()->user()->name,
                ]);
                break;
            default:
                break;
        }

        return [
            'assigned_user' => $notifiable->id, //Assigned user ID
            'created_user'  => auth()->user()->id,
            'message'       => $text,
            'type'          => Contact::class,
            'type_id'       => $this->contact->id,
            'url'           => url('Contacts/'.$this->contact->id),
            'action'        => $this->action,
        ];
    }
}
