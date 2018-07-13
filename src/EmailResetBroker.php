<?php

namespace Yaquawa\Laravel\EmailReset;

use Illuminate\Foundation\Auth\User;
use Yaquawa\Laravel\TokenRepository\Contracts\TokenRepository;
use Yaquawa\Laravel\EmailReset\Notifications\EmailResetNotification;
use Yaquawa\Laravel\EmailReset\Contracts\EmailResetBroker as EmailResetBrokerInterface;

class EmailResetBroker implements EmailResetBrokerInterface
{
    /**
     * The password token repository.
     *
     * @var TokenRepository
     */
    protected $tokens;


    /**
     * Create a new email reset broker instance.
     *
     * @return void
     */
    public function __construct(TokenRepository $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * Send a password reset link to a user.
     *
     * @param User $user
     *
     * @return string
     */
    public function sendToken(User $user): string
    {
        $token = $this->createToken($user);

        $user->notify(
            new EmailResetNotification($token, $user)
        );

        return $token;
    }

    /**
     * Reset the password for the given token.
     *
     * @param string $token
     *
     * @return string
     */
    public function reset(string $token, User $user): string
    {
        $record = $this->findToken($user, $token);

        if ( ! $record) {
            return static::INVALID_TOKEN;
        }

        $this->resetEmail($user, $record['new_email']);

        $this->deleteToken($user);

        return static::EMAIL_RESET;
    }


    /**
     * Reset the given user's password.
     *
     * @param  User $user
     * @param string $newEmail
     *
     * @return void
     */
    protected function resetEmail(User $user, string $newEmail)
    {
        $user->email = $newEmail;

        $user->save();
    }

    /**
     * Create a new password reset token for the given user.
     *
     * @param  User $user
     *
     * @return string
     */
    public function createToken(User $user)
    {
        return $this->tokens->create($user);
    }

    /**
     * Delete password reset tokens of the given user.
     *
     * @param  User $user
     *
     * @return void
     */
    public function deleteToken(User $user)
    {
        $this->tokens->delete($user);
    }

    /**
     * Validate the given password reset token.
     *
     * @param User $user
     * @param  string $token
     *
     * @return bool
     */
    public function findToken(User $user, string $token)
    {
        return $this->tokens->find($user, $token);
    }
}
