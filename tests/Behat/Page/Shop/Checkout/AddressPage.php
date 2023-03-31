<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Waaz\SyliusTntExportPlugin\Behat\Page\Shop\Checkout;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Shop\Checkout\AddressPage as BaseAddressPage;

class AddressPage extends BaseAddressPage {

    public function selectBillingCountry(string $country): void
    {
        $this->getElement('billing_country')->selectOption($country);
    }
    
    public function selectBillingPostcode(string $postcode): void
    {
        $this->getElement('billing_postcode')->setValue($postcode);
    }
    
    public function getAvailableBillingCities(): array
    {
        $this->waitForOption(3, 'billing_city_select');
        return $this->getOptionsFromSelect($this->getElement('billing_city_select'));
    }

    /**
     * @return string[]
     */
    private function getOptionsFromSelect(NodeElement $element): array
    {
        return array_map(
            /** @return string[] */
            static fn (NodeElement $element): string => $element->getText(),
            $element->findAll('css', 'option[value!=""]'),
        );
    }
    
    private function waitForElement(int $timeout, string $elementName): bool
    {
        return $this->getDocument()->waitFor($timeout, fn () => $this->hasElement($elementName));
    }

    private function waitForOption(int $timeout, string $elementName): void
    {
        $parentElement = $this->getElement($elementName);
        $this->getDocument()->waitFor($timeout, fn () => count($parentElement->findAll('css', 'option')) > 0);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_country' => '[data-test-billing-country]',
            'billing_postcode' => '[data-test-billing-postcode]',
            'billing_city' => '[data-test-billing-city]',
            'billing_city_select' => '#city-select-0',
            // 'billing_address_book' => '[data-test-billing-address] [data-test-address-book]',
            // 'billing_country_province' => '[data-test-billing-address] [data-test-province-code]',
            // 'billing_first_name' => '[data-test-billing-first-name]',
            // 'billing_last_name' => '[data-test-billing-last-name]',
            // 'billing_province' => '[data-test-billing-address] [data-test-province-name]',
            // 'billing_street' => '[data-test-billing-street]',
            // 'checkout_subtotal' => '[data-test-checkout-subtotal]',
            // 'customer_email' => '[data-test-login-email]',
            // 'different_billing_address' => '[data-test-different-billing-address]',
            // 'different_billing_address_label' => '[data-test-different-billing-address-label]',
            // 'different_shipping_address' => '[data-test-different-shipping-address]',
            // 'different_shipping_address_label' => '[data-test-different-shipping-address-label]',
            // 'login_button' => '[data-test-login-button]',
            // 'login_password' => '[data-test-password-input]',
            // 'login_validation_error' => '[data-test-login-validation-error]',
            // 'next_step' => '[data-test-next-step]',
            // 'shipping_address' => '[data-test-shipping-address]',
            // 'shipping_address_book' => '[data-test-shipping-address] [data-test-address-book]',
            // 'shipping_city' => '[data-test-shipping-city]',
            // 'shipping_country' => '[data-test-shipping-country]',
            // 'shipping_country_province' => '[data-test-shipping-address] [data-test-province-code]',
            // 'shipping_first_name' => '[data-test-shipping-first-name]',
            // 'shipping_last_name' => '[data-test-shipping-last-name]',
            // 'shipping_postcode' => '[data-test-shipping-postcode]',
            // 'shipping_province' => '[data-test-shipping-address] [data-test-province-name]',
            // 'shipping_street' => '[data-test-shipping-street]',
        ]);
    }
}