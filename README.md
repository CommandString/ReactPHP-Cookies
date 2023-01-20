
# [CommandString/ReactPHP-cookies](https://packagist.org/packages/commandstring/reactphp-cookies) - A easier way to manipulate cookies in React/Http #

### Install with Composer using `composer require commandstring/reactphp-cookies` ###

*For the examples $req is an object that implements of PSR-7 ServerRequestInterface and $res is an object that implements PSR-7 ResponseInterface*

# Creating Controller

```php
$cookieController = new CookieController(null);
```

If you want to encrypt your cookies you can either create a class that implements CookieEncryptionInterface or use [Cookie Encryption](https://github.com/CommandString/Cookie-Encryption)

Creating Cookie object from controller

You will need to create an object that implements PSR-7's Response Interface beforehand

```php
$cookie = $cookieController->cookie($req, $res);
```

# Setting cookies

```php
$cookie->set("token", "123456", 1, 15, 13, "/app", "app.domain.com");
```

This will create a cookie with the name of `token` that is set to `123456`. This cookie will expire in 1 hour, 15 minutes, and 13 seconds from now. The cookie is valid only in the app path and on the app.domain.com website.

# Getting cookies

```php
$cookie->get("token");
```

If a cookie with the name of token is set then it will return it's value. If not it returns null.

# Deleting cookies

```php
$cookie->delete("token", "/app", "app.domain.com");
```

This will delete a cookie with the name token that has its path set to `/app` and domain set to  `app.domain.com`

# Example usage

```php
<?php

use CommandString\Cookies\CookieController;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

require_once "vendor/autoload.php";

$cookies = new CookieController();

$http = new HttpServer(function (ServerRequestInterface $req) use ($cookies) {
    $res = new React\Http\Message\Response;

    $cookie = $cookies->cookie($req, $res);

    $parts = explode("/", $req->getRequestTarget());
    $partsNum = count($parts) - 1;

    $invalidReq = function (string $message) use (&$res): Response
    {
        $res->withStatus(403);
		$res = $res->withHeader('content-type', 'text-plain');
		$res->getBody()->write($message);
    };

    if ($parts[1] === "set") {
        if ($partsNum !== 3) {
            return $invalidReq("Invalid URI, example `/set/id/123456`");
        }

        $cookie->set($parts[2], $parts[3]);
        $res->getBody()->write("Set cookie {$parts[2]} to {$parts[3]}");
    }

    if ($parts[1] === "get") {
        if ($partsNum !== 2) {
            return $invalidReq("Invalid URI, example `/get/id`");
        }

        if ($cookie->exists($parts[2])) {
            $res->getBody()->write("Found cookie {$parts[2]}, it is set to {$cookie->get($parts[2])}");
        } else {
            $res->getBody()->write("Cookie {$parts[2]} does not exist");
        }
    }

    if ($parts[1] === "delete") {
        if ($partsNum !== 2) {
            return $invalidReq("Invalid URI, example `/delete/id`");
        }

        if ($cookie->exists($parts[2])) {
            $cookie->delete($parts[2]);
            $res->getBody()->write("Deleted cookie {$parts[2]}");
        } else {
            $res->getBody()->write("Cooke {$parts[2]} does not exist");
        }
    }
    
    return $res;
});

$socket = new SocketServer('127.0.0.1:8000');
$http->listen($socket);
```
