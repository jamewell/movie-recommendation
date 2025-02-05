<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PasswordRequirements extends Compound
{

    /**
     * @inheritDoc
     */
    protected function getConstraints(array $options): array
    {
        return [
            new NotBlank(),
            new Length([
                'min' => 8,
                'minMessage' => 'Your password should be at least {{ limit }} characters',
                // max length allowed by Symfony for security reasons
                'max' => 4096,
            ]),
            new Regex([
                'pattern' => '/\d+/i',
                'message' => 'Your password must contain at least 1 digit.',
            ]),
            new Regex([
                'pattern' => '/[A-Z]/',
                'message' => 'Your password must contain at least 1 capital letter.',
            ]),
            new Regex([
                'pattern' => '/[a-z]/',
                'message' => 'Your password must contain at least 1 lowercase letter.',
            ]),
            new Regex([
                'pattern' => '/\W/',
                'message' => 'Your password must contain at least 1 symbol.',
            ]),
        ];
    }
}