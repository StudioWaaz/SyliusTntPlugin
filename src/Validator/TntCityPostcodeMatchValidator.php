<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Validator;

use Sylius\Component\Core\Model\Address;
use TNTExpress\Client\TNTClientInterface;
use TNTExpress\Exception\ClientException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Sylius\Component\Addressing\Model\AddressInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class TntCityPostcodeMatchValidator extends ConstraintValidator
{
    public function __construct(
        private TNTClientInterface $tntClient
    ) {}
    
    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AddressInterface) {
            throw new UnexpectedValueException($value, AddressInterface::class);
        }

        if (!$constraint instanceof TntCityPostcodeMatch) {
            throw new UnexpectedValueException($constraint, TntCityPostcodeMatch::class);
        }

        if ($value->getCountryCode() != 'fr') {
            return;
        }

        $postcode = $value->getPostcode();
        $city = $value->getCity();


        if (false === is_null($postcode) && false === is_null($city)) {
            try {
                $result = $this->tntClient->getCitiesGuide($postcode);
                foreach ($result as $tntCity) {
                    $cityName = ucwords(strtolower($tntCity->getName() ?? ''));
                    if ($cityName === $city) {
                        return;
                    }
                }
            } catch (ClientException $e) {
            }
        }
        
        $this->context
            ->buildViolation($constraint->cityAndPostcodeDoesNotMatchMessage)
            ->atPath('city')
            ->addViolation();
    }
}