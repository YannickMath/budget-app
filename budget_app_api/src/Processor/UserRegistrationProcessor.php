<?php

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\User\Input\UserRegistrationInput;
use App\Service\RegistrationService;

class UserRegistrationProcessor implements ProcessorInterface
{
    public function __construct(private RegistrationService $registrationService) {}
    
    public function process(
        mixed $data, 
        Operation $operation, 
        array $uriVariables = [], 
        array $context = []
    ): ?UserRegistrationInput
    {
        // Traitement de l'enregistrement de l'utilisateur
        if (!$data instanceof UserRegistrationInput) {
            return null;
        }
        // Ici, vous pouvez ajouter la logique pour enregistrer l'utilisateur,
        // return $this->registrationService->registerUser($data);
        return null;
        
    }
}