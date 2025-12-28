<?php

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\DTO\Admin\Input\AdminCreateUserInputDTO;
use App\DTO\Admin\Input\AdminUpdateUserInputDTO;
use App\DTO\User\Output\UserAttributesOutputDTO;
use App\Processor\User\UserCreateProcessor;
use App\Processor\User\UserUpdateProcessor;
use App\Processor\User\UserDeleteProcessor;
use App\Provider\User\UserCollectionAttributesProvider;
use App\Provider\User\UserAttributesProvider;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`users`')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
// #[ApiResource()]
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
#[Post(
    denormalizationContext: [
        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false
    ],
    processor: UserCreateProcessor::class,
    input: AdminCreateUserInputDTO::class,
    output: UserAttributesOutputDTO::class,
    security: 'is_granted("ROLE_ADMIN")',
    securityMessage: 'Access denied.'
)]
#[Put(
    denormalizationContext: [
        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false
    ],
    processor: UserUpdateProcessor::class,
    input: AdminUpdateUserInputDTO::class,
    output: UserAttributesOutputDTO::class,
    security: 'is_granted("ROLE_ADMIN")',
    securityMessage: 'Access denied.'
)]
#[Delete(
    processor: UserDeleteProcessor::class,
    security: 'is_granted("ROLE_ADMIN")',
    securityMessage: 'Access denied.'
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function __construct()
    {
        $this->publicId = Uuid::v4();
        $this->emailChangeRequests = new ArrayCollection();
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
    private ?\DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $emailVerificationTokenExpiresAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $passwordResetToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordResetTokenExpiresAt = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $deletionReason = null;

    /**
     * @var Collection<int, EmailChangeRequest>
     */
    #[ORM\OneToMany(targetEntity: EmailChangeRequest::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $emailChangeRequests;


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
        ## If you store any temporary, sensitive data on the user, clear it here
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
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $emailVerifiedAt): static
    {
        $this->emailVerifiedAt = $emailVerifiedAt;

        return $this;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->emailVerificationToken;
    }

    public function setEmailVerificationToken(?string $emailVerificationToken): static
    {
        $this->emailVerificationToken = $emailVerificationToken;

        return $this;
    }

    public function getEmailVerificationTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->emailVerificationTokenExpiresAt;
    }

    public function setEmailVerificationTokenExpiresAt(?\DateTimeImmutable $emailVerificationTokenExpiresAt): static
    {
        $this->emailVerificationTokenExpiresAt = $emailVerificationTokenExpiresAt;

        return $this;
    }

    public function isEmailVerificationTokenValid(): bool
    {
        if ($this->emailVerificationToken === null || $this->emailVerificationTokenExpiresAt === null) {
            return false;
        }

        return $this->emailVerificationTokenExpiresAt > new \DateTimeImmutable();
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->passwordResetToken;
    }

    public function setPasswordResetToken(?string $passwordResetToken): static
    {
        $this->passwordResetToken = $passwordResetToken;

        return $this;
    }

    public function getPasswordResetTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->passwordResetTokenExpiresAt;
    }

    public function setPasswordResetTokenExpiresAt(?\DateTimeImmutable $passwordResetTokenExpiresAt): static
    {
        $this->passwordResetTokenExpiresAt = $passwordResetTokenExpiresAt;

        return $this;
    }

    public function isPasswordResetTokenValid(): bool
    {
        if ($this->passwordResetToken === null || $this->passwordResetTokenExpiresAt === null) {
            return false;
        }

        return $this->passwordResetTokenExpiresAt > new \DateTimeImmutable();
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletionReason(): ?string
    {
        return $this->deletionReason;
    }

    public function setDeletionReason(?string $deletionReason): static
    {
        $this->deletionReason = $deletionReason;

        return $this;
    }

    /**
     * @return Collection<int, EmailChangeRequest>
     */
    public function getEmailChangeRequests(): Collection
    {
        return $this->emailChangeRequests;
    }

    public function addEmailChangeRequest(EmailChangeRequest $emailChangeRequest): static
    {
        if (!$this->emailChangeRequests->contains($emailChangeRequest)) {
            $this->emailChangeRequests->add($emailChangeRequest);
            $emailChangeRequest->setUser($this);
        }

        return $this;
    }

    public function removeEmailChangeRequest(EmailChangeRequest $emailChangeRequest): static
    {
        if ($this->emailChangeRequests->removeElement($emailChangeRequest)) {
            if ($emailChangeRequest->getUser() === $this) {
                $emailChangeRequest->setUser(null);
            }
        }

        return $this;
    }
}