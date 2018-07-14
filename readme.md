## How to install
To get started, follow the following steps.

#### Installation

`composer require yaquawa/laravel-email-reset`

#### Configuration

Add the following code to your `<config/auth.php>` file.

```php
'defaults' => [
    'guard'       => 'web',
    'passwords'   => 'users',
    'email-reset' => 'default' // Add this line
],

// Add this entire block
'email-reset' => [
    'default' => [
        'table'  => 'email_resets',
        'expire' => 60,
        // 'ignore-migrations' => true,
    ]
]
```

#### Migration 
 
`php artisan migrate`

If you would like to use your own migration, set `ignore-migrations` to `true` in the config file.

#### Publish the assets

The following command publishes the package's controller and translation files to your app's directories.

`php artisan vendor:publish --tag=laravel-email-reset`

| Asset        | Location                                             |
| ------------ | ---------------------------------------------------- |
| Controller   | `app/Http/Controllers/Auth/ResetEmailController.php` |
| Translations | `resources/lang/vendor/laravel-email-reset`          |

#### Use the `CanResetEmail` trait

In your `app/Models/User.php` file, use the `CanResetEmail` trait. This trait adds a `resetEmail` method to the user model.

```php
namespace App\Models;

use Yaquawa\Laravel\EmailReset\CanResetEmail;

class User extends Authenticatable
{
    use CanResetEmail;
}
```

## Usage

### Send the verification email

```php
// By calling the `resetEmail` method of `User` instance,
// an verification email will be sent to the user's current email address.
// If the user clicked the verification link, the new email address will be set. 
 
$user->resetEmail('new_email@example.com');
```

If you want to change the email contents, you can do something like this in your `AppServiceProvider.php`.

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

After the user clicked the verification link, by default, the user will be redirected to the root of your app URL.
You can change this behavior by overriding the methods of the published controller `ResetEmailController`.

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
```

### Retrieve the "new email"

The new email won't be saved until the user clicks the verification link.
Before user clicking the verification link you can get the new email by:

```php
$user->new_email; // retrieve the `new_email` from database
```