<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;
    public $invoiceEmail;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoiceEmail)
    {
        $this->invoiceEmail = $invoiceEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.invoice-notify')
            ->from('notify@penwebpos.com')
            ->subject($this->invoiceEmail["type"])
        ;
    }
}
