<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\Entity\User;
use App\Service\UserService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Processor for creating new users
 */
class UserAttributesProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []): User
    {
        if (!$data instanceof UserRegistrationInputDTO) {
            throw new \InvalidArgumentException('Invalid input data: expected UserRegistrationInputDTO');
        }

        $tempUser = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($tempUser, $data->password);

        return $this->userService->createUser($data, $hashedPassword);
    }

}