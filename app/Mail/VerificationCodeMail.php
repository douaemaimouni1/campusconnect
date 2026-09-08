<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;

    /**
     * @param string $code Le code à 6 chiffres à afficher dans l'email.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Votre code de vérification CampusConnect')
            ->view('emails.verification-code')
            ->text('emails.verification-code-text');
    }
}