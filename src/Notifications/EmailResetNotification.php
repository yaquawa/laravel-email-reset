<?php

namespace Yaquawa\Laravel\EmailReset\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Auth\Authenticatable;
use Yaquawa\Laravel\EmailReset\EmailResetBrokerFactory;

class EmailResetNotification extends Notification
{
    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * @var Authenticatable
     */
    public $user;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;


    /**
     * EmailResetNotification constructor.
     *
     * @param string $token
     * @param Authenticatable $user
     */
    public function __construct(string $token, Authenticatable $user)
    {
        $this->token = $token;
        $this->user  = $user;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed $notifiable
     *
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $driver    = EmailResetBrokerFactory::getDefaultDriver();
        $config    = EmailResetBrokerFactory::getConfig($driver);
        $route     = $config['route'] ?? 'email/reset/{token}';
        $resetLink = str_replace('{token}', $this->token, $route);

        if (static::$toMailCallback) {
            return \call_user_func(static::$toMailCallback, $notifiable, $this->token, $resetLink);
        }

        return (new MailMessage)
            ->line('You are receiving this email because we received a email reset request for your account.')
            ->action('Reset Email', url(config('app.url') . route('email-reset', $this->token, false)))
            ->line('If you did not request a email reset, no further action is required.');
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  callable $callback
     *
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
