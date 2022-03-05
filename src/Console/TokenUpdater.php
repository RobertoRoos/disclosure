<?php

namespace App\Console;

use App\Database;
use DateTime;
use DateInterval;
use Exception;

/**
 * Console task to remove expired tokens and create new ones.
 */
class TokenUpdater
{

    /** @var array */
    protected $config;

    /**
     * Constructor
     *
     * @param $_argc
     * @param $_argv
     */
    public function __construct($_argc, $_argv) {

        if ($_argc < 3 || empty($_argv[1]) || empty($_argv[2])) {

            echo "Usage: update_tokens.php [number of hours between tokens] [number of tokens in the future]\n";
            exit(0);
        }

        $this->config['hours_between_tokens'] = intval($_argv[1]);
        $this->config['max_number_tokens'] = intval($_argv[2]);
    }

    /**
     * Run console task
     *
     * @return int
     * @throws Exception On datetime parsing error
     */
    public function run(): int {

        // Remove all old tokens
        Database::get()->query("DELETE FROM tokens WHERE expiration <= NOW()");
        $rows = Database::get()->affected_rows;
        echo "Removed $rows old tokens\n";

        // Get total number of tokens
        $results_count = Database::get()->query("SELECT id FROM tokens WHERE expiration > NOW()");
        $current_tokens = 0;
        if ($results_count) {
            $current_tokens = $results_count->num_rows;
        }
        echo "Found $current_tokens existing tokens\n";

        // Get date of the last token
        $results_last = Database::get()->query("SELECT MAX(expiration) AS expiration_last FROM tokens WHERE expiration > NOW()");
        $datetime_last = new DateTime();
        if ($results_last) {
            $row = $results_last->fetch_assoc();
            if (!is_null($row['expiration_last'])) {
                $datetime_last = DateTime::createFromFormat("Y-m-d H:i:s", $row['expiration_last']);
            }
        }

        $datetime_last = $this->roundDateTime($datetime_last);

        echo "Last existing token expires on {$datetime_last->format(DateTime::ISO8601)}\n";

        $number_new_tokens = $this->config['max_number_tokens'] - $current_tokens;

        if ($number_new_tokens == 0) {
            echo "No new tokens need to be created.\n";
            return 0;
        }

        $step = new DateInterval("PT" . $this->config['hours_between_tokens'] . "H");

        $statement = Database::get()->prepare(
            "INSERT INTO tokens (identifier, token, expiration) VALUES (?, ?, ?)"
        );

        for ($i = 0; $i < $number_new_tokens; $i++) {
            $datetime_last->add($step);

            $identifier = $this->getRandomString(16);
            $token = $this->getRandomString(64);
            $expiration = $datetime_last->format("Y-m-d H:i:s");

            $statement->bind_param("sss", $identifier, $token, $expiration);

            if (!$statement->execute()) {
                echo "Error, failed to create new token!\n";
                echo Database::get()->error;
                return -1;
            }
        }

        echo "Created $number_new_tokens new tokens!\n";

        echo "Last new token expires on {$datetime_last->format(DateTime::ISO8601)}\n";

        return 0;
    }

    /**
     * Round datetime up to the next hour
     *
     * @param DateTime $date
     * @return DateTime
     */
    protected function roundDateTime(DateTime $date): DateTime {

        $h = $date->format('H');

        if ($date->format('i') > 0 || $date->format('s')) {
            $h++;
        }

        return $date->setTime($h, 0);
    }

    /**
     * Produce random string (hexadecimal characters)
     *
     * @param int $n
     * @return string
     * @throws Exception If source of randomness could not be found
     */
    protected function getRandomString(int $n): string {

        $n_half = ceil($n / 2);
        $str = bin2hex(random_bytes($n_half)); // Byte is written as two hex characters

        if (2 * $n_half != $n) {
            $str = substr($str, 0, -1);
        }

        return $str;
    }
}