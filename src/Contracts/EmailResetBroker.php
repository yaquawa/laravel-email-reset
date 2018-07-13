<?php

namespace Yaquawa\Laravel\EmailReset\Contracts;

use Illuminate\Foundation\Auth\User;

interface EmailResetBroker
{
    /**
     * Constant representing a successfully reset email.
     *
     * @var string
     */
    public const EMAIL_RESET = 'EmailReset::Reset';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    public const INVALID_TOKEN = 'EmailReset::InvalidToken';

    /**
     * Send a token to user for resetting the user's email.
     *
     * @param User $user
     *
     * @return string
     */
    public function sendToken(User $user): string;

    /**
     * Reset the email for the given token.
     *
     * @param string $token
     *
     * @return mixed
     */
    public function reset(string $token, User $user);
}
