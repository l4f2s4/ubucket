<?php

namespace App\Entity;

use App\Repository\MessageholderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessageholderRepository::class)
 */
class Messageholder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $msg;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $messagetimes;

    /**
     * @ORM\ManyToOne(targetEntity=GroupT::class)
     */
    private $groupmessage;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="usersent")
     */
    private $SentBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMsg(): ?string
    {
        return $this->msg;
    }

    public function setMsg(string $msg): self
    {
        $this->msg = $msg;

        return $this;
    }

    public function getMessagetimes(): ?string
    {
        return $this->messagetimes;
    }

    public function setMessagetimes(string $messagetimes): self
    {
        $this->messagetimes = $messagetimes;

        return $this;
    }

    public function getGroupmessage(): ?GroupT
    {
        return $this->groupmessage;
    }

    public function setGroupmessage(?GroupT $groupmessage): self
    {
        $this->groupmessage = $groupmessage;

        return $this;
    }

    public function getSentBy(): ?User
    {
        return $this->SentBy;
    }

    public function setSentBy(?User $SentBy): self
    {
        $this->SentBy = $SentBy;

        return $this;
    }
}
