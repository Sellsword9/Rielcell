<?php

namespace App\Entity;

use App\Repository\RepoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepoRepository::class)]
class Repo
{

    public const PRIVACY_PUBLIC = 'public';
    public const PRIVACY_PRIVATE = 'private';
    public const VCS_GIT = 'git';
    public const VCS_RIEL = 'riel';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $vcs = null;

    #[ORM\ManyToOne(inversedBy: 'repos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverpath = null;

    #[ORM\Column]
    private ?bool $hasDirectorymade = null;

    #[ORM\Column]
    private ?bool $deleted = null;

    #[ORM\Column(name: 'is_private', type: 'boolean')]
    private ?bool $isPrivate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVcs(): ?string
    {
        return $this->vcs;
    }

    public function setVcs(string $vcs): static
    {
        $this->vcs = $vcs;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getServerpath(): ?string
    {
        return $this->serverpath;
    }

    public function setServerpath(?string $serverpath): static
    {
        $this->serverpath = $serverpath;

        return $this;
    }

    public function hasDirectorymade(): ?bool
    {
        return $this->hasDirectorymade;
    }

    public function setHasDirectorymade(bool $hasDirectorymade): static
    {
        $this->hasDirectorymade = $hasDirectorymade;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): static
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }
}
