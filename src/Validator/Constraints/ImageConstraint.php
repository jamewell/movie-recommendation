<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints\File;

class ImageConstraint extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'image/*',
                ],
                'mimeTypesMessage' => 'Please upload a valid image',
            ]),
        ];
    }
}
