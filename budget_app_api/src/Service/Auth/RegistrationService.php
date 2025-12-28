<?php

namespace App\Service\Auth;

use App\DTO\Auth\Input\RegisterInputDTO;
use App\Entity\User;
use App\Event\RegisterSuccessEvent;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Service to manage user registration operations
 */
class RegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private EventDispatcherInterface $dispatcher
    ) {}

    /**
     * Register a new user with the provided input data
     */
    public function registerNewUser(RegisterInputDTO $input): User
    {
        $user = new User();
        $user->setEmail($input->email);
        $user->setUsername($input->username);
        $hashedPassword = $this->userService->hashPassword($user, $input->password);
        $user->setPassword($hashedPassword);
        $user->setRoles($input->roles);
        $user->setTimezone($input->timezone);
        $user->setLocale($input->locale);

        try {
            $this->userRepository->save($user, true);
            $event = new RegisterSuccessEvent($user);
            $this->dispatcher->dispatch($event);
        } catch (UniqueConstraintViolationException $e) {
            throw new UnprocessableEntityHttpException('Cet email ou nom d\'utilisateur est déjà utilisé');
        } catch (Exception $e) {
            throw new RuntimeException('Erreur lors de l\'enregistrement de l\'utilisateur: ' . $e->getMessage());
        }

        return $user;
    }
}