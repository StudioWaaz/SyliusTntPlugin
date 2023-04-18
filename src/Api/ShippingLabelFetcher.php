<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use TNTExpress\Model\Expedition;
use Webmozart\Assert\Assert;

class ShippingLabelFetcher implements ShippingLabelFetcherInterface
{
    private ?Expedition $response = null;

    public function __construct(
        private FlashBagInterface $flashBag,
        private Client $client,
    ) {
    }

    public function createShipment(ShippingGatewayInterface $shippingGateway, ShipmentInterface $shipment): void
    {
        try {
            $this->client->setShippingGateway($shippingGateway);
            $this->client->setShipment($shipment);
            $this->response = $this->client->createExpedition();
        } catch (\Exception $exception) {
            $order = $shipment->getOrder();
            Assert::isInstanceOf($order, OrderInterface::class);

            /** @var string $number */
            $number = $order->getNumber();

            $this->flashBag->add(
                'error',
                sprintf(
                    'TNT Service for #%s order: %s',
                    $number,
                    $exception->getMessage(),
                ),
            );
        }
    }

    public function getLabelContent(): ?string
    {
        $this->flashBag->add('success', 'bitbag.ui.shipment_data_has_been_exported');
        $response = $this->response;
        Assert::isInstanceOf($response, Expedition::class);

        return $response->getPDFLabels();
    }
}
