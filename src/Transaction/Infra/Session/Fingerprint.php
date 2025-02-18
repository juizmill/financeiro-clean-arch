<?php

namespace App\Transaction\Infra\Session;

/**
 * Class Fingerprint.
 *
 * Generates a unique fingerprint for user sessions based on various environmental factors.
 * This class is used to enhance security by creating a unique identifier for each session,
 * which helps in detecting potential session hijacking or unauthorized access. The fingerprint
 * is generated using a combination of the user's IP address, user agent, and specific cookies,
 * ensuring a robust mechanism to validate the authenticity of a session.
 *
 * The primary purpose of this class is to provide a method for verifying the integrity and
 * authenticity of a session. By using a salted hash, the generated fingerprint is both unique
 * and secure, reducing the risk of session-related attacks and maintaining the confidentiality
 * and integrity of user data within the application.
 */
readonly class Fingerprint
{
    /**
     * Constructor.
     *
     * Initializes the Fingerprint class with a salt value. The salt is used to add
     * an extra layer of security to the fingerprint generation process, ensuring that
     * even if the inputs (IP address, user agent, cookies) are known, the resulting
     * fingerprint cannot be easily guessed or replicated.
     *
     * @param string $salt a string used to salt the fingerprint hash
     */
    public function __construct(private string $salt)
    {
    }

    /**
     * Generates a unique session fingerprint.
     *
     * This method creates a hash based on the user's IP address, user agent, and
     * specific cookies, combined with a salt. The generated fingerprint serves as
     * a unique identifier for the session, helping to detect any anomalies or changes
     * that might indicate session hijacking. By checking this fingerprint on subsequent
     * requests, the application can ensure that the session is being accessed by the
     * same user under the same conditions.
     *
     * @return string the generated fingerprint for the session
     */
    public function generate(): string
    {
        $fingerprint = [$this->getIpAddress()];

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $fingerprint[] = $_SERVER['HTTP_USER_AGENT'];
        }

        foreach ($_COOKIE as $cookie => $value) {
            if ($cookie === '_gid') {
                $fingerprint[] = $value;
            }
        }

        $id = md5(implode(':', $fingerprint));

        return "$this->salt:$id";
    }

    /**
     * Retrieves the user's IP address.
     *
     * This method determines the user's real IP address by checking various server
     * variables that may contain it. It is designed to account for scenarios where
     * the user's IP address is hidden or routed through proxies, ensuring that the
     * most accurate IP address is used for fingerprint generation.
     *
     * @return string the user's IP address
     */
    private function getIpAddress(): string
    {
        $serverKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        $realIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        foreach ($serverKeys as $serverKey) {
            $serverValue = getenv($serverKey);

            if ($serverValue !== '') {
                $realIp = $serverValue;
                break;
            }
        }

        // @codeCoverageIgnoreStart
        // @phpstan-ignore-next-line
        if (mb_strpos($realIp, ',') !== false) {
            $ip = explode(',', $realIp); // @phpstan-ignore-line

            $realIp = $ip[0];
        }
        // @codeCoverageIgnoreEnd

        return $realIp; // @phpstan-ignore-line
    }
}
