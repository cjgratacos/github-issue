<?php

namespace Cj\Github\Manager;


use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

class TravisManager
{

    private const TRAVIS_SIGNATURE_HEADER = "signature";
    private const TRAVIS_CONFIG_URI = "https://api.travis-ci.org";
    private const TRAVIS_PAYLOAD = "payload";
    private const TRAVIS_PULL_REQUEST = "pull_request";

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

    private function getPayloadAsArray(Request $request):array {
        return json_decode($this->getRawPayload($request), true);
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

    public function getRepoInfo(Request $request): array {
        $payload = $this->getPayloadAsArray($request);

        return $payload['repository'];
    }

    public function getRepoSlug(Request $request): string {
        $repo = $this->getRepoInfo($request);
        return $repo['owner_name'] .'/'.$repo['name'];
    }

    public function isBuildTypePullRequest(Request $request):bool {
        $build = $this->getPayloadAsArray($request);

        return $build['type'] === TravisManager::TRAVIS_PULL_REQUEST;
    }
}