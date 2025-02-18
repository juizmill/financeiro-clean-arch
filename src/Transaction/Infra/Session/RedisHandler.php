<?php

declare(strict_types=1);

namespace App\Transaction\Infra\Session;

use ArrayObject;
use Predis\Client;
use RuntimeException;
use Predis\Session\Handler;
use Psr\Log\LoggerInterface;
use App\Transaction\SessionHandler;

/**
 * Class RedisHandler.
 *
 * Manages session data using Redis as the storage backend. This class implements the `SessionHandler` interface,
 * providing a robust and scalable solution for handling session data in distributed environments. By leveraging
 * Redis, a high-performance in-memory data store, this handler ensures that session data is stored and retrieved
 * efficiently, supporting high-throughput and low-latency requirements typical of modern web applications.
 *
 * The primary purpose of this class is to facilitate session management through Redis, enhancing the scalability
 * and reliability of the application's session handling capabilities. It also includes functionality for generating
 * unique session IDs using a fingerprint mechanism, adding an extra layer of security to the session management
 * process.
 *
 * @extends ArrayObject<string,mixed>
 * @codeCoverageIgnore
 */
class RedisHandler extends ArrayObject implements SessionHandler
{
    private string $sessionId;

    /**
     * Constructor.
     *
     * Initializes the Redis session handler with necessary parameters such as Redis host, port, database,
     * session TTL, and session name. The constructor also generates a unique session ID using a fingerprint,
     * ensuring that each session is uniquely identified and protected against hijacking.
     *
     * @param LoggerInterface $logger      logs session-related activities for monitoring and debugging
     * @param string          $host        the Redis server host
     * @param int             $port        the Redis server port
     * @param int             $database    the Redis database index to use
     * @param int             $sessionTtl  the session time-to-live (TTL) in seconds
     * @param string          $sessionName the name of the session
     * @param string          $salt        a salt string used for generating the session fingerprint
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $host,
        private readonly int $port,
        private readonly int $database,
        private readonly int $sessionTtl,
        private readonly string $sessionName = 'finance-system',
        string $salt = 'finance-system',
    ) {
        parent::__construct();

        $this->sessionId = (new Fingerprint($salt))->generate();
    }

    /**
     * Starts the Redis session.
     *
     * This method initializes the session with Redis as the storage backend. It sets up the session parameters,
     * such as cookie settings and session handler configuration, to ensure secure and efficient session management.
     * If the session is successfully started, it stores the session data in Redis; otherwise, it throws an exception.
     *
     * @throws RuntimeException if the session fails to start
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->logger->debug(
                "Starting session with ID: $this->sessionId",
                [
                    'type' => 'Redis',
                    'host' => $this->host,
                    'port' => $this->port,
                    'database' => $this->database,
                    'sessionTtl' => $this->sessionTtl,
                    'sessionName' => $this->sessionName,
                ]
            );

            $sessionHandler = new Handler(
                new Client(['host' => $this->host, 'port' => $this->port, 'database' => $this->database]),
                array_filter(['gc_maxlifetime' => $this->sessionTtl]) // @phpstan-ignore-line
            );

            $sessionHandler->register();

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
                throw new RuntimeException('Failed to start session');
            }
        }
    }

    /**
     * Sets a value in the session.
     *
     * This method stores a key-value pair in the session data, ensuring that the application can maintain
     * user state and other necessary data across requests. It provides a standardized way to add data to
     * the session, which is critical for maintaining continuity in user interactions.
     *
     * @param string $key   the key under which the value will be stored
     * @param mixed  $value the value to store in the session
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieves a value from the session.
     *
     * This method accesses stored session data by its key, returning the value if it exists or a default value
     * if the key is not found. It is essential for retrieving user-specific data that needs to persist across
     * multiple requests, such as user preferences or authentication tokens.
     *
     * @param string $key     the key of the value to retrieve
     * @param mixed  $default the default value to return if the key does not exist
     *
     * @return mixed the value associated with the specified key, or the default value if the key does not exist
     */
    public function get(string $key, mixed $default): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
}
