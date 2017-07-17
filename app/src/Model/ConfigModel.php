<?php

namespace Cj\Github\Model;

class ConfigModel
{

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $deployBranch;

    /**
     * @var array
     */
    private $states;

    /**
     * ConfigModel constructor.
     * @param string $username
     * @param string $password
     * @param string $project
     * @param string $deployBranch
     * @param array $status
     */
    function __construct(string $username, string $password, string $project, string $deployBranch, array $states)
    {
        $this->username = $username;
        $this->password = $password;
        $this->project = $project;
        $this->deployBranch = $deployBranch;
        $this->states = $states;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getDeployBranch(): string
    {
        return $this->deployBranch;
    }

    /**
     * @return array
     */
    public function getListOfStates(): array
    {
        return $this->states;
    }

    /**
     * @param string $state
     * @return bool
     */
    public function isStatusSupported(string $state): bool {
        foreach ($this->states as $element) {
            if(strpos($state, $element) !== false) {
                return true;
            }
        }
        return false;
    }
}