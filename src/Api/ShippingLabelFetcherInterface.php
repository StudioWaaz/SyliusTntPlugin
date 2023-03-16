<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

interface ShippingLabelFetcherInterface
{
    public function createShipment($shippingGateway, $shipment): void;

    public function getLabelContent(): ?string;
}
