<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Equipo;

class SolicitudUnirseEquipo extends Notification
{
    use Queueable;

    private $user;
    private $equipo_id;
    private $notificacion_id;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $equipo_id, $notificacion_id)
    {
        $this->user = $user;
        $this->equipo_id = $equipo_id;
        $this->notificacion_id = $notificacion_id;
    }
    

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    $equipo = Equipo::find($this->equipo_id);
    return (new MailMessage)
                ->subject('Solicitud de unión a equipo')
                ->line('El usuario ' . $this->user->name .''. $this->user->apellidos . ' ha solicitado unirse a tu equipo ' . $equipo->nombre)
                ->action('Aceptar Solicitud', route('equipo.aceptarSolicitud', ['user' => $this->user, 'equipo_id' => $this->equipo_id,'notificacion_id' => $this->notificacion_id]))
                ->line('¡Gracias por usar nuestra aplicación!');
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
