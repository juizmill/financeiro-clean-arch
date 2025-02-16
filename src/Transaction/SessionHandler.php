<?php

declare(strict_types=1);

namespace App\Transaction;

use ArrayAccess;

/**
 * Interface SessionHandler.
 *
 * Defines a contract for handling session data within the application, ensuring that session management
 * can be implemented consistently across different storage mechanisms. By extending `ArrayAccess`, this
 * interface allows session data to be managed like an array, providing a flexible and familiar way to
 * interact with session storage. This abstraction is crucial for maintaining state across different
 * requests in a stateless environment like HTTP.
 *
 * The primary purpose of this interface is to abstract the session management layer, allowing for
 * different implementations (e.g., file-based, database, or in-memory) without changing the application
 * logic. This promotes flexibility and testability, making the application easier to maintain and extend.
 *
 * @extends ArrayAccess<string, mixed>
 */
interface SessionHandler extends ArrayAccess
{
    /**
     * Sets a value in the session.
     *
     * This method is essential for storing data in the session under a specific key.
     * It allows for adding or updating session data, which is crucial for maintaining
     * the state of user interactions across multiple requests, such as keeping track
     * of a shopping cart or user authentication status.
     *
     * @param string $key   the key under which the value will be stored
     * @param mixed  $value the value to store in the session
     */
    public function set(string $key, mixed $value): void;

    /**
     * Retrieves a value from the session.
     *
     * This method is used to access stored session data by its key. It is vital for retrieving
     * data that needs to persist between requests, such as user preferences or cart contents.
     * If the key does not exist, a default value can be returned, which helps prevent errors
     * and provides a more robust way to handle session data retrieval.
     *
     * @param string $key     the key of the value to retrieve
     * @param mixed  $default the default value to return if the key does not exist
     *
     * @return mixed the value associated with the specified key, or the default value if the key does not exist
     */
    public function get(string $key, mixed $default): mixed;

    /**
     * Starts the session.
     *
     * This method is responsible for initializing the session, making sure that all session-related
     * configurations are set up properly before the session is used. It is crucial for ensuring that
     * session data can be reliably stored and retrieved during the application's lifecycle. Starting
     * a session typically involves setting session handlers, configuring cookie parameters, and managing
     * session state. This method ensures that these steps are performed consistently across different
     * implementations.
     */
    public function start(): void;
}
