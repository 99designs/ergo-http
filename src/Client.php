<?php

namespace Ergo\Http;

use Ergo\Http;

/**
 * A simple HTTP client
 */
class Client
{
    const MAXredirects = 10;

    private $url;
    private $redirects = 0;
    private $filters = array();
    private $headers = array();


    public static $requestCount = 0;
    public static $requestTime = 0;

    private static $transport;

    /**
     * @param string $url
     * @throws \InvalidArgumentException
     */
    public function __construct($url)
    {
        if (!$url) {
            throw new \InvalidArgumentException('A base url must be set');
        }
        $this->url = new Url($url);
    }

    public static function transport($transport = null)
    {
        if (!is_null($transport)) {
            self::$transport = $transport;
        }

        if (!isset(self::$transport)) {
            self::$transport = new Transport();
        }

        return self::$transport;
    }

    /**
     * Adds an HTTP header to all requests
     * @chainable
     */
    public function addFilter(ClientFilter $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Adds an HTTP header to all requests
     * @param mixed either a string or a HeaderField
     * @return $this
     */
    public function addHeader($header)
    {
        if (is_string($header)) {
            $header = HeaderField::fromString($header);
        }
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Sets an HTTP proxy to use
     */
    public function setHttpProxy($url)
    {
        self::transport()->setHttpProxy($url);
        return $this;
    }

    /**
     *    Sets HTTP authentication credentials
     */
    public function setHttpAuth($user, $pass)
    {
        self::transport()->setHttpAuth($user, $pass);
        return $this;
    }

    /**
     * Sets the total timeout in seconds
     * @chainable
     */
    public function setTimeout($seconds)
    {
        self::transport()->setTimeout($seconds);
        return $this;
    }

    /**
     * Sets the connection timeout in milliseconds
     * @chainable
     */
    public function setConnectTimeoutMs($milliseconds)
    {
        self::transport()->setConnectTimeoutMs($milliseconds);
        return $this;
    }

    /**
     * Sets the IP Family to use when making requests.
     * Defaults to negotiating with the other end
     */
    public function setIPFamily($family)
    {
        self::transport()->setIPFamily($family);
    }

    /**
     * Return the base url this Client was instanciated with
     * @return \Ergo\Http\Url
     */
    public function getBaseUrl()
    {
        return $this->url;
    }

    /**
     * Sends a POST request
     * @return Response
     */
    function post($path, $body, $contentType = null)
    {
        return $this->dispatchRequest(
            $this->buildRequest('POST', $path, $body, $contentType)
        );
    }

    /**
     * Sends a PUT request
     * @return Response
     */
    function put($path, $body, $contentType = null)
    {
        return $this->dispatchRequest(
            $this->buildRequest('PUT', $path, $body, $contentType)
        );
    }

    /**
     * Sends a GET request
     * @return Response
     */
    function get($path)
    {
        return $this->dispatchRequest(
            $this->buildRequest('GET', $path)
        );
    }

    /**
     * Sends a DELETE request
     * @return Response
     */
    function delete($path)
    {
        return $this->dispatchRequest(
            $this->buildRequest('DELETE', $path)
        );
    }

    /**
     * Builds an Request object
     */
    private function buildRequest($method, $path, $body = null, $contentType = null)
    {
        // copy default headers
        $headers = $this->headers;

        // add Content-Type header if provided
        if ($contentType)
            $headers [] = new HeaderField('Content-Type', $contentType);

        $request = new Request(
            $method,
            $this->url->getUrlForRelativePath($path),
            $headers,
            $body
        );

        // pass the request through the filter chain
        foreach ($this->filters as $filter) {
            $request = $filter->request($request);
        }

        return $request;
    }

    /**
     * Dispatches a request via CURL
     */
    private function dispatchRequest($request)
    {
        // track the number of requests across instances
        self::$requestCount++;
        $timestart = microtime(true);

        $response = self::transport()->send($request);

        // pass the response through the filter chain
        foreach ($this->filters as $filter) {
            $response = $filter->response($response);
        }

        $httpCode = $response->getStatus()->getCode();
        $location = $response->getHeaders()->value('Location');
        $body = $response->getBody();

        // track the time taken across instances
        self::$requestTime += microtime(true) - $timestart;

        // process a redirect if needed
        if ($httpCode < 400 && $location) {
            return $this->redirect($location);
        } else {
            $this->redirects = 0;
        }

        // translate error code to a typed exception
        if ($httpCode == 500) {
            throw new Error\InternalServerError($body);
        } elseif ($httpCode == 400) {
            throw new Error\BadRequest($body);
        } elseif ($httpCode == 401) {
            throw new Error\Unauthorized($body);
        } elseif ($httpCode == 404) {
            throw new Error\NotFound($body);
        } elseif ($httpCode >= 300) {
            throw new Error\Exception($body, $httpCode);
        }

        return $response;
    }

    /**
     * Redirect to a new url
     */
    private function redirect($location)
    {
        $locationUrl = new Url($location);

        // if the location header was relative (bleh) add the host
        if (!$locationUrl->hasHost()) {
            $locationUrl = $this->url->getUrlForPath($location);
        }

        if ($this->redirects > self::MAXredirects) {
            throw new Error\BadRequest("Exceeded maximum redirects");
        }

        $this->redirects++;

        return $this->dispatchRequest(
            new Request('GET', $locationUrl, $this->headers)
        );
    }
}
