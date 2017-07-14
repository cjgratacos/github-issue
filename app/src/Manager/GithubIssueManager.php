<?php

namespace Cj\Github\Manager;

use Cj\Github\Model\AuthModel;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;
use \stdClass;

class GithubIssueManager {

    private const BASE_URI = "https://api.github.com";

    private $client;

    function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function postIssueOnTravisFail(AuthModel $authModel, stdClass $travisResponse):void {

        $body = <<< EOT
        Travis Deployment #{$travisResponse->number} failed for branch:[{$travisResponse->branch}]
        The Travis CI deployment number:**{$travisResponse->number}** failed for the branch:**[{$travisResponse->branch}]**.
        Build URL: {$travisResponse->build_url}
        Build message: {$travisResponse->message}
EOT;
        $this->client->post(GithubIssueManager::BASE_URI."/repos/{$authModel->getProject()}/issues", [
            "headers" => [
                "cache-control" => "no-cache",
                "content-type" => "application/json",
                "authorization" => "Basic ".base64_encode($authModel->getUsername().":".$authModel->getPassword())
            ],
            "json" => [
                "title" =>"Travis build #{$travisResponse->number} failed",
                "body" => $body,
                "labels"=>["help wanted","build failed"]
            ]
        ]);

    }

}