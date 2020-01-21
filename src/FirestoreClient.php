<?php
namespace MrShan0\PHPFirestore;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use Guzzle\Http\Message\Response;
use MrShan0\PHPFirestore\Authentication\FirestoreAuthentication;
use MrShan0\PHPFirestore\Handlers\RequestErrorHandler;
use MrShan0\PHPFirestore\Helpers\FirestoreHelper;

/**
 * @method array listDocuments($collection, array $parameters = [], array $options = [])
 * @method FirestoreDocument getDocument($documentPath, array $parameters = [], array $options = [])
 * @method array getBatchDocuments(array $documentsId, array $parameters = [], array $options = [])
 * @method FirestoreDocument addDocument($collection, $payload, $documentId = null, array $parameters = [], array $options = [])
 * @method FirestoreDocument updateDocument($documentPath, $payload, $documentExists = null, array $parameters = [], array $options = [])
 * @method FirestoreDocument setDocument($documentPath, $payload, $documentExists = null, array $parameters = [], array $options = [])
 * @method boolean deleteDocument($document, array $options = [])
 */
class FirestoreClient
{
    /**
     * Firestore REST API Base URL
     *
     * @var string
     */
    private $apiRoot = 'https://firestore.googleapis.com/v1/';

    /**
     * To hold project-id of Firestore application.
     *
     * @var string
     */
    private $project;

    /**
     * Private Server API Key for Firestore
     *
     * @var string
     */
    private $apiKey;

    /**
     * GuzzleClient object
     *
     * @var GuzzleClient
     */
    private $guzzle;

    /**
     * Options options control various aspects of a request including, headers, query string parameters, timeout settings, the body of a request
     *
     * @var array
     */
    private $options = ['debug' => true];

    /**
     * Set last response from API
     *
     * @var string
     */
    private $lastResponse;

    /**
     * FirestoreAuthentication object
     *
     * @var \MrShan0\PHPFirestore\Authentication\FirestoreAuthentication
     */
    private $authenticator;

    /**
     * FirestoreQuery object
     *
     * @var FirestoreQuery
     */
    private $structuredQuery;

    /**
     * Config container for internal purposes
     *
     * @var array
     */
    private static $config;

    /**
     * Client constructor.
     *
     * @param string            $projectId
     * @param string            $apiKey
     * @param array             $config
     * @param array             $options
     * @param GuzzleClient|null $guzzle
     */
    public function __construct($projectId, $apiKey, array $config = [], GuzzleClient $guzzle = null, $options = [])
    {
        $this->setHttpClient($guzzle ?: new GuzzleClient());
        $this->setApiKey($apiKey);
        $this->setOptions($options);

        $this->setConfig(array_merge($config, [
            'database' => '(default)',
            'projectId' => $projectId,
        ]));
    }

    /**
     * Config setter
     *
     * @param array $configs
     * @return void
     */
    public static function setConfig(array $configs)
    {
        self::$config = $configs;
    }

    /**
     * Get configs
     *
     * @param string $key
     * @return mixed
     */
    public static function getConfig($key)
    {
        return array_key_exists($key, self::$config) ? self::$config[$key] : null;
    }

    /**
     * @param GuzzleClient $client
     *
     * @return void
     */
    public function setHttpClient(GuzzleClient $client)
    {
        $this->guzzle = $client;
    }

    /**
     * @return mixed
     */
    public function getHttpClient()
    {
        return $this->guzzle;
    }

    /**
     * Set a key for authentication with checkr.
     *
     * @param $apiKey
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get the firestore api key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Return firestore relative database path
     *
     * @return string
     */
    public static function getRelativeDatabasePath()
    {
        return 'projects/' . self::getConfig('projectId') . '/databases/' . self::getConfig('database');
    }

    public function getApiEndPoint()
    {
        return $this->apiRoot;
    }

    /**
     * @param bool $status
     *
     * @return void
     */
    public function setLastResponse($response)
    {
        $this->lastResponse = $response;
    }

