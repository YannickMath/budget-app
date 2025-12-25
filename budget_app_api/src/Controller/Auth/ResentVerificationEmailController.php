<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Auth\ForgotPasswordService;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;
#[AsController]
final class ResentVerificationEmailController extends AbstractController
{
    public function __construct(
        // private readonly ForgotPasswordService $forgotPasswordService
    ) {}

}