<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use Waaz\SyliusTntPlugin\Api\Client;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ShippingLabelFetcher implements ShippingLabelFetcherInterface
{
    public function __construct(
        private FlashBagInterface $flashBag,
        private Client $client
    ) {
    }

    public function createShipment($shippingGateway, $shipment): void
    {
        try {
            $this->client->setShippingGateway($shippingGateway);
            $this->client->setShipment($shipment);
            $this->response = $this->client->createExpedition();
        } catch (\Exception $exception) {
            $this->flashBag->add(
                'error',
                sprintf(
                    'TNT Service for #%s order: %s',
                    $shipment->getOrder()->getNumber(),
                    $exception->getMessage()
                )
            );
            return;
        }
    }

    public function getLabelContent(): ?string
    {
        if (!isset($this->response)) {
            return '';
        }

        $this->flashBag->add('success', 'bitbag.ui.shipment_data_has_been_exported'); // Add success notification

        return $this->response->getPDFLabels();
    }
}
