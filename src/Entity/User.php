<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;


/**
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * 
 * 
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $Firstname;
    
    /**
     * @ORM\Column(type="string", length=10)
     */
    private $Username;

     /**
     * @ORM\Column(type="string", length=225)
     * 
     * 
     *  
     */
    private $Email;

    /**
     * 
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $Password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $resetToken;
     /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;
      /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\GroupT", mappedBy="users")
     */
    private $groupTs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Repo", mappedBy="userId", cascade="persist")
     */
    private $repos;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userimage;

    /**
     * @ORM\OneToMany(targetEntity=Messageholder::class, mappedBy="SentBy", cascade="remove")
     */
    private $usersent;

    
    public function __construct()
    {
        $this->groupTs = new ArrayCollection();
        $this->repos = new ArrayCollection();
        $this->usersent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->Firstname;
    }

    public function setFirstname(string $Firstname): self
    {
        $this->Firstname = $Firstname;

        return $this;
    }
    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): self
    {
        $this->Username = $Username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): self
    {
        $this->Password = $Password;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->Email;
    }
    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }
    public function getResetToken(): string
    {
        return $this->resetToken;
    }
    public function setResetToken(?string $resetToken): void
    {
        $this->resetToken = $resetToken;
    }

    public function getSalt(){
        return null;
    }
    public function eraseCredentials(){
        return null;
    }

    /**
     * @return Collection|GroupT[]
     */
    public function getGroupTs(): Collection
    {
        return $this->groupTs;
    }

    public function addGroupT(GroupT $groupT): self
    {
        if (!$this->groupTs->contains($groupT)) {
            $this->groupTs[] = $groupT;
            $groupT->addUser($this);
        }

        return $this;
    }

    public function removeGroupT(GroupT $groupT): self
    {
        if ($this->groupTs->contains($groupT)) {
            $this->groupTs->removeElement($groupT);
            $groupT->removeUser($this);
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
            $repo->setUserId($this);
        }

        return $this;
    }

    public function removeRepo(Repo $repo): self
    {
        if ($this->repos->contains($repo)) {
            $this->repos->removeElement($repo);
            // set the owning side to null (unless already changed)
            if ($repo->getVisibility() === $this) {
                $repo->setVisibility(false);
            }
        }

        return $this;
    }

   /** Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */

    public function getRoles()
    {
      //  $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
       // $roles[] = 'ROLE_USER';

        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
       public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUserimage(): ?string
    {
        return $this->userimage;
    }

    public function setUserimage(?string $userimage): self
    {
        $this->userimage = $userimage;

        return $this;
    }

    /**
     * @return Collection|Messageholder[]
     */
    public function getUsersent(): Collection
    {
        return $this->usersent;
    }

    public function addUsersent(Messageholder $usersent): self
    {
        if (!$this->usersent->contains($usersent)) {
            $this->usersent[] = $usersent;
            $usersent->setSentBy($this);
        }

        return $this;
    }

    public function removeUsersent(Messageholder $usersent): self
    {
        if ($this->usersent->removeElement($usersent)) {
            // set the owning side to null (unless already changed)
            if ($usersent->getSentBy() === $this) {
                $usersent->setSentBy(null);
            }
        }

        return $this;
    }

    
}
