<?php

namespace App\Controller;

use Exception;
use App\Database;

/**
 * Controller for the main index page
 */
class IndexController extends Controller
{

    const CIPHER = "aes-256-cbc";

    /** @var string[] Page error messages */
    private $errors = [];

    /**
     * Run the /index.php page
     */
    public function load() {

        $this->setVar(
            "tokens",
            $this->getTokens()
        );

        $url = false;
        $secret_cipher = false;

        if ($this->getMethod() == self::POST) {

            $url = $this->makeEncryptedUrl(
                $this->getData('secret_cipher'),
                $this->getData('expiration'),
                $this->getData('hint')
            );

            if (!$url && empty($this->errors)) {
                $this->errors[] = "Failed to create secret URL";
            }
        } else {

            $secret_cipher = $this->decryptUrl(
                $this->getQuery('crypt'),
                $this->getQuery('expiration')
            );
        }

        $this->setVar("errors", $this->errors);
        $this->setVar("url", $url);
        $this->setVar("secret_cipher", $secret_cipher);
    }

    /**
     * @return array All available tokens
     * @throws Exception On database error
     */
    private function getTokens(): array {

        try {

            $result = Database::get()->query(
                "SELECT identifier, expiration FROM tokens WHERE expiration > NOW() ORDER BY expiration"
            );

            $tokens = $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            return ["Error" => $e->getMessage()];
        }

        return $tokens;
    }

    /**
     * @param string $identifier Identifier of expiration token
     * @return string|false Secret key of expiration token
     */
    private function getTokenFromIdentifier(string $identifier) {
        $stmt = Database::get()->prepare(
            "SELECT token FROM tokens WHERE expiration > NOW() AND identifier = ?"
        );

        $stmt->bind_param("s", $identifier);
        $stmt->execute();
        $token = false;
        $stmt->bind_result($token);
        $stmt->fetch();

        if (is_null($token)) {
            return false;
        }

        return $token;
    }

    /**
     * Make disclosure URL
     *
     * The flow of variables and their types are:
     *      User secret [text]
     *          > Cipher 1 [text, base64] (CryptoJS)
     *              > Cipher 2 [text, hex] (openssl_encrypt)
     *
     * @param string $secret_cipher User's secret, already encrypted by password
     * @param string $expiration Identifier of expiration token
     * @param string $hint Defaults to empty
     * @return string|false Full based URL - False on error
     */
    private function makeEncryptedUrl(string $secret_cipher, string $expiration, string $hint = "") {

        if (empty($secret_cipher) || empty($expiration)) {
            $this->errors[] = "Missing cipher or expiration from client";
            return false;
        }

        $token = $this->getTokenFromIdentifier($expiration);

        if (!$token) {
            $this->errors[] = "Could not find selected expiration token";
            return false;
        }

        // Generate an initialization vector
        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_size);

        $cipher = openssl_encrypt($secret_cipher, self::CIPHER, $token, 0, $iv);

        $url = "https://" . $this->getHost() . $this->getUrl() . "?" .
            "crypt=" . bin2hex($cipher) .
            "&expiration=" . $expiration;

        if ($hint) {
            $url .= "&hint=" . base64_encode($hint);
        }

        return $url;
    }

    /**
     * Decrypt the components of an incoming URL and return the secret (encrypted still by the user password)
     *
     * @param string $cipher Cipher, encrypted by expiration token
     * @param string $expiration Identifier of expiration token
     * @return string|false
     */
    public function decryptUrl($cipher, $expiration) {

        if (empty($cipher) && empty($expiration)) {
            return false; // Quietly return
        } elseif (empty($cipher) || empty($expiration)) {
            $this->errors[] = "Missing cipher or expiration in URL";
            return false;
        }

        $token = $this->getTokenFromIdentifier($expiration);

        if (!$token) {
            $this->errors[] = "Could not find expiration token from URL";
            return false;
        }

        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_size);

        return openssl_decrypt(hex2bin($cipher), self::CIPHER, $token, 0, $iv);
    }

}