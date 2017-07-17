<?php

namespace Cj\Github\Manager;

use Cj\Github\Model\ConfigModel;
use stdClass;

class ConfigManager
{

    private $config;

    function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConfigById(string $id): ?ConfigModel {
        $element = $this->getElement($id);
        return !empty($element) ? $this->createConfigModel($element): null;
    }

    public function getFirstConfig(): ConfigModel {
        $element = $this->config[0];
        return $this->createConfigModel($element);
    }
    private function createConfigModel(array $element):ConfigModel{
        return new ConfigModel(
            $element['credentials']['username'],
            $element['credentials']['password'],
            $element['project'],
            $element['deploy_branch'],
            $element['states']
        );
    }
    private function getElement(string $id): array {

        foreach ($this->config as $element) {
            if ($element['id'] == $id) {
                return $element;
            }
        }

        return [];
    }
}