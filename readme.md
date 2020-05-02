Laravel package to reset users emails
1. send verification link to user's new email
2. on click on verification link, new email is verified and changed

## Installation process
Follow few installation steps to set up vendor.

### Prepare your App
Edit `config/auth.php` config file and add the `email-reset` driver default configuration.

```php
<?php

return [
    'defaults' => [
        // ...
        'email-reset' => 'default' // Add this line
        // ...
    ],
    // Add this entire block
    'email-reset' => [
        'default' => [
            'table'  => 'email_resets',
            'expire' => 60,
            'callback' => 'App\Http\Controllers\Auth\ResetEmailController@reset',
            'ignore-migrations' => false,
            'route' => 'email/reset/{token}',
        ]
    ],
];
```

- `table`: **required**, database table name where will be store users new emails
- `expire`: **required**, validation link validity expiration
- `callback`: **required**, controller method that implement `ResetEmail` trait
- `ignore-migrations`: **optional**, default value false; if you would like to use your own migration, set it to `true` (take inspiration from [migration file](https://github.com/yaquawa/laravel-email-reset/blob/master/database/migrations/2018_06_01_000001_create_email_resets_table.php)).
- `route`: **optional**, default value `'email/reset/{token}'`

### Vendor installation
```shell
composer require yaquawa/laravel-email-reset
php artisan migrate
```

### Publish assets
`php artisan vendor:publish --tag=laravel-email-reset`

Create following assets in your app:
- `app/Http/Controllers/Auth/ResetEmailController.php`
- `resources/lang/vendor/laravel-email-reset`

### Apply `CanResetEmail` trait in User model
Edit your user model to use `CanResetEmail` trait.

```php
namespace App\Models;

class User extends Authenticatable
{
    use \Yaquawa\Laravel\EmailReset\CanResetEmail;
}
```

## Usage
### Send the verification email
Once you added the `CanResetEmail` trait in your `User` model, you can use reset email features.
Let's set new email for a user!

```php
$user->resetEmail('new_email@example.com');
```

Until user verified his new email, you can get the new email with `$user->new_email;`.

The user will receive a validation link at `new_email@example.com`.
By default, when the user clicked the verification link, he will be redirected to the root of your App URL.
You can change this behavior by overriding `ResetEmail` trait methods in your controller `ResetEmailController`.

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
        return redirect($this->redirectPathForFailure())
            ->withErrors(['laravel-email-reset' => trans($status)]);
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
        return redirect($this->redirectPathForSuccess())
            ->with('laravel-email-reset', trans($status));
    }

    /**
     * The redirect URI for failure.
     *
     * @return string
     */
    protected function redirectPathForFailure(): string
    {
        return '/';
    }

    /**
     * The redirect URI for success.
     *
     * @return string
     */
    protected function redirectPathForSuccess(): string
    {
        return '/';
    }
}
```

If you want to change the email contents, you can do something like this in your `AppServiceProvider.php` (do more with [laravel notifications doc](https://laravel.com/docs/7.x/notifications)).

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
