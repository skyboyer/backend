<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Product;
use App\Form\Type\ProductType;



/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options = {"unsigned":true})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $info;

    /**
     * @ORM\Column(type="date")
     */
    private $public_date;

    /**
     * @ORM\OneToMany(targetEntity=PersonLikeProduct::class, mappedBy="product")
     */
    private $ProductHavePersons;

    public function __construct()
    {
        $this->ProductHavePersons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getPublicDate(): ?\DateTimeInterface
    {
        return $this->public_date;
    }

    public function setPublicDate(\DateTimeInterface $public_date): self
    {
        $this->public_date = $public_date;

        return $this;
    }

    /**
     * @return Collection|PersonLikeProduct[]
     */
    public function getProductHavePersons(): Collection
    {
        return $this->ProductHavePersons;
    }

    public function addProductHavePerson(PersonLikeProduct $productHavePerson): self
    {
        if (!$this->ProductHavePersons->contains($productHavePerson)) {
            $this->ProductHavePersons[] = $productHavePerson;
            $productHavePerson->setProduct($this);
        }

        return $this;
    }

    public function removeProductHavePerson(PersonLikeProduct $productHavePerson): self
    {
        if ($this->ProductHavePersons->removeElement($productHavePerson)) {
            // set the owning side to null (unless already changed)
            if ($productHavePerson->getProduct() === $this) {
                $productHavePerson->setProduct(null);
            }
        }

        return $this;
    }
}
