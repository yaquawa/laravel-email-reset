<?php

namespace Yaquawa\Laravel\EmailReset;

use Illuminate\Support\Facades\Auth;

trait ResetEmail
{
    public function reset($token)
    {
        $status = EmailResetBrokerFactory::broker()->reset($token, Auth::user());

        if ($status === EmailResetBroker::INVALID_TOKEN) {
            return $this->sendResetFailedResponse($status);
        }

        return $this->sendResetResponse($status);
    }

    /**
     * This method will be called if the token is invalid.
     * Should return a response that representing the token is invalid.
     *
     * @param $status
     *
     * @return mixed
     */
    protected function sendResetFailedResponse(string $status)
    {
        return redirect($this->redirectPathForFailure())->withErrors(['laravel-email-reset' => trans($status)]);
    }

    /**
     * This method will be called if the token is valid.
     * New email will be set for the user.
     * Should return a response that representing the email reset has succeeded.
     *
     * @param $status
     *
     * @return mixed
     */
    protected function sendResetResponse(string $status)
    {
        return redirect($this->redirectPathForSuccess())->with('laravel-email-reset', trans($status));
    }

    /**
     * The redirect path for failure.
     *
     * @return string
     */
    protected function redirectPathForFailure(): string
    {
        return '/';
    }

    /**
     * The redirect path for success.
     *
     * @return string
     */
    protected function redirectPathForSuccess(): string
    {
        return '/';
    }
}