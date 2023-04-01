<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use TNTExpress\Model\Expedition;
use Sylius\Component\Core\Model\ShipmentInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;

interface ClientInterface
{
    public function setShippingGateway(ShippingGatewayInterface $shippingGateway): void;

    public function setShipment(ShipmentInterface $shipment): void;
    
    public function createExpedition(): Expedition;
}