<?php

namespace CommandString\Cookies;

interface CookieEncryptionInterface {
    public function encrypt(string $data): string;
    public function decrypt(string $data): string;
}
