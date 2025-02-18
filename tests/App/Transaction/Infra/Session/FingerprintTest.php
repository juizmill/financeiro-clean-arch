<?php

namespace App\Transaction\Infra\Session;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

class FingerprintTest extends TestCase
{
    #[Test]
    #[TestDox('Should result generate fingerprint')]
    public function shouldGenerateFingerprint()
    {
        $_COOKIE['_gid'] = 'foo';
        $_SERVER['HTTP_USER_AGENT'] = 'bar';
        $fingerprint = new Fingerprint('abc');
        $result = $fingerprint->generate();

        Assert::assertEquals('abc:8a4d25ce3ca565891fb6bf0efbc96cea', $result);
    }
}