    /**
     * @return Guzzle\Http\Message\Response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Set options for HttpClient.
     *
     * @param array $options
     *
     * @return array
     */
    public function setOptions(array $options): array
    {
        return $this->options = $options;
    }

    /**
     * Set individual option.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setOption($key, $value)
    {
        return $this->options[$key] = $value;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param $key
     *
     * @return bool|mixed
     * @return void
     */
    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return false;
    }

    /**
     * Compile final url to hit service.
     *
     * @return string
     */
    private function constructUrl($method, $params=null)
    {
        $params     = is_array($params) ? $params : [];
        $builtQuery = (count($params) ? '&' . http_build_query($params) : '');

        if ( count(preg_grep('~\.fieldPaths~', array_keys($params))) ) {
            $builtQuery = preg_replace('/%5B\d+%5D/', '', $builtQuery);
        }

        return (
            $this->getApiEndPoint() . $this->getRelativeDatabasePath() . '/' . $method . '?key=' . $this->getApiKey() . $builtQuery
        );
    }

    /**
     * @return \MrShan0\PHPFirestore\Authentication\FirestoreAuthentication
     */
    public function getFirestoreAuth()
    {
        if ($this->authenticator) {
            return $this->authenticator;
        }

        $this->authenticator = new FirestoreAuthentication($this);

        return $this->authenticator;
    }

    /**
     * @return \MrShan0\PHPFirestore\Query\FirestoreQuery
     */
    public function getFirestoreQuery()
    {
        if ($this->structuredQuery) {
            return $this->structuredQuery;
        }

        $this->structuredQuery = new FirestoreQuery($this);

        return $this->structuredQuery;
    }

    /**
     * Fetch an API resource to handle the client request.
     *
     * @param string                               $name
     * @param array                                $args
     *
     * @return mixed
     */
    private function api($name, $args)
    {
        $firestoreInstance = new FirestoreDatabaseResource($this);

        return call_user_func_array([$firestoreInstance, $name], $args);
    }

    /**
     * Call Firestore resource method.
     *
     * @param $name
     * @param $args
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        switch ($name) {
            case 'authenticator':
                return $this->getFirestoreAuth();
            case 'query':
                return $this->getFirestoreQuery();
                break;
            default:
                return $this->api($name, $args);
        }

    }

    /**
     * Make a request through Guzzle.
     *
     * @param $method
     * @param $path
     * @param array $options
     * @param array $parameters
     *
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\BadRequest
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Conflict
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Forbidden
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\NotFound
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Unauthorized
     * @throws \MrShan0\PHPFirestore\Exceptions\Server\InternalServerError
     * @throws \MrShan0\PHPFirestore\Exceptions\UnhandledRequestError
     *
     * @return mixed
     */
    public function request($method, $path, array $options = [], array $parameters = [])
    {
        $body = '';
        $options = array_merge($this->getOptions(), $options);

        try {
            $response = $this->getHttpClient()->request($method, $this->constructUrl($path, $parameters), $options);
            $this->setLastResponse($response);
            $body = FirestoreHelper::decode((string) $response->getBody());
        } catch (BadResponseException $exception) {
            $this->setLastResponse($exception->getResponse());
            $this->handleError($exception);
        }

        return $body;
    }

    /**
     *  Throw our own custom handler for errors.
     *
     * @param BadResponseException $exception
     *
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\BadRequest
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Conflict
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Forbidden
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\NotFound
     * @throws \MrShan0\PHPFirestore\Exceptions\Client\Unauthorized
     * @throws \MrShan0\PHPFirestore\Exceptions\Server\InternalServerError
     * @throws \MrShan0\PHPFirestore\Exceptions\UnhandledRequestError
     *
     */
    private function handleError(Exception $exception)
    {
        $handler = new RequestErrorHandler($exception);
        $handler->handleError();
    }
}
