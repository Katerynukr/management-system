<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, inversedBy="users")
     */
    private $grades;

    public function __construct()
    {
        $this->grades = new ArrayCollection();
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

    /**
     * @return Collection|Group[]
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Group $grade): self
    {
        if (!$this->grades->contains($grade)) {
            $this->grades[] = $grade;
        }

        return $this;
    }

    public function removeGrade(Group $grade): self
    {
        $this->grades->removeElement($grade);

        return $this;
    }
}
