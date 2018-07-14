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
     * Notify the user to verify the new email.
     *
     * @see \Yaquawa\Laravel\EmailReset\Notifications\EmailResetNotification for customize the mail contents.
     *
     * @param string $newEmail
     */
    public function resetEmail(string $newEmail): void
    {
        $this->newEmail = $newEmail;
        $this->email    = $newEmail;
        EmailResetBrokerFactory::broker()->sendToken($this);
    }

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
}