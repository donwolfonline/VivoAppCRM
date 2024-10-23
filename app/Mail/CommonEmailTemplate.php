<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Utility;
use App\Models\User;

class CommonEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $template;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $settings)
    {
        $this->template = $template;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // if (\Auth::user()->creatorId()) {
        //     return $this->from($this->settings['mail_from_address'], $this->template->from)->markdown('email.common_email_template')->subject($this->template->subject)->with('content', $this->template->content);
        // } else {
        //     return $this->from($this->settings['company_email'], $this->template->from)->markdown('email.common_email_template')->subject($this->template->subject)->with('content', $this->template->content);
        // }

        return $this->from($this->settings['mail_from_address'], $this->settings['mail_from_name'])->markdown('email.common_email_template')->subject($this->template->subject)->with('content', $this->template->content);
    }
}
