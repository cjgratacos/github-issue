<?php

namespace Cj\Github\Manager;


use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class TravisManager
{

    private const  TRAVIS_SIGNATURE_HEADER = "HTTP_SIGNATURE";
    private const TRAVIS_CONFIG_URI = "https://api.travis-ci.org";

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

    public function getPayload(Request $request):string {
        return $request->get('payload');
    }

    public function isValidRequestSignature(Request $request): bool {
        $pubKey = $this->fetchTravisPublicKey();
        $signature = $this->getRequestSignature($request);
        $payload = $this->getPayload($request);
        return openssl_verify($payload,$signature,$pubKey) == 1;
    }

}