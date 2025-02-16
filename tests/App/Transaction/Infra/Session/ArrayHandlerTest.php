<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Session;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

class ArrayHandlerTest extends TestCase
{
    protected string $sessionName = 'test-session-name';

    protected LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->removeSession();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    protected function tearDown(): void
    {
        $this->removeSession();
    }

    private function removeSession(): void
    {
        session_destroy();

        $file = __DIR__ . '/../../../../../var/session' . '/' . $this->sessionName . '.php';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    #[Test]
    #[TestDox('Should crate session file')]
    public function shouldCreateSessionFile(): void
    {
        $handler = new ArrayHandler($this->logger, $this->sessionName);
        $sessionFilePath = require $handler->getSessionFullPath(); // @phpcs:ignore

        Assert::assertIsArray($sessionFilePath);
    }

    #[Test]
    #[TestDox('Should start session')]
    public function shouldStartSession(): void
    {
        $handler = new ArrayHandler($this->logger, $this->sessionName);
        $handler->start();

        Assert::assertIsArray($_SESSION);
    }
}
