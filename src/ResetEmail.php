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
     * @param $status
     *
     * @return mixed
     */
    protected function sendResetFailedResponse(string $status)
    {
        return redirect($this->redirectPathForFailure())->withErrors(['email-reset' => trans($status)]);
    }

    /**
     * @param $status
     *
     * @return mixed
     */
    protected function sendResetResponse(string $status)
    {
        return redirect($this->redirectPathForSuccess())->with('status', trans($status));
    }

    /**
     * @return string
     */
    protected function redirectPathForFailure(): string
    {
        return '/';
    }

    /**
     * @return string
     */
    protected function redirectPathForSuccess(): string
    {
        return '/';
    }
}