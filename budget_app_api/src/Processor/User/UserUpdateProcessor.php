<?php

namespace App\Processor\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\User\Input\UserUpdateInputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\UserService as UserUserService;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Processor for updating existing users
 */
class UserUpdateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly UserUserService $userService,
        private UserRepository $userRepository
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []): User
    {
        if (!$data instanceof UserUpdateInputDTO) {
            throw new InvalidArgumentException('Invalid input data: expected UserUpdateInputDTO');
        }
        $id = $uriVariables['id'] ?? null;
        if ($id === null) {
            throw new BadRequestHttpException('User ID is required for update');
        }

        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw new BadRequestHttpException('User not found for update');
        }

        return $this->userService->updateUser($data, $user);
    }
}