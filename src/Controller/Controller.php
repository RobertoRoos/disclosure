<?php

namespace App\Controller;

/**
 * Template for page controllers
 */
abstract class Controller
{
    const POST = "POST";
    const GET = "GET";

    /** @var array Copy of $_POST */
    private $data;

    /** @var string Method type */
    private $method;

    /**
     * @var string $host Current server host
     * @var string $uri Current URL (incl. query params)
     * @var array $query Query parameters
     */
    private $host, $uri, $query;

    /** @var array View variables */
    private $view_vars;

    /**
     * @param array $server     _SERVER
     * @param array $post       _POST
     */
    public function __construct(array $server, array $post) {
        $this->data = $post;

        if ($server['REQUEST_METHOD'] == self::POST) {
            $this->method = self::POST;
        } else {
            $this->method = self::GET;
        }

        $this->host = $server['SERVER_NAME'];
        $this->uri = $server['REQUEST_URI'];

        $query_string = parse_url($this->uri,PHP_URL_QUERY);
        parse_str($query_string, $this->query);
    }

    /**
     * @param string|null $key
     * @param mixed $default
     * @return mixed POST data
     */
    public function getData(string $key = null, $default = null) {

        if (is_null($key)) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * @return string self::POST or self::GET
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * @return string Current host (e.g. "example.com")
     */
    public function getHost(): string {
        return $this->host;
    }

    /**
     * @return string Current Url (e.g. "/disclosure/index.php")
     */
    public function getUrl(): string {
        return parse_url($this->uri, PHP_URL_PATH);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed A single or all query parameters
     */
    public function getQuery($key = null, $default = null) {
        if (is_null($key)) {
            return $this->query;
        }

        return $this->query[$key] ?? $default;
    }

    /**
     * Return true if POST data contains keys (and data is not null or empty strings)
     *
     * @param array|string $keys
     * @return void
     */
    protected function hasData($keys): bool {

        if (is_array($keys)) {
            foreach ($keys as $key) {
                if (empty($this->data[$key])) {
                    return false;
                }
            }
        } else {
            if (empty($this->data[$keys])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get view variable
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getVar(string $key, $default = null) {
        return $this->view_vars[$key] ?? $default;
    }

    /**
     * Set view variable
     *
     * @param string $key
     * @param        $value
     */
    protected function setVar(string $key, $value) {
        $this->view_vars[$key] = $value;
    }

}
