<?php

namespace CommandString\Cookies;

use InvalidArgumentException;
use CommandString\Cookies\NullEncryption;

/**
 * An simpler way to manipulate cookies in PHP
 * 
 * @author Command_String - https://discord.dog/232224992908017664
 */
class Cookie {
    private cookieEncryptionInterface $encryptor;
        
    public function __construct(?cookieEncryptionInterface $encryptor = null)
    {
        $this->encryptor = $encryptor ?? new nullEncryption;
    }

    /**
     * Does cookie exist
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function exists(string $name):bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Set a cookie
     * 
     * @param string $name
     * @param string|int $value
     * @param int $hoursValid
     * @param int $minutesValid
     * @param int $secondsValid
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return cookie
     */
    public function set(string $name, string|int $value, int $hoursValid=168, int $minutesValid=0, int $secondsValid=0, string $path = "/", string $domain = "", bool $secure = false, bool $httponly = false):cookie
    {
        $encryptedString = $this->encryptor->encrypt($value);
        setcookie($name, $encryptedString, time() + ((3600 * $hoursValid) + (60 * $minutesValid) + $secondsValid), $path, $domain, $secure, $httponly);

        $_COOKIE[$name] = $encryptedString;

        return $this;
    }

    /**
     * Get cookie
     * 
     * @param string $name
     * 
     * @return ?string
     */
    public function get(string $name): ?string
    {
        return $this->encryptor->decrypt($_COOKIE[$name]) ?? null;
    }

    /**
     * Delete cookie(s) specified
     * 
     * @param string $name
     */
    public function delete(string ...$cookies):cookie
    {
        foreach ($cookies as $cookie) {
            if ($this->exists($_COOKIE[$cookie])) {
                throw new InvalidArgumentException("Cookie doesn't exist in configuration");
            }

            unset($_COOKIE[$cookie]);
            setcookie($cookie, null, -1, '/');
        }

        return $this;
    }

    /**
     * Deletes all cookies
     * 
     * @return void
     */
    public function deleteAll():cookie 
    {
        $this->delete(...array_keys($_COOKIE));
        return $this;
    }
}
