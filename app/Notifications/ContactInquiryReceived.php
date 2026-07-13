<?php

namespace App\Notifications;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactInquiryReceived extends Notification
{
    use Queueable;

    public function __construct(public ContactInquiry $inquiry)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'contact_inquiry',
            'title' => 'New public contact inquiry',
            'message' => $this->inquiry->subject,
            'contact_inquiry_id' => $this->inquiry->id,
            'category' => $this->inquiry->category,
            'email' => $this->inquiry->email,
        ];
    }
}
