<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[\Attribute]
class TntCityPostcodeMatch extends Constraint
{
    public string $cityAndPostcodeDoesNotMatchMessage = 'waaz.tnt_plugin.city_postcode_match.messages.invalid';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}