# Laravel JWTAuth

Laravel JWTAuth is a package to simplify [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) installation in a new laravel project.

## Installation

Let’s install the package via Composer:

`composer require pierresilva/laravel-jwt-auth`

After that, add the service provider to the Providers array in your `config/app.php` file:

```
'providers' => [
     ...
    /*
     * Package Service Providers...
     */

    'Tymon\JWTAuth\Providers\LaravelServiceProvider',


    /*
     * Application Service Providers...
     */
     ...

],
```

Next, also in the `config/app.php` file, add the JWTAuth and JWTFactory facades to the aliases array.

```
'aliases' => [
    ...
    'JWTAuth' => 'Tymon\JWTAuth\Facades\JWTAuth',
    'JWTFactory' => 'Tymon\JWTAuth\Facades\JWTFactory'
],
```

After that, we publish the package’s config using the following command:

```
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

Finally, let’s generate a secret key that this package will use to encrypt our tokens:

```
php artisan jwt:secret
```

The above command generates an encryption key and sets it in the `.env` file with something like `JWT_SECRET=keystring`.

We will be using the existing User model for authentication, therefore, we need to integrate our user model with the jwt-auth package. To do that, we’ll implement the `Tymon\JWTAuth\Contracts\JWTSubject` contract on the user model and define the two required methods, `getJWTIdentifier()` and `getJWTCustomClaims()`.

In `app/User.php` file modify like this:

```
<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

Now we need to have Laravel always use `jwt-auth` for authentication instead of the traditional `session` driver.

Set the default guard to `api` and the API guard’s driver to `jwt` in `config/auth.php` like so:

```
...
'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
            'hash' => false,
        ],
    ],
    ...
```

## API

### api/jwt-auth/register
**Register a new user:**

Method: POST

Example Request:
```
{
    "name": "User Name",
    "email": "user@name.com",
    "password": "password",
    "password_confirmation": "password"
}
```

Example Response:

```
{
    "message": "Successfully registered",
    "user": {
        "name": "User Name",
        "email": "user@name.com",
        "updated_at": "2020-06-18T14:27:05.000000Z",
        "created_at": "2020-06-18T14:27:05.000000Z",
        "id": 2
    }
}
```

### api/jwt-auth/login
**Login User**

Method: POST

Example Request:
```
{
    "email": "user@name.com",
    "password": "password"
}
```

Example Response:
```
{
    "message": "Logged in successfully",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhbmctYmFzZWxpbmUudGVzdFwvYXBpXC9qd3QtYXV0aFwvbG9naW4iLCJpYXQiOjE1OTI0OTA2MjQsImV4cCI6MTU5MjQ5NDIyNCwibmJmIjoxNTkyNDkwNjI0LCJqdGkiOiJjRkQ5WU96cFNpTGZiU1FQIiwic3ViIjoyLCJwcnYiOiI4N2UwYWYxZWY5ZmQxNTgxMmZkZWM5NzE1M2ExNGUwYjA0NzU0NmFhIn0.aEklNmy7Qt1kGv9WmNkZvo0u8bXTyty2zLrgyuTAXpM",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### api/jwt-auth/logout
**Logout User**

Headers: "Authorization: Bearer token_string"

Method: POST

Example Request: NA

Example Response:
```
{
    "message": "Successfully logged out"
}
```
### api/jwt-auth/refresh
**Refresh Token**

Headers: "Authorization: Bearer token_string"

Method: POST

Example Request: NA

Example Response:
```
{
    "message": "Token refreshed successfully",
    "data": {
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sYXJhbmctYmFzZWxpbmUudGVzdFwvYXBpXC9qd3QtYXV0aFwvcmVmcmVzaCIsImlhdCI6MTU5MjQ4NzY1MiwiZXhwIjoxNTkyNDkxNjI1LCJuYmYiOjE1OTI0ODgwMjUsImp0aSI6ImptNEh3UnNwVnpsdTV1TDEiLCJzdWIiOjEsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.fuw1EL6wi5nqWWu0eVs7pVUsh3d1dVoxT3NgaG-tCfk",
        "token_type": "bearer",
        "expires_in": 3600
    }
}
```

### api/jwt-auth/profile
**Get User Profile**

Headers: "Authorization: Bearer token_string"

Method: GET

Example Request: NA

Example Response:
```
{
    "message": "Profile obtained successfully",
    "data": {
        "id": 2,
        "name": "User Name",
        "email": "user@name.com",
        "email_verified_at": null,
        "created_at": "2020-06-18T14:27:05.000000Z",
        "updated_at": "2020-06-18T14:27:05.000000Z"
    }
}
```
