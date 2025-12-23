<?php

namespace App\DTO\User\Output;

use Symfony\Component\Uid\Uuid;

readonly class UserCollectionAttributesOutputDTO
{ 
    public function __construct(
       /** @var UserAttributesOutputDTO[] */
        public array $users
    ) {}
    
}