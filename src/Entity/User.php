<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[Groups(['public'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['public'])]
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "Email не может быть пустым")]
    #[Assert\Email(message: "Email '{{ value }}' не является валидным адресом")]
    private ?string $email = null;

    #[Groups(['public'])]
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['public'])]
    private ?string $avatar = null;

    #[ORM\Column]
    private \DateTime $createdAt;

    #[ORM\Column]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Гарантируем, что у всех есть ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

//    public string $agreeTerms;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

//    public function getRoles(): array
//    {
//        return ['ROLE_USER'];
//    }

    public function eraseCredentials(): void
    {
        // Если есть временные данные (plainPassword), очистить их
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
