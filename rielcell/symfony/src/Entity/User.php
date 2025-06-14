<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Repo>
     */
    #[ORM\OneToMany(targetEntity: Repo::class, mappedBy: 'owner')]
    private Collection $repos;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    #[ORM\Column(length: 10)]
    private ?string $deleted = null;

    public function __construct()
    {
        $this->repos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Repo>
     */
    public function getRepos(): Collection
    {
        return $this->repos;
    }

    public function addRepo(Repo $repo): static
    {
        if (!$this->repos->contains($repo)) {
            $this->repos->add($repo);
            $repo->setOwner($this);
        }

        return $this;
    }

    public function removeRepo(Repo $repo): static
    {
        if ($this->repos->removeElement($repo)) {
            // set the owning side to null (unless already changed)
            if ($repo->getOwner() === $this) {
                $repo->setOwner(null);
            }
        }

        return $this;
    }

    public function getNamesBlacklist(): ?array
    {
        $repos = $this->getRepos();
        $blacklist = [];
        foreach ($repos as $repo) {
            // Slow to process but safer
            /* if ($repo->getName() != null) {
                $blacklist[] = $repo->getName();
            } */
            // Fast to process and safe enough
            $blacklist[] = strtolower($repo->getName());
        }

        // Dot protection is redundant but knowing how much it could brick a system better not to risk it 
        $fsreferences = ['.', '..'];
        $blacklist = array_merge($blacklist, $fsreferences);

        $windowsReserved = ['con', 'prn', 'aux', 'nul'];
        foreach (range(1, 9) as $i) {
            $windowsReserved[] = "com{$i}";
            $windowsReserved[] = "lpt{$i}";
        }
        $blacklist = array_merge($blacklist, $windowsReserved);
        return $blacklist;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getDeleted(): ?string
    {
        return $this->deleted;
    }

    public function setDeleted(string $deleted): static
    {
        $this->deleted = $deleted;

        return $this;
    }
}
