<?php

namespace Cj\Github\Manager;

use Cj\Github\Model\ConfigModel;
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

    public function postIssueOnTravisFail(ConfigModel $configModel, string $travisResponse):void {
        $data = json_decode($travisResponse, true);
        $body = <<< EOT
Travis Deployment **#{$data['number']}** {$data['state']} for branch:[{$data['branch']}]
The Travis CI deployment:**#{$data['number']}** {$data['state']} for the branch:**[{$data['branch']}]**.
Build URL: {$data['build_url']}
Build message: `{$data['result_message']}`
Payload Data: `{$travisResponse}`
EOT;
        $this->client->post(GithubIssueManager::BASE_URI."/repos/{$configModel->getProject()}/issues", [
            "headers" => [
                "cache-control" => "no-cache",
                "content-type" => "application/json",
                "authorization" => "Basic ".base64_encode($configModel->getUsername().":".$configModel->getPassword())
            ],
            "json" => [
                "title" =>"Travis build #{$data['number']} {$data['state']}",
                "body" => $body,
                "labels"=>["help wanted","build failed"]
            ]
        ]);

    }

}