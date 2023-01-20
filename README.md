
# [CommandString/Cookies](https://packagist.org/packages/commandstring/cookies) - A simpler way to manipulate cookies in PHP #

### Install with Composer using `composer require commandstring/cookies` ###

## Requirements ##
- PHP >=8.0
- Basic understanding of PHP OOP
- Composer 2

## Basic Usage ##
```php
require  __DIR__."/vendor/autoload.php";
use CommandString\Cookies\Cookie;

$cookies = new Cookie();

#                              v hours 
#                              v valid   v seconds valid
$cookies->set("name", "value", 168, 10, 30); // by default cookies expire in a week
#                                   ^ minutes valid

// After page refresh (An exception will be thrown if the cookie doesn't exist) //
echo $cookies->get("name"); // output: value

// Delete cookie (An exception will be thrown if the cookie doesn't exist) //
$cookie->delete("name"); // remove the cookie

// Delete all cookies
$cookie->deleteAll();

// Check if a cookie exists
$cookie->exists("name"); // returns bool
```

## Comparing regular cookie manipulation with CommandString/Cookies ##
### CommandString/Cookies ###
```php
// config.php
require  __DIR__."/vendor/autoload.php";
use CommandString\Cookies\Cookie;

$cookies = new Cookie();
// ...

// login.php
require_once "config.php";

if ($userIsReadyToBeLoggedIn) {
	$cookies->set("username", "Command_String");
	header("location: home.php");
}
// ...

// home.php
<?php
require_once "config.php";

if (!$cookies->exists("username")) {
	header("location: login.php");
}
```
```html
// ...
<h1>Welcome back, <?= $cookies->get("username")); ?></h1>
// ...
```
```php
// logout.php
require_once "config.php";

$cookies->deleteAll();
header("location: login.php");
```
### Regular Cookie Manipulation ###
```php
// login.php
setcookie($name, "ValueForCookie", time() + (3600 * 168), "/");
header("location: home.php");
}

// home.php
require_once "config.php";

if (isset($_COOKIE['username'])) {
	header("location: login.php");
}

// ...
```
```html
<h1>Welcome back, <?= $_COOKIE['username'] ?><h1>
<!-- ... -->
```
```php
// logout.php
foreach ($_COOKIE as $key => $value) {
	unset($_COOKIE[$key]);
	setcookie($key, null, -1, '/');
}

header("location: login.php");
```

## Implementing Custom Cookie Encryption ##
```php
use CommandString\Cookies\CookieEncryptionInterface;
use CommandString\Cookies\Cookie;

class Encryption implements CookieEncrypytionInterface {
	public function encrypt(string $data):string
	{
		/* do some encrypting stuff here */
	}
	public function decrypt(string $data):string
	{
		/* do some decrypting stuff here */
	}
}

$cookies = new Cookie((new Encryption()));
// now when using $cookie->set("name", "value"); it will use the methods defined in encryption 
```

## Using CommandString/Encrypt with CommandString/Cookies ##
### *[I recommend checking out the README for CommandString/Encrypt](https://github.com/CommandString/encrypt#basic-usage)* ###
```php
use CommandString\CookieEncryption\CookieEncryption;
use CommandString\Cookies\Cookie;

// use the cookieEncryption class that wraps around cmdstr/encrypt/encryption class
$cookies = new Cookie(new CookieEncryption("MZCdg02STLzrsj05KE3SIL62SSlh2Ij", "AES-256-CTR"));
// ... now cmdstr/encrypt will handle encrypting cookies
```