<?php

namespace Cj\Github;

use Cj\Github\Manager\ConfigManager;
use Cj\Github\Manager\TravisManager;
use GuzzleHttp\Client;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Cj\Github\Manager\GithubIssueManager;
use Symfony\Component\Yaml\Yaml;

class App extends Application{

    static function init():void{
        $application = new App();
        $application->run();
    }

    /**
     * App constructor.
     */
    public function __construct()
    {
        $config_path = __DIR__ . "/../../config.yml";

        $config = (object) Yaml::parse(file_get_contents($config_path));

        parent::__construct([
            "config" => $config
        ]);

        $this->loadRoutes();
    }

    private function loadRoutes():void {
        $this->post("/issue", function(Request $request)  {

            $configManager = new ConfigManager($this['config']);
            $id = $request->query->get('id');
            if (!$id) {
                return new Response("No ID field was provided", Response::HTTP_PRECONDITION_FAILED);
            }

            if (!$configManager->isValidId($id)) {
                return new Response("Invalid ID", Response::HTTP_UNAUTHORIZED);
            }

            $client = new Client();
            $travisManager = new TravisManager($client);

            if(!$travisManager->isValidRequestSignature($request)) {
                return new Response("Invalid Signature", Response::HTTP_FORBIDDEN);
            }

            $payload = urldecode($travisManager->getPayload($request));

            $ghbIssueManager = new GithubIssueManager($client);

            try {
                $ghbIssueManager->postIssueOnTravisFail(
                    $configManager->getAuthModel(),
                    json_decode($payload)
                );
            } catch (\Exception $e){
                return new Response("The issue was not posted to Github: ".$e->getMessage(), Response::HTTP_FORBIDDEN);
            }

            return new Response("The issue was posted to Github.",Response::HTTP_OK);
        });

        $this->get("/", function(Application $app){
            return new Response("Please post to the issue endpoint with the given key", 200);
        });
    }
}
