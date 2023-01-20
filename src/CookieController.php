<?php

namespace CommandString\Cookies;

use InvalidArgumentException;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CookieController {
    private CookieEncryptionInterface $encryptor;

    public function __construct(CookieEncryptionInterface $encryptor = null) {
        $this->encryptor = $encryptor ?? new NullEncryption;
    }

    public function cookie(ServerRequestInterface &$request, ResponseInterface &$response): Cookie
    {
        return new Cookie($request, $response, $this);
    }
    
    /**
     * Does cookie exist
     * 
     * @param string $name
     * 
     * @return bool
     */
    public function exists(ServerRequestInterface &$request, string $name): bool
    {
        return isset($request->getCookieParams()[$name]);
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
    public function set(ResponseInterface &$response, string $name, string|int $value, int $hoursValid=168, int $minutesValid=0, int $secondsValid=0, string $path = "/", string $domain = "", bool $secure = false, bool $httponly = false): self
    {
        $encryptedString = $this->encryptor->encrypt($value);

        $timeExpires = (new DateTime())->setTimestamp(time() + ((3600 * $hoursValid) + (60 * $minutesValid) + $secondsValid));
        $timeExpiresString = $timeExpires->format("D, j n Y G:i:s");
        
        if (preg_match("/[()<>@,;:\"\/[\]?{}]/", $name)) {
            throw new InvalidArgumentException("The cookie name must not contain any of the following characters `[()<>@,;:\"/[\]?{}]`.");
        }

        if (preg_match("/[\",\\;]/", $value)) {
            throw new InvalidArgumentException("The cookie value must not contain any of the following characters `[\",\\;]`");
        }

        $cookieHeader = sprintf('%s=%s; Expires=%s', $name, $value, $timeExpiresString);

        $empty = ["Path", "Domain"];

        foreach ($empty as $key) {
            $keyValue = ${strtolower($key)};
            if (!empty($keyValue)) {
                $cookieHeader .= sprintf('; %s=%s', $key, $keyValue);
            }
        }

        $true = ["Secure", "HttpOnly"];

        foreach ($true as $key) {
            $keyValue = ${strtolower($key)};
            if ($keyValue) {
                $cookieHeader .= sprintf('; %s', $key);
            }
        }

        $response = $response->withHeader("Set-Cookie", $cookieHeader);

        return $this;
    }

    /**
     * Get cookie
     * 
     * @param string $name
     * 
     * @return ?string
     */
    public function get(ServerRequestInterface &$request, string $name): ?string
    {
        return (isset($request->getCookieParams()[$name])) ? $this->encryptor->decrypt($request->getCookieParams()[$name]) : null;
    }

    /**
     * Delete cookie(s) specified
     * 
     * @param string $name
     */
    public function delete(ResponseInterface &$response, ServerRequestInterface &$request, string $cookie, string $path = "/", string $domain = ""): self
    {
        if ($this->exists($request, $cookie)) {
            $cookieHeader = "$cookie=deleted; Expires=Sun, 06 Nov 1994 08:49:37";

            $empty = ["Path", "Domain"];
    
            foreach ($empty as $key) {
                $keyValue = ${strtolower($key)};
                if (!empty($keyValue)) {
                    $cookieHeader .= sprintf('; %s=%s', $key, $keyValue);
                }
            }

            $response = $response->withHeader("Set-Cookie", $cookieHeader);
        }


        return $this;
    }

    /**
     * Deletes all cookies
     * 
     * @return void
     */
    public function deleteAll(ResponseInterface &$response, ServerRequestInterface &$request): self 
    {
        $this->delete($response, $request, ...array_keys($request->getCookieParams()));
        return $this;
    }
}