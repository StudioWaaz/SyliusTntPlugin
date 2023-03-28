<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
*/

declare(strict_types=1);

namespace Tests\Waaz\SyliusTntPlugin\Behat\Context\Ui\Shop;

use Webmozart\Assert\Assert;
use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Shop\Checkout\AddressPageInterface;

final class ShippingAddressContext implements Context
{
    public function __construct(
        private AddressPageInterface $addressPage,
    ) {
    }

    /**
     * @When I specify billing country as :countryName
     */
    public function iSpecifyBillingCountryAs(string $countryName): void
    {
        $this->addressPage->selectBillingCountry($countryName);
    }

    /**
     * @When I specify billing postcode as :postcode
     */
    public function iSpecifyBillingPostcodeAs(string $postcode): void
    {
        $this->addressPage->selectBillingPostcode($postcode);
    }

    /**
     * @Then I should have :cityName city available to choose from
     */
    public function shouldHaveCountriesToChooseFrom(string $cityName): void
    {
        $availableBillingCities = $this->addressPage->getAvailableBillingCities();
        Assert::inArray($cityName, $availableBillingCities, "$cityName is not in cities select.");
    }
}