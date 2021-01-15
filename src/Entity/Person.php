<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $i_name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $f_name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getIName(): ?string
    {
        return $this->i_name;
    }

    public function setIName(string $i_name): self
    {
        $this->i_name = $i_name;

        return $this;
    }

    public function getFName(): ?string
    {
        return $this->f_name;
    }

    public function setFName(string $f_name): self
    {
        $this->f_name = $f_name;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public const ACTIVE=1;
    public const BANNED=2;
    public const DELETED=3;

    public function getStateString (int $state) : string
    {
        if ($state==1) $state_string = 'active';
        if ($state==2) $state_string = 'banned';
        if ($state==3) $state_string = 'deleted';
        
        return $state_string;
    }

}
