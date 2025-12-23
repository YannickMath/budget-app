<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\RegistrationUser\Input\UserRegistrationInputDTO;
use App\Entity\User;
use App\Service\User\UserService as UserUserService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Processor for creating new users
 */
class UserCreateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserUserService $userService,
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

        return $this->userService->createNewUser($data);
    }

}