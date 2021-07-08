<?php

require_once GOOGLEAUTHENTICATOR_BASIC_PATH . '/vendor/autoload.php';

use RobThree\Auth\Providers\Time\LocalMachineTimeProvider;
use RobThree\Auth\TwoFactorAuth;

class GoogleAuthenticator
{
    private $auth;
    private $secret;
    private $siteName;

    public function __construct($secret = null)
    {
        $this->siteName = qa_opt('site_title');
        $this->auth = new TwoFactorAuth($this->siteName, 6, 30, 'sha1');
        $this->secret = $secret;
        $this->auth->ensureCorrectTime([ new LocalMachineTimeProvider ]);
    }

    public function verifyCode($code): bool
    {
        return $this->auth->verifyCode($this->secret, $code, 10);
    }

    public function createSecret(): void
    {
        $this->secret = $this->auth->createSecret(160);
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getCode()
    {
        return $this->auth->getCode($this->secret);
    }

    public function getQRCode(): string
    {
        return $this->auth->getQRCodeImageAsDataUri($this->siteName, $this->secret);
    }

    public function getRandomRecoveryCode(): string
    {
        return $this->randomString(5) . '-' . $this->randomString(5);
    }

    /**
     * https://stackoverflow.com/a/31107425
     */
    public function randomString(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }
}
