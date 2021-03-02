<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\AbstractType;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options = {"unsigned":true})
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
     * @ORM\Column(type="smallint", options = {"unsigned":true} )
     */
    private $state;

    /**
     * @ORM\OneToMany(targetEntity=PersonLikeProduct::class, mappedBy="person")
     */
    private $PersonHaveProducts;

    public function __construct()
    {
        $this->PersonHaveProducts = new ArrayCollection();
    }

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

    public function getStateString () : string
    {
        if ($this->getState()==Person::ACTIVE) $state_string = 'ACTIVE';
        if ($this->getState()==Person::BANNED) $state_string = 'BANNED';
        if ($this->getState()==Person::DELETED) $state_string = 'DELETED';
        
        return $state_string;
    }

    /**
     * @return Collection|PersonLikeProduct[]
     */
    public function getPersonHaveProducts(): Collection
    {
        return $this->PersonHaveProducts;
    }

    public function addPersonHaveProduct(PersonLikeProduct $personHaveProduct): self
    {
        if (!$this->PersonHaveProducts->contains($personHaveProduct)) {
            $this->PersonHaveProducts[] = $personHaveProduct;
            $personHaveProduct->setPerson($this);
        }

        return $this;
    }

    public function removePersonHaveProduct(PersonLikeProduct $personHaveProduct): self
    {
        if ($this->PersonHaveProducts->removeElement($personHaveProduct)) {
            // set the owning side to null (unless already changed)
            if ($personHaveProduct->getPerson() === $this) {
                $personHaveProduct->setPerson(null);
            }
        }

        return $this;
    }

}
