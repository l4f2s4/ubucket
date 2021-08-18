<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="repo")
 * @ORM\Entity(repositoryClass="App\Repository\RepoRepository")
 */
class Repo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visibility;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="repos", fetch="EAGER")
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupT", inversedBy="repos")
     * 
     */
    private $groupId;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getVisibility(): ?bool
    {
        return $this->visibility;
    }

    public function setVisibility(bool $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getGroupId(): ?GroupT
    {
        return $this->groupId;
    }

    public function setGroupId(?GroupT $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }
}
