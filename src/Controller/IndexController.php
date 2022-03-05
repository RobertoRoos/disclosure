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
    static $IV_LENGTH = 0;

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

            if ($secret_cipher === "") {
                $this->errors[] = "Failed to analyse encrypted URL";
            }
        }

        $this->setVar("errors", $this->errors);
        $this->setVar("url", $url);
        $this->setVar("secret_cipher", $secret_cipher);

        $hint_encoded = $this->getQuery("hint");
        if ($hint_encoded) {
            $hint = base64_decode(urldecode($hint_encoded));
        } else {
            $hint = false;
        }
        $this->setVar("password_hint", $hint);
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
     * The link contains the salted cipher, the expiration identifier and the password hint.
     *
     * The flow of variables and their types are:
     *      User secret [text]
     *          > Cipher 1 [binary] (CryptoJS)
     *          > Cipher 1 [text, base64] (CryptoJS)
     *              > Cipher 2 [text, base64] (openssl_encrypt)
     *              > Cipher 2 + salt [text, hex + base64]
     *
     * @param string $cipher1    User's secret, already encrypted by password
     * @param string $expiration Identifier of expiration token
     * @param string $hint       Defaults to empty
     * @return string|false Full based URL - False on error
     */
    private function makeEncryptedUrl($cipher1, $expiration, $hint = "") {

        if (empty($cipher1) || empty($expiration)) {
            $this->errors[] = "Missing cipher or expiration from client";
            return false;
        }

        $token = $this->getTokenFromIdentifier($expiration);

        if (!$token) {
            $this->errors[] = "Could not find selected expiration token";
            return false;
        }

        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(self::$IV_LENGTH);

        // Output of encrypt is a base64 encoded string
        $cipher2 = openssl_encrypt($cipher1, self::CIPHER, $token, 0, $iv);

        $cipher2_salted = urlencode(bin2hex($iv) . $cipher2);
        // $iv is a bytes string, encode in hex

        $url = "https://" . $this->getHost() . $this->getUrl() . "?" .
            "crypt=" . $cipher2_salted .
            "&expiration=" . $expiration;

        if ($hint) {
            $url .= "&hint=" . urlencode(base64_encode($hint));
        }

        return $url;
    }

    /**
     * Decrypt the components of an incoming URL and return the secret (encrypted still by the user password)
     *
     * Returned string is false when information was missing and "" in case it could not be decrypted.
     *
     * @see makeEncryptedUrl
     * @param string $cipher2    Cipher, encrypted by expiration token
     * @param string $expiration Identifier of expiration token
     * @return string|false
     */
    public function decryptUrl($cipher2, $expiration) {

        if (empty($cipher2) && empty($expiration)) {
            return false; // Quietly return
        } elseif (empty($cipher2) || empty($expiration)) {
            $this->errors[] = "Missing cipher or expiration in URL";
            return false;
        }

        $token = $this->getTokenFromIdentifier($expiration);

        if (!$token) {
            $this->errors[] = "Could not find expiration token from URL";
            return false;
        }

        $iv_size_hex = self::$IV_LENGTH * 2; // Because encoded in hex
        $iv = hex2bin(substr($cipher2, 0, $iv_size_hex));
        $cipher_bin = substr($cipher2, $iv_size_hex);

        $cipher1 = openssl_decrypt($cipher_bin, self::CIPHER, $token, 0, $iv);

        if (!$cipher1) {
            $cipher1 = "";
        }

        return $cipher1;
    }

}

IndexController::$IV_LENGTH = openssl_cipher_iv_length(IndexController::CIPHER);
