<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

use Webmozart\Assert\Assert;
use TNTExpress\Model\Expedition;
use Waaz\SyliusTntPlugin\Api\Client;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;

class ShippingLabelFetcher implements ShippingLabelFetcherInterface
{
    private ?Expedition $response = null;

    public function __construct(
        private FlashBagInterface $flashBag,
        private Client $client
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
            
            /** @var string */
            $number = $order->getNumber();


            $this->flashBag->add(
                'error',
                sprintf(
                    'TNT Service for #%s order: %s',
                    $number,
                    $exception->getMessage()
                )
            );
        }
    }

    public function getLabelContent(): ?string
    {
        $this->flashBag->add('success', 'bitbag.ui.shipment_data_has_been_exported'); // Add success notification
        Assert::isInstanceOf($this->response, Expedition::class);

        return $this->response->getPDFLabels();
    }
}
