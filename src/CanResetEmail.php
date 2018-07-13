<?php

namespace Yaquawa\Laravel\EmailReset;

trait CanResetEmail
{
    /**
     * The new email to be set for.
     * @var string
     */
    protected $newEmail;

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

    public function newEmail(): string
    {
        return $this->newEmail;
    }
}