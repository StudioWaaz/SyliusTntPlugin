<?php

declare(strict_types=1);

namespace Waaz\SyliusTntExportPlugin\Api;

use Sylius\Component\Core\Model\ShipmentInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;

interface ShippingLabelFetcherInterface
{
    public function createShipment(ShippingGatewayInterface $shippingGateway, ShipmentInterface $shipment): void;

    public function getLabelContent(): ?string;
}
