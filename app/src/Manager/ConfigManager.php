<?php

namespace Cj\Github\Manager;

use Cj\Github\Model\AuthModel;
use stdClass;

class ConfigManager
{

    private $config;
    private $authModel;

    function __construct(stdClass $config)
    {
        $this->config = $config;
        $this->authModel = new AuthModel(
            $config->auth['id'],
            $config->auth['credentials']['username'],
            $config->auth['credentials']['password'],
            $config->auth['credentials']['project']
        );
    }

    public function isValidId(string $id):bool {
        return $this->authModel->getId() == $id;
    }

    public function getAuthModel():AuthModel{
        return $this->authModel;
    }
}