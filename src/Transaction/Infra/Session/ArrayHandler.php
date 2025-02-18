<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Session;

use ArrayObject;
use RuntimeException;
use Psr\Log\LoggerInterface;
use SessionHandlerInterface;
use App\Transaction\SessionHandler;

/**
 * @extends ArrayObject<string,mixed>
 */
class ArrayHandler extends ArrayObject implements SessionHandler, SessionHandlerInterface
{
    protected const string SESSION_PATH = __DIR__ . '/../../../../var/session';

    protected string $sessionFullPath;

    private string $sessionId;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $sessionName = 'finance-system',
        string $salt = 'finance-system',
    ) {
        $this->sessionFullPath = self::SESSION_PATH . '/' . $this->sessionName . '.php';

        if (! is_dir(self::SESSION_PATH)) {
            mkdir(self::SESSION_PATH); // @codeCoverageIgnore
        }

        if (! file_exists($this->sessionFullPath)) {
            file_put_contents($this->sessionFullPath, '<?php return [];');
        }

        parent::__construct();

        $this->sessionId = (new Fingerprint($salt))->generate();
    }

    public function getSessionFullPath(): string
    {
        return $this->sessionFullPath;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->logger->debug(
                'Starting session with ID: ' . $this->sessionId,
                [
                    'type' => 'Array',
                    'sessionName' => $this->sessionName,
                ]
            );

            session_set_save_handler($this, true);

            // @phpstan-ignore-next-line
            session_set_cookie_params([
                'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'path' => '/',
                'secure' => true,
                'httponly' => true,
            ]);

            session_id($this->sessionId);

            if ($this->sessionName !== '') {
                session_name($this->sessionName);
            }

            if (! session_start()) {
                throw new RuntimeException('Failed to start session'); // @codeCoverageIgnore
            }
        }
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        //  @phpcs:ignore
        $session = require $this->sessionFullPath;
        unset($session[$id]);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 86400; // 24 hours
    }

    public function open(string $path, string $name): bool
    {
        unset($path, $name);

        return true;
    }

    public function read(string $id): string|false
    {
        //  @phpcs:ignore
        $session = require $this->sessionFullPath;

        return $session[$id] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        //  @phpcs:ignore
        $session = require $this->sessionFullPath;
        $session[$id] = $data;

        return true;
    }
}
