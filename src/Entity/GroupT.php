<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupTRepository")
 */
class GroupT
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
    private $owner;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="groupTs")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Repo", mappedBy="groupId")
     * 
     */
    private $repos;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->repos = new ArrayCollection();
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

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return Collection|Repo[]
     */
    public function getRepos(): Collection
    {
        return $this->repos;
    }

    public function addRepo(Repo $repo): self
    {
        if (!$this->repos->contains($repo)) {
            $this->repos[] = $repo;
            $repo->setGroupId($this);
        }

        return $this;
    }

    public function removeRepo(Repo $repo): self
    {
        if ($this->repos->contains($repo)) {
            $this->repos->removeElement($repo);
            // set the owning side to null (unless already changed)
            if ($repo->getGroupId() == $this && $repo->getVisibility()== $this) {
                $repo->setGroupId(null);
                $repo->setVisibility(false);
            }
        }

        return $this;
    }
   /**
     * @return Collection|User[]
     */
   public function getUserToGroup()
    {
        return $this->users;
    }

    public function addUserToGroup(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addGroupT($this);
        }

        return $this;
    }
    public function removeUserToGroup(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeGroupT($this);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     * 
     */
public function removeGrp(Repo $repo): self
    {
        if ($this->repos->contains($repo)) {
            $this->repos->removeElement($repo);
            // set the owning side to null (unless already changed)
            if ($repo->getGroupId() == $this && $repo->getVisibility()== $this) {
                $repo->setGroupId(null);
                $repo->setVisibility(true);
            }
        }

        return $this;
    }    
}
