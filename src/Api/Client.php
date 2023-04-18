<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use TNTExpress\Client\TNTClient;
use TNTExpress\Exception\InvalidPairZipcodeCityException;
use TNTExpress\Model\Address;
use TNTExpress\Model\Expedition;
use TNTExpress\Model\ExpeditionRequest;
use TNTExpress\Model\Service;
use Waaz\SyliusTntPlugin\Factory\ExpeditionRequestFactoryInterface;
use Waaz\SyliusTntPlugin\Factory\ParcelRequestFactoryInterface;
use Waaz\SyliusTntPlugin\Factory\ReceiverFactoryInterface;
use Waaz\SyliusTntPlugin\Factory\SenderFactoryInterface;
use Webmozart\Assert\Assert;

class Client implements ClientInterface
{
    private ?ShippingGatewayInterface $shippingGateway = null;

    private ?ShipmentInterface $shipment = null;

    public function __construct(
        private TNTClient $tntClient,
        private string $weightUnit,
        private SenderFactoryInterface $senderFactory,
        private ReceiverFactoryInterface $receiverFactory,
        private ParcelRequestFactoryInterface $parcelRequestFactory,
        private ExpeditionRequestFactoryInterface $expeditionRequestFactory,
    ) {
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
        $shippingGateway = $this->shippingGateway;
        Assert::notNull($shippingGateway, '$shippingGateway must be set before expedition creation.');
        $sender = $this->senderFactory->createNew($shippingGateway);

        $shipment = $this->shipment;
        Assert::notNull($shipment, '$shipment must be set before expedition creation.');
        $receiver = $this->receiverFactory->createNew($shippingGateway, $shipment);
        $this->verifyAddresses([$receiver]);

        $parcelRequest = $this->parcelRequestFactory->createNew($shipment, $this->weightUnit);

        $expeditionRequest = $this->expeditionRequestFactory->createNew($sender, $receiver, $parcelRequest, $shippingGateway);

        $feasibility = $this->getFeasibility($expeditionRequest);
        $expeditionRequest->setServiceCode($feasibility->getServiceCode());

        return $this->tntClient->createExpedition($expeditionRequest);
    }

    private function verifyAddresses(array $addresses): void
    {
        foreach ($addresses as $address) {
            Assert::isInstanceOf($address, Address::class);
            if (false === $this->tntClient->checkZipcodeCityMatch($address->getZipCode(), $address->getCity())) {
                throw new InvalidPairZipcodeCityException($address->getZipCode(), $address->getCity());
            }
        }
    }

    /** Must be improved **/
    private function getFeasibility(ExpeditionRequest $expeditionRequest): Service
    {
        /** @var array $feasibility */
        $feasibility = [];

        try {
            $feasibility = $this->tntClient->getFeasibility($expeditionRequest);
        } catch (\SoapFault $e) {
        }

        /** @var Service $service */
        $service = $feasibility[0];

        return $service;
    }
}
