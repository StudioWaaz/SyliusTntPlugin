<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\EventListener;

use Webmozart\Assert\Assert;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Sylius\Component\Core\Model\ShipmentInterface;
use Waaz\SyliusTntPlugin\Api\ShippingLabelFetcherInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingExportInterface;
use BitBag\SyliusShippingExportPlugin\Repository\ShippingExportRepository;

class ShippingExportEventListener
{
    public const TNT_GATEWAY_CODE = 'tnt';

    public function __construct(
        private Filesystem $filesystem,
        private ShippingExportRepository $shippingExportRepository,
        private string $shippingLabelsPath,
        private ShippingLabelFetcherInterface $shippingLabelFetcher
    ) {
    }

    public function exportShipment(ResourceControllerEvent $event): void
    {
        $shippingExport = $event->getSubject();
        Assert::isInstanceOf($shippingExport, ShippingExportInterface::class);

        $shippingGateway = $shippingExport->getShippingGateway();
        Assert::notNull($shippingGateway);

        if (self::TNT_GATEWAY_CODE !== $shippingGateway->getCode()) {
            return;
        }

        $shipment = $shippingExport->getShipment();
        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        $this->shippingLabelFetcher->createShipment($shippingGateway, $shipment);

        $labelContent = $this->shippingLabelFetcher->getLabelContent();
        Assert::stringNotEmpty($labelContent);

        $this->saveShippingLabel($shippingExport, $labelContent, 'pdf'); // Save label
        $this->markShipmentAsExported($shippingExport); // Mark shipment as "Exported"
    }

    public function saveShippingLabel(
        ShippingExportInterface $shippingExport,
        string $labelContent,
        string $labelExtension
    ): void {
        $labelPath = $this->shippingLabelsPath
            . '/' . $this->getFilename($shippingExport)
            . '.' . $labelExtension;

        $this->filesystem->dumpFile($labelPath, $labelContent);
        $shippingExport->setLabelPath($labelPath);

        $this->shippingExportRepository->add($shippingExport);
    }

    private function getFilename(ShippingExportInterface $shippingExport): string
    {
        $shipment = $shippingExport->getShipment();
        Assert::notNull($shipment);

        $order = $shipment->getOrder();
        Assert::notNull($order);

        /** @var string */
        $orderNumber = $order->getNumber();

        /** @var int */
        $shipmentId = $shipment->getId();

        return implode(
            '_',
            [
                $shipmentId,
                preg_replace('~[^A-Za-z0-9]~', '', $orderNumber),
            ]
        );
    }

    private function markShipmentAsExported(ShippingExportInterface $shippingExport): void
    {
        $shippingExport->setState(ShippingExportInterface::STATE_EXPORTED);
        $shippingExport->setExportedAt(new \DateTime());

        $this->shippingExportRepository->add($shippingExport);
    }
}
