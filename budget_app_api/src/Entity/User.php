<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
// use ApiPlatform\Metadata\Link;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\Provider\User\UserCollectionAttributesProvider;
use App\Provider\User\UserAttributesProvider;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource()]
#[Get(
    // uriTemplate: '/users/{publicId}',
    //         uriVariables: [
        //     'publicId' => new Link(
    //         fromClass: User::class,
    //         identifiers: ['publicId']
    //     )
    // ],
    provider: UserAttributesProvider::class,
    output: UserAttributesOutputDTO::class,
    security: 'is_granted("ROLE_ADMIN")',
    securityMessage: 'Access denied.'
)]
#[GetCollection(
    provider: UserCollectionAttributesProvider::class,
    output: UserAttributesOutputDTO::class,
    security: 'is_granted("ROLE_ADMIN")',
    securityMessage: 'Access denied.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct()
    {
        $this->publicId = Uuid::v4();
    }
    
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid', unique: true)]
    #[Assert\Uuid]
    private ?Uuid $publicId = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email obligatoire')]
    #[Assert\Email(message: 'Email invalide')]
    private ?string $email = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Nom d\'utilisateur obligatoire')]
    #[Assert\Length(
        min: 3,
        minMessage: 'Le nom d\'utilisateur doit contenir au moins {{ limit }} caractères',
        max: 50,
        maxMessage: 'Le nom d\'utilisateur ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $username = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Mot de passe obligatoire')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères',
        max: 30,
        maxMessage: 'Le mot de passe ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $password = null;

    #[ORM\Column(length: 5)]
    #[Assert\Choice(choices: ['fr', 'en'], message: 'Langue obligatoire')]
    private ?string $locale = 'fr';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Fuseau horaire obligatoire')]
    #[Assert\Timezone]
    private ?string $timezone = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $avatarPath = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $email_verified_at = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $is_active = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_login_at = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\SoftDeleteable(fieldName: 'deleted_at')]
    private ?\DateTimeImmutable $deleted_at = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicId(): ?Uuid
    {
        return $this->publicId;
    }

    public function getDisplayName(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
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

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getAvatarPath(): ?string
    {
        return $this->avatarPath;
    }

    public function setAvatarPath(?string $avatarPath): static
    {
        $this->avatarPath = $avatarPath;

        return $this;
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->email_verified_at;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $email_verified_at): static
    {
        $this->email_verified_at = $email_verified_at;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->last_login_at;
    }

    public function setLastLoginAt(?\DateTimeImmutable $last_login_at): static
    {
        $this->last_login_at = $last_login_at;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(\DateTimeImmutable $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }
}