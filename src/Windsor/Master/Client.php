<?php namespace Windsor\Master;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise as GuzzlePromise;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var GuzzleClient
     */
    protected $guzzle;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param GuzzleClient $guzzle
     */
    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @return GuzzleClient
     */
    public function getGuzzle()
    {
        return $this->guzzle;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function authenticate($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool  $async
     * @param bool  $wait
     * @return GuzzlePromise\PromiseInterface|mixed
     * @throws Exception
     */
    public function call($method, $uri, $options = [], $async = false, $wait = false)
    {
        if (null !== $this->token) {
            $authHeaders = ['Access-Token' => $this->token];

            $options['headers'] = isset($options['headers']) ? array_merge($options['headers'], $authHeaders) : $authHeaders;
        }

        if ($async) {
            return $this->callAsync($method, $uri, $options, $wait);
        }

        try {
            $response = $this->guzzle->request($method, $uri, $options);

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw $this->handleException($e);
        }
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     * @param bool $wait
     * @return GuzzlePromise\PromiseInterface|mixed
     * @throws Exception
     */
    public function callAsync($method, $uri, $options, $wait = false)
    {
        $promise = $this->guzzle->requestAsync($method, $uri, $options);

        if ($wait) {
            try {
                $response = $promise->wait();

                return $this->parseResponse($response);
            } catch (RequestException $e) {
                throw $this->handleException($e);
            }
        }

        return $promise;
    }

    /**
     * @param $promises
     * @return array
     * @throws Exception
     */
    public function processAsyncCalls($promises)
    {
        $results = GuzzlePromise\settle($promises)->wait();

        $responses = [];
        foreach ($results as $key => $result) {
            if ($result['state'] === 'fulfilled') {
                $response = $result['value'];
                $responses[$key] = $this->parseResponse($response);
            } else if ($result['state'] === 'rejected') {
                $error = $result['reason'];

                throw $this->handleException($error);
            }
        }

        return $responses;
    }

    /**
     * @param $uri
     * @param array $options
     * @return mixed
     */
    public function get($uri, array $options = [], $async = false, $wait = false)
    {
        return $this->call('GET', $uri, $options, $async, $wait);
    }

    /**
     * @param string $uri
     * @param array $options
     * @param bool  $async
     * @param bool  $wait
     * @return GuzzlePromise\PromiseInterface|mixed
     */
    public function put($uri, $options = [], $async = false, $wait = false)
    {
        return $this->call('PUT', $uri, $options, $async, $wait);
    }

    /**
     * @param string $uri
     * @param array $options
     * @param bool  $async
     * @param bool  $wait
     * @return GuzzlePromise\PromiseInterface|mixed
     */
    public function post($uri, $options = [], $async = false, $wait = false)
    {
        return $this->call('POST', $uri, $options, $async, $wait);
    }

    /**
     * @param string $uri
     * @param array $options
     * @param bool  $async
     * @param bool  $wait
     * @return mixed
     */
    public function delete($uri, $options = [], $async = false, $wait = false)
    {
        return $this->call('DELETE', $uri, $options, $async, $wait);
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    protected function parseResponse(ResponseInterface $response)
    {
        return $response->getBody()->getContents();
    }

    /**
     * @param RequestException $exception
     * @return Exception
     */
    protected function handleException(RequestException $exception)
    {
        $response = $exception->getResponse();
        $message = $response->getReasonPhrase();
        $code = $response->getStatusCode();

        if ($this->isJson($response)) {
            $json = json_decode($this->parseResponse($exception->getResponse()), true);
            $message = $json;
        }

        return new Exception($message, $code, $exception);
    }

    protected function isJson(ResponseInterface $response)
    {
        return $response->getHeader('Content-type')[0] == 'application/json';
    }
}
