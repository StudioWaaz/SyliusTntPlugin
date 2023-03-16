@managing_shipping_gateway_tnt
Feature: Creating shipping gateway
    In order to export shipping data to external shipping provider service
    As an Administrator
    I want to be able to add new shipping gateway with shipping method

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator
        And the store has "TNT" shipping method with "$10.00" fee

    @ui
    Scenario: Creating TNT shipping gateway
        When I visit the create shipping gateway configuration page for "tnt"
        And I select the "TNT" shipping method
        And I fill the "login" field with "mylogin"
        And I fill the "password" field with "mypassord"
        And I fill the "account_number" field with "123"
        And I fill the "sender_name" field with "Waaz"
        And I fill the "sender_address1" field with "Allée d'Aguiléra"
        And I fill the "sender_address2" field with "Etage 2"
        And I fill the "sender_city" field with "Biarritz"
        And I fill the "sender_zip_code" field with "64200"
        And I fill the "sender_phone_number" field with "0900000000"
        And I fill the "sender_contact_first_name" field with "Ibes"
        And I fill the "sender_contact_last_name" field with "Mongabure"
        And I fill the "sender_email_address" field with "noreply@studiowaaz.com"
        And I fill the "sender_fax_number" field with "0900000001"
        And I fill the "sender_type" field with "ENTERPRISE"
        And I fill the "receiver_type" field with "INDIVIDUAL"
        And I fill the "label_format" field with "STDA4"
        And I add it
        Then I should be notified that the shipping gateway has been created