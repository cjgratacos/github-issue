<?php

namespace Cj\Github\Manager;


use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class TravisManager
{

    private const TRAVIS_SIGNATURE_HEADER = "signature";
    private const TRAVIS_CONFIG_URI = "https://api.travis-ci.org";
    private const TRAVIS_PAYLOAD = "payload";

    private $client;

    function __construct(Client $client)
    {
        $this->client = $client;
    }

    private function fetchTravisPublicKey():string {
        $response = $this->client->get(TravisManager::TRAVIS_CONFIG_URI."/config");
        $body = json_decode($response->getBody()->getContents());
        return $body->config->notifications->webhook->public_key;
    }

    private function getRequestSignature(Request $request):string {
        return base64_decode($request->headers->get(TravisManager::TRAVIS_SIGNATURE_HEADER));
    }

    public function getDecodedPayload(Request $request):string {
        return urldecode($this->getRawPayload($request));
    }

    public function getRawPayload(Request $request):string {
        return $request->get(TravisManager::TRAVIS_PAYLOAD);
    }

    public function isValidRequestSignature(Request $request): bool {

        $payload = $this->getDecodedPayload($request);
        $signature = $this->getRequestSignature($request);
        $pubKey = $this->fetchTravisPublicKey();

        return openssl_verify($payload,$signature,$pubKey) == 1;
    }

}