<?php

namespace Yaquawa\Laravel\EmailReset;

use Illuminate\Support\Facades\DB;

trait CanResetEmail
{
    /**
     * The new email to be set for.
     * @var string
     */
    protected $newEmail;

    /**
     * Determine if use the new email for notification.
     * @var bool
     */
    protected $useNewEmailForNotification = false;

    /**
     * Notify the user to verify the new email.
     *
     * @see \Yaquawa\Laravel\EmailReset\Notifications\EmailResetNotification for customize the mail contents.
     *
     * @param string $newEmail
     */
    public function resetEmail(string $newEmail): void
    {
        $this->newEmail = $newEmail;
        EmailResetBrokerFactory::broker()->sendToken($this);
    }

    /**
     * @return null|string
     */
    public function getNewEmailAttribute(): ?string
    {
        if ($this->newEmail) {
            return $this->newEmail;
        }

        $record = DB::table(Config::defaultDriverConfig('table'))->where('user_id', $this->getAuthIdentifier())->first();

        if ($record) {
            return $this->newEmail = $record->new_email;
        }

        return null;
    }

    /**
     * @param bool|null $use
     *
     * @return bool
     */
    public function useNewEmailForNotification(bool $use = null): bool
    {
        if ($use) {
            return $this->useNewEmailForNotification = $use;
        }

        return $this->useNewEmailForNotification;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function useNewEmailForNotificationOnce(callable $callback): self
    {
        $this->useNewEmailForNotification(true);

        $callback($this);

        $this->useNewEmailForNotification(false);

        return $this;
    }

    /**
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        if ($this->useNewEmailForNotification && $this->newEmail) {
            return $this->newEmail;
        }

        return $this->email;
    }
}