<?php

namespace CommandString\Cookies;

use CommandString\Cookies\CookieController;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ServerRequest;

class Cookie {
    public function __construct(
        private ServerRequest &$request,
        private ResponseInterface &$response,
        private CookieController $controller
    ) {}

    public function exists($name): bool
    {
        return $this->controller->exists($this->request, $name);
    }

    public function set(string $name, string|int $value, int $hoursValid=168, int $minutesValid=0, int $secondsValid=0, string $path = "/", string $domain = "", bool $secure = false, bool $httponly = false): self 
    {
        $this->controller->set($this->response, $name, $value, $hoursValid, $minutesValid, $secondsValid, $path, $domain, $secondsValid, $httponly);
        return $this;
    }

    public function get($name): ?string
    {
        return $this->controller->get($this->request, $name);
    }

    public function delete(string $cookie, string $path = "/", string $domain = ""): self
    {
        $this->controller->delete($this->response, $this->request, $cookie, $path, $domain);
        return $this;
    }

    public function deleteAll(): self
    {
        $this->controller->deleteAll($this->response, $this->request);

        return $this;
    }
}