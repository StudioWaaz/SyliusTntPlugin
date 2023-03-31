<?php

declare(strict_types=1);

namespace spec\Waaz\SyliusTntExportPlugin\Api;

use PhpSpec\ObjectBehavior;
use TNTExpress\Model\Service;
use TNTExpress\Client\TNTClient;
use TNTExpress\Model\Expedition;
use Waaz\SyliusTntExportPlugin\Api\Client;
use TNTExpress\Model\ExpeditionRequest;
use TNTExpress\Client\TNTClientInterface;
use Waaz\SyliusTntExportPlugin\Api\ClientInterface;
use Webmozart\Assert\InvalidArgumentException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use TNTExpress\Exception\InvalidPairZipcodeCityException;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGateway;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;

class ClientSpec extends ObjectBehavior
{
    function let(TNTClient $client): void
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Client::class);
        $this->shouldHaveType(ClientInterface::class);
    }

    function it_creates_request_data_shipment(
        ShippingGatewayInterface $shippingGateway,
        ShipmentInterface $shipment,
        AddressInterface $address,
        OrderInterface $order,
        CustomerInterface $customer,
        Service $service,
        TNTClient $client,
        ExpeditionRequest $expeditionRequest
    ): void {
        $shippingGateway->getConfigValue('sender_name')->willReturn('Caissin');
        $shippingGateway->getConfigValue('sender_address1')->willReturn('73 Boulevard de Grenelle');
        $shippingGateway->getConfigValue('sender_address2')->willReturn(null);
        $shippingGateway->getConfigValue('sender_city')->willReturn('Paris');
        $shippingGateway->getConfigValue('sender_zip_code')->willReturn('75015');
        $shippingGateway->getConfigValue('sender_phone_number')->willReturn(null);
        $shippingGateway->getConfigValue('sender_contact_first_name')->willReturn(null);
        $shippingGateway->getConfigValue('sender_contact_last_name')->willReturn(null);
        $shippingGateway->getConfigValue('sender_email_address')->willReturn(null);
        $shippingGateway->getConfigValue('sender_fax_number')->willReturn(null);
        $shippingGateway->getConfigValue('sender_type')->willReturn('ENTERPRISE');
        $shippingGateway->getConfigValue('receiver_type')->willReturn('INDIVIDUAL');
        $shippingGateway->getConfigValue('account_number')->willReturn('06324676');
        
        $address->getLastName()->willReturn('Mongabure');
        $address->getFirstName()->willReturn('Ibes');
        $address->getStreet()->willReturn('9 rue Port du Temple');
        $address->getPhoneNumber()->willReturn('0500000000');
        $address->getCompany()->willReturn(null);
        $address->getCity()->willReturn('Biarritz');
        $address->getPostcode()->willReturn('64200');

        $customer->getEmail()->willReturn('alex@durand.fr');
        
        $order->getShippingAddress()->willReturn($address);
        $order->getCustomer()->willReturn($customer);
        $shipment->getOrder()->willReturn($order);
        $shipment->getShippingWeight()->willReturn(2000);

        $service->getServiceCode()->willReturn('test');

        $client->getFeasibility($expeditionRequest)->willReturn([$service]);

        $this->setShippingGateway($shippingGateway);
        $this->setShipment($shipment);

        //$this->createExpedition();

    }

    function it_should_not_allow_create_expedition_without_shipping_gateway(): void
    {
        $this->shouldThrow(new InvalidArgumentException('$shippingGateway must be set before expedition creation.'))->during('createExpedition');
    }

    function it_should_not_allow_create_expedition_without_shipment(ShippingGatewayInterface $shippingGateway): void
    {
        $this->setShippingGateway($shippingGateway);
        $this->shouldThrow(new InvalidArgumentException('$shipment must be set before expedition creation.'))->during('createExpedition');
    }

    // function it_should_not_allow_create_expedition_if_zip_code_not_corresponding_with_city(
    //     ShippingGatewayInterface $shippingGateway,
    //     ShipmentInterface $shipment,
    //     AddressInterface $address,
    //     OrderInterface $order,
    //     CustomerInterface $customer
    // ): void {
    //     $shippingGateway->getConfigValue('sender_name')->willReturn('Caissin');
    //     $shippingGateway->getConfigValue('sender_address1')->willReturn('73 Boulevard de Grenelle');
    //     $shippingGateway->getConfigValue('sender_address2')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_city')->willReturn('Paris');
    //     $shippingGateway->getConfigValue('sender_zip_code')->willReturn('75015');
    //     $shippingGateway->getConfigValue('sender_phone_number')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_contact_first_name')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_contact_last_name')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_email_address')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_fax_number')->willReturn(null);
    //     $shippingGateway->getConfigValue('sender_type')->willReturn('ENTERPRISE');
    //     $shippingGateway->getConfigValue('receiver_type')->willReturn('INDIVIDUAL');
        
    //     $address->getLastName()->willReturn('Mongabure');
    //     $address->getFirstName()->willReturn('Ibes');
    //     $address->getStreet()->willReturn('9 rue Port du Temple');
    //     $address->getPhoneNumber()->willReturn('0500000000');
    //     $address->getCompany()->willReturn(null);
    //     $address->getCity()->willReturn('Biarritz');
    //     $address->getPostcode()->willReturn('64500');

    //     $customer->getEmail()->willReturn('alex@durand.fr');
        
    //     $order->getShippingAddress()->willReturn($address);
    //     $order->getCustomer()->willReturn($customer);
    //     $shipment->getOrder()->willReturn($order);
    //     $shipment->getShippingWeight()->willReturn(2000);

    //     $this->setShippingGateway($shippingGateway);
    //     $this->setShipment($shipment);

    //     $this->shouldThrow(new InvalidPairZipcodeCityException('64500', 'Biarritz'))->during('createExpedition');
    // }
}
