@managing_postcode_zipcode
Feature: Checking postcode and zipcode match
    In order to export shipping data to external shipping provider service
    As an Administrator
    I want to be able to export shipments to external API

    Background:
        Given the store operates on a single channel in "United States"
        And the store ships to "France"
        And the store has a zone "World" with code "WR"
        And this zone has the "France" country member
        And the store has a product "PHP T-Shirt" priced at "$19.99"
        And the store ships everywhere for free
        And I am a logged in customer

    @ui @javascript
    Scenario: City field is converted to select and show cities
        Given I added product "PHP T-Shirt" to the cart
        And I am at the checkout addressing step
        And I specify billing country as "France"
        And I specify billing postcode as "64200"
        Then I should have "Biarritz" city available to choose from
