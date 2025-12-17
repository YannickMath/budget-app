<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ProfileController extends AbstractController

{
    public function __construct(
        // private UserRepository $userRepository,
    ) {} 
    #[Route('/api/profile', name: 'app_user_profile', methods: ['GET'])]
    
    public function profile()
    {
       echo 'User profile endpoint';
    }
}