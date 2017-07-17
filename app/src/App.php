<?php

namespace Cj\Github;

use Cj\Github\Manager\ConfigManager;
use Cj\Github\Manager\TravisManager;
use GuzzleHttp\Client;
use Monolog\Logger;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Cj\Github\Manager\GithubIssueManager;
use Symfony\Component\Yaml\Yaml;
use Silex\Provider\MonologServiceProvider;

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

        $this->registerServices();

        $this->loadRoutes();
    }

    private function registerServices():void {
        // Register Monolog for logging
        $this->register(new MonologServiceProvider(), [
            "monolog.logfile" =>realpath(__DIR__."/../../app.log"),
            "monolog.level"=>Logger::ERROR,
            "monolog.name"=>"GithubApp"
        ]);
    }

    private function loadRoutes():void {
        $this->post("/issue", function(Request $request)  {

            // Get ID
            $id = $request->get('id')?:"";

            // Load Config Manager
            $configManager = new ConfigManager($this['config']->config);
            $configModel = $configManager->getConfigById($id)?:$configManager->getFirstConfig();

            // Generate Client
            $client = new Client();

            // Generate Travis Manager
            $travisManager = new TravisManager($client);

            // Decode Travis Payload
            $payloadString = $travisManager->getDecodedPayload($request);
            $payload = json_decode($travisManager->getDecodedPayload($request),true);

            // Handle when is not the deployment branch
            if ($payload['branch'] != $configModel->getDeployBranch()) {
                return new Response("The branch in the received payload is not the deployment branch.", Response::HTTP_FORBIDDEN);
            }
            // Handle when state is not supported
            if (!$configModel->isStateSupported($payload['state'])) {
                return new Response("Payload has an unsupported state.", Response::HTTP_FORBIDDEN);
            }

            $ghbIssueManager = new GithubIssueManager($client);

            try {
                $ghbIssueManager->postIssueOnTravisFail(
                    $configModel,
                    $payloadString
                );
            } catch (\Exception $e){
                return new Response("The issue was not posted to Github: ".$e->getMessage(), Response::HTTP_FORBIDDEN);
            }

            return new Response("The issue was posted to Github.",Response::HTTP_OK);
        });

        $this->get("/", function(Application $app){
            return new Response("Please post to the issue endpoint", 200);
        });
    }
}
