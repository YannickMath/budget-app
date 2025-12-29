<?php

namespace App\DTO\Admin\Input;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data Transfer Object for admin role assignment
 */
readonly class AssignRolesInputDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Roles array must not be blank.')]
        #[Assert\Type(type: 'array', message: 'Roles must be provided as an array.')]
        #[Assert\All([
            new Assert\Type(type: 'string', message: 'Each role must be a string.'),
            new Assert\Regex(
                pattern: UserRole::VALIDATION_PATTERN,
                message: 'Each role must start with ROLE_ and contain only uppercase letters and underscores.'
            )
        ])]
        #[Assert\Count(
            min: 1,
            minMessage: 'At least one role must be provided.'
        )]
        public array $roles,
    ) {}
}
