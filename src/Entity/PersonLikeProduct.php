<?php

namespace App\Entity;

use App\Repository\PersonLikeProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PersonLikeProduct
 * @ORM\Entity 
 * @ORM\Entity(repositoryClass=PersonLikeProductRepository::class)
 * @ORM\Table(name="person_like_product", indexes={@ORM\Index(name="fk_person_like_product_product1_idx", columns={"product_id"})})
 */
class PersonLikeProduct
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Person::class, inversedBy="PersonHaveProducts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="ProductHavePersons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;
//@ORM\JoinColumn(nullable=false, onDelete="CASCADE")  - version with cascading delete 

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
