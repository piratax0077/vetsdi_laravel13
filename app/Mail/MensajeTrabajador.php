<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MensajeTrabajador extends Mailable
{
    public function __construct(public string $centro,public string $asuntoMensaje,public string $textoMensaje) {}

    public function build()
    {
        return $this->subject($this->asuntoMensaje)->view('app.mail.mensaje_trabajador',['centro'=>$this->centro,'textoMensaje'=>$this->textoMensaje]);
    }
}
