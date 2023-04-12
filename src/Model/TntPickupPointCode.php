<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Model;

use InvalidArgumentException;
use Symfony\Component\Intl\Countries;
use Webmozart\Assert\Assert;

final class TntPickupPointCode implements TntPickupPointCodeInterface
{
    private const DELIMITER = '---';

    private string $id;

    private string $provider;

    /**
     * Some providers will only have unique ids per country
     * hence we need the country to make it completely unique in these cases
     *
     * @var string
     */
    private $country;

    private ?string $zipCode = null;

    private ?string $city = null;

    /**
     * @param mixed $id
     */
    public function __construct($id, string $provider, string $country)
    {
        Assert::scalar($id);

        $country = mb_strtoupper($country);
        Assert::true(Countries::exists($country));

        $this->id = (string) $id;
        $this->provider = $provider;
        $this->country = $country;
    }

    public static function createFromString(string $string): self
    {
        $parts = explode(self::DELIMITER, $string);

        if (!isset($parts[0])) {
            throw new InvalidArgumentException('No provider part provided');
        }

        if (!isset($parts[1])) {
            throw new InvalidArgumentException('No id part provided');
        }

        if (!isset($parts[2])) {
            throw new InvalidArgumentException('No country part provided');
        }

        $code = new self($parts[1], $parts[0], $parts[2]);
        $code->setZipcode($parts[3]);
        $code->setCity($parts[4]);

        return $code;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        Assert::notNull($this->getZipCodePart(), 'Zip code is not set');
        Assert::notNull($this->getCityPart(), 'City is not set');

        return $this->provider . self::DELIMITER . $this->id . self::DELIMITER . $this->country . self::DELIMITER . $this->getZipCodePart() . self::DELIMITER . $this->getCityPart();
    }

    public function getIdPart(): string
    {
        return $this->id;
    }

    public function getProviderPart(): string
    {
        return $this->provider;
    }

    public function getCountryPart(): string
    {
        return $this->country;
    }

    public function getZipCodePart(): ?string
    {
        return $this->zipCode;
    }

    public function getCityPart(): ?string
    {
        return $this->city;
    }

    public function setZipcode(string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }
}
