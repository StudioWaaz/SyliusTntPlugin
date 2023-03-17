<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use TNTExpress\Model\Sender;
use Webmozart\Assert\Assert;
use TNTExpress\Model\Address;
use TNTExpress\Model\Service;
use TNTExpress\Model\Receiver;
use TNTExpress\Client\TNTClient;
use TNTExpress\Model\Expedition;
use TNTExpress\Model\ParcelRequest;
use TNTExpress\Model\ExpeditionRequest;
use TNTExpress\Client\SoapClientBuilder;
use TNTExpress\Exception\ExceptionManager;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use TNTExpress\Exception\ExceptionManagerInterface;
use TNTExpress\Exception\InvalidPairZipcodeCityException;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;

class Client implements ClientInterface
{
    private ?ShippingGatewayInterface $shippingGateway = null;

    private ?ShipmentInterface $shipment = null;

    private ExceptionManagerInterface $exceptionManager;

    public function __construct()
    {
        $this->exceptionManager = new ExceptionManager();
    }

    public function setShippingGateway(ShippingGatewayInterface $shippingGateway): void
    {
        $this->shippingGateway = $shippingGateway;
    }

    public function setShipment(ShipmentInterface $shipment): void
    {
        $this->shipment = $shipment;
    }

    public function createExpedition(): Expedition
    {
        $tntClient = $this->init();

        $sender = $this->createSender();
        $receiver = $this->createReceiver();

        $this->verifyAddresses([$receiver], $tntClient);
        $parcelRequest = $this->createParcelRequest();

        $expeditionRequest = $this->createExpeditionRequest($sender, $receiver, $parcelRequest);
        $feasibility = $this->getFeasibility($expeditionRequest, $tntClient);

        $expeditionRequest->setServiceCode($feasibility->getServiceCode());
        
        return $tntClient->createExpedition($expeditionRequest);
    }

    private function init(): TNTClient
    {
        Assert::isInstanceOf($this->shippingGateway, ShippingGatewayInterface::class, '$shippingGateway must be set before initialization.');
        Assert::isInstanceOf($this->shipment, ShipmentInterface::class, '$shipment must be set before initialization.');

        /** @var string */
        $login = $this->shippingGateway->getConfigValue('login');
        /** @var string */
        $password = $this->shippingGateway->getConfigValue('password');

        $builder = new SoapClientBuilder($login, $password);
        $soapClient = $builder->createClient(true);

        return new TNTClient($soapClient, $this->exceptionManager);
    }

    private function createSender(): Sender
    {
        Assert::isInstanceOf($this->shippingGateway, ShippingGatewayInterface::class, '$shippingGateway must be set before initialization.');

        $sender = new Sender();
        $sender->setName($this->shippingGateway->getConfigValue('sender_name'))
            ->setAddress1($this->shippingGateway->getConfigValue('sender_address1'))
            ->setAddress2($this->shippingGateway->getConfigValue('sender_address2'))
            ->setCity($this->shippingGateway->getConfigValue('sender_city'))
            ->setZipCode($this->shippingGateway->getConfigValue('sender_zip_code'))
            ->setPhoneNumber($this->shippingGateway->getConfigValue('sender_phone_number'))
            ->setContactFirstName($this->shippingGateway->getConfigValue('sender_contact_first_name'))
            ->setContactLastName($this->shippingGateway->getConfigValue('sender_contact_last_name'))
            ->setEmailAddress($this->shippingGateway->getConfigValue('sender_email_address'))
            ->setFaxNumber($this->shippingGateway->getConfigValue('sender_fax_number'))
            ->setType($this->shippingGateway->getConfigValue('sender_type'))
        ;
        
        return $sender;
    }

    private function createReceiver(): Receiver
    {
        $receiver = new Receiver();

        Assert::isInstanceOf($this->shipment, ShipmentInterface::class, '$shipment must be set before initialization.');
        Assert::isInstanceOf($this->shippingGateway, ShippingGatewayInterface::class, '$shippingGateway must be set before initialization.');

        /** @var OrderInterface */
        $order = $this->shipment->getOrder();

        /** @var AddressInterface */
        $address = $order->getShippingAddress();

        /** @var CustomerInterface */
        $customer = $order->getCustomer();
        
        $receiver->setContactFirstName($address->getFirstName())
            ->setContactLastName($address->getLastName())
            ->setPhoneNumber($address->getPhoneNumber())
            ->setName($address->getCompany())
            ->setAddress1($address->getStreet())
            ->setCity($address->getCity())
            ->setZipCode($address->getPostcode())
            ->setType($this->shippingGateway->getConfigValue('receiver_type'))
            ->setEmailAddress($customer->getEmail())
        ;

        return $receiver;
    }

    private function createParcelRequest(): ParcelRequest
    {
        $parcelRequest = new ParcelRequest;
        $parcelRequest->setSequenceNumber(1);
        
        Assert::isInstanceOf($this->shipment, ShipmentInterface::class, '$shipment must be set before initialization.');

        $weight = $this->shipment->getShippingWeight()/1000;
        $parcelRequest->setWeight(sprintf("%.3f", $weight));

        return $parcelRequest;
    }

    private function verifyAddresses(array $addresses, TNTClient $tntClient): void
    {
        foreach ($addresses as $address) {
            Assert::isInstanceOf($address, Address::class);
            if (false === $tntClient->checkZipcodeCityMatch($address->getZipCode(), $address->getCity()))
            {
                throw new InvalidPairZipcodeCityException($address->getZipCode(), $address->getCity());
            }
        }
    }

    private function createExpeditionRequest(Sender $sender, Receiver $receiver, ParcelRequest $parcelRequest): ExpeditionRequest
    {
        Assert::isInstanceOf($this->shippingGateway, ShippingGatewayInterface::class, '$shippingGateway must be set before initialization.');

        $expeditionRequest = new ExpeditionRequest();
        $expeditionRequest->setShippingDate(new \Datetime());
        $expeditionRequest->setAccountNumber($this->shippingGateway->getConfigValue('account_number'));
        $expeditionRequest->setSender($sender);
        $expeditionRequest->setReceiver($receiver);
        $expeditionRequest->setParcelsRequest([$parcelRequest]);

        return $expeditionRequest;
        
    }

    /** Must be improved **/
    private function getFeasibility(ExpeditionRequest $expeditionRequest, TNTClient $tntClient): Service
    {
        /** @var array $feasibility */
        $feasibility = [];

        try {
            $feasibility = $tntClient->getFeasibility($expeditionRequest);
        } catch (\SoapFault $e) {
        }

        /** @var Service $service */
        $service = $feasibility[0];

        return $service;
    }
}