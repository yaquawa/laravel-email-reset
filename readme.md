## How to install
To get started, follow the following steps.

#### Installation

`composer require yaquawa/laravel-email-reset`

#### Configuration

Add the following code to your `<config/auth.php>`.

```php
'defaults' => [
    'guard'       => 'web',
    'passwords'   => 'users',
    'email-reset' => 'default' // Add this line
]

// Add this entire block
'email-reset' => [
    'default' => [
        'table'  => 'email_resets',
        'expire' => 60,
        // 'ignore-migrations' => true,
    ],
]
```

#### Migration 
 
`php artisan migrate`

If you would like use your own migration, set `ignore-migrations` to `true` in the config file.

#### Publish the controller

`php artisan vendor:publish --tag=email-reset-controllers`

#### Use the `CanResetEmail` trait

In your `app/Models/User.php` file, use the `CanResetEmail` trait.

```php
namespace App\Models;

use Yaquawa\Laravel\EmailReset\CanResetEmail;

class User extends Authenticatable
{
    use CanResetEmail;
}
```

## Usage

```php
// By calling the `resetEmail` method of `User` instance,
// an verification email will be sent to the user's current email address.
// If the user clicked the verification link, the new email address will be set. 
 
$user->resetEmail('new_email@example.com');
```

If you want to change the email content, you can do something like this in your `AppServiceProvider.php`.

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Yaquawa\Laravel\EmailReset\Notifications\EmailResetNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        EmailResetNotification::toMailUsing(function ($user, $token, $resetLink) {
            return (new MailMessage)
                ->line('You are receiving this email because we received a email reset request for your account.')
                ->action('Reset Email', $resetLink)
                ->line('If you did not request a email reset, no further action is required.');
        });
    }
}
```

After the user clicked the verification link, by default the user will be redirected to the root of your app url.
You can change this behavior by modify the published controller.

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Yaquawa\Laravel\EmailReset\ResetEmail;

class ResetEmailController extends Controller
{
    use ResetEmail;

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
        return redirect($this->redirectPathForFailure())->withErrors(['email-reset' => trans($status)]);
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
        return redirect($this->redirectPathForSuccess())->with('status', trans($status));
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
```
