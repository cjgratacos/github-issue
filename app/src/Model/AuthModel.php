<?php

namespace Cj\Github\Model;

class AuthModel
{
    /**
     * @var string
     */
    private $id;

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
     * AuthModel constructor.
     * @param string $id
     * @param string $username
     * @param string $password
     * @param string $project
     */
    function __construct(string $id, string $username, string $password, string $project)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->project = $project;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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


}