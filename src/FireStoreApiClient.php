<?php

namespace MrShan0\PHPFirestore;

use MrShan0\PHPFirestore\FireStoreDocument;
use MrShan0\PHPFirestore\FireStoreErrorCodes;
use MrShan0\PHPFirestore\Helpers\FireStoreHelper;

class FireStoreApiClient {

    /**
     * Firestore REST API Base URL
     *
     * @var string
     */
    private $apiRoot = 'https://firestore.googleapis.com/v1beta1/';

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
     * Config container for internal purposes
     *
     * @var array
     */
    private static $config;

    public function __construct($project, $apiKey, $config=array())
    {
        $this->project = $project;
        $this->apiKey = $apiKey;

        $this->setConfig(array_merge($config, [
            'database' => '(default)',
            'project' => $project,
        ]));
    }

    public static function setConfig($configs)
    {
        self::$config = $configs;
    }

    public static function getConfig($key)
    {
        return array_key_exists($key, self::$config) ? self::$config[$key] : null;
    }

    public function getDocument($collectionName, $documentId=null)
    {
        $documentPath = 'documents/' . FireStoreHelper::normalizeCollection($collectionName . (null === $documentId ? '' : "/$documentId"));
        if ($response = $this->get($documentPath)) {
            $object = FireStoreHelper::decode($response);

            if ( FireStoreDocument::isValidDocument($object) ) {
                return new FireStoreDocument($object);
            } else {
                throw new \Exception('Document does not exist.', FireStoreErrorCodes::DOCUMENT_NOT_FOUND);
            }
        }

        throw new \Exception('Error while parsing response from FireStore', FireStoreErrorCodes::UNABLE_TO_RESOLVE_REQUEST);
    }

    public function batchGet(array $documents, $params=[])
    {
        $payload = [];
        foreach ($documents as $document) {
            $payload[] = 'projects/' . $this->project . '/' . 'databases/(default)/documents/' . $document;
        }

        $response = $this->post(
            'documents:batchGet',
            $params,
            FireStoreHelper::encode([
                'documents' => $payload
            ])
        );

        $result = [];
        foreach (FireStoreHelper::decode($response) as $document) {
            $result[] = new FireStoreDocument($document['found']);
        }

        return $result;
    }

    public function addDocument($collectionName, $payload, $newDocumentId=null, $params=[])
    {

        if ( !($payload instanceof FireStoreDocument) ) {
            $document = new FireStoreDocument();
        } else {
            $document = $payload;
        }

        // Set document id
        if ( $newDocumentId ) {
            $params['documentId'] = $newDocumentId;
        }

        if ( is_array($payload) ) {
            $document->fillValues($payload);
        }

        return $this->post(
            "documents/$collectionName",
            $params,
            $document->toJson()
        );
    }

    public function updateDocument($collectionName, $documentId, $payload, $documentExists=null, array $params=[])
    {
        $document = new FireStoreDocument();

        if ($payload instanceof FireStoreDocument) {
            $document = $payload;
        } elseif ( is_array($payload) ) {
            $document->fillValues($payload);
        }

        if ($documentExists !== null) {
            $params['currentDocument.exists'] = !!$documentExists;
        }

        $params['updateMask.fieldPaths'] = array_unique(array_keys($document->toArray()));

        return $this->patch(
            "documents/$collectionName/$documentId",
            $params,
            $document->toJson()
        );
    }

    public function setDocument($collectionName, $documentId, $payload, array $params=[])
    {

        $document = new FireStoreDocument();

        if ($payload instanceof FireStoreDocument) {
            $document = $payload;
        } elseif ( is_array($payload) ) {
            $document->fillValues($payload);
        }

        if ( array_key_exists('exists', $params) ) {
            $params['currentDocument.exists'] = !!$params['exists'];
            unset($params['exists']);
        }

        if ( array_key_exists('merge', $params) && $params['merge'] === true ) {
            $params['updateMask.fieldPaths'] = array_unique(array_keys($document->toArray()));
            unset($params['merge']);
        }

        return $this->patch(
            "documents/$collectionName/$documentId",
            $params,
            $document->toJson()
        );
    }

    public function deleteDocument($collectionName, $documentId)
    {
        return $this->delete(
            "documents/$collectionName/$documentId", []
        );
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

        if ( array_key_exists('updateMask.fieldPaths', $params) ) {
            $builtQuery = preg_replace('/%5B\d%5D/', '', $builtQuery);
        }

        return (
            $this->apiRoot . 'projects/' . $this->project . '/' .
            'databases/(default)/' . $method . '?key=' . $this->apiKey . $builtQuery
        );
    }

    private function get($method, $params=null)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function post($method, $params, $postBody)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($postBody)),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function put($method, $params, $postBody)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($postBody)),
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function patch($method, $params, $postBody)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json', 'Content-Length: ' . strlen($postBody)),
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $postBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function delete($method, $params)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

}
