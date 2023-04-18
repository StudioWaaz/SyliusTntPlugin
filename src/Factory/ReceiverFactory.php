<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Factory;

use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use TNTExpress\Model\Receiver;
use Waaz\SyliusTntPlugin\Model\TntPickupPointCode;

class ReceiverFactory implements ReceiverFactoryInterface
{
    public function createNew(ShippingGatewayInterface $shippingGateway, ShipmentInterface $shipment): Receiver
    {
        $receiver = new Receiver();

        /** @var OrderInterface $order */
        $order = $shipment->getOrder();

        /** @var AddressInterface $address */
        $address = $order->getShippingAddress();

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $receiver->setPhoneNumber($address->getPhoneNumber())
            ->setEmailAddress($customer->getEmail())
        ;

        if ($shipment instanceof PickupPointAwareInterface && $shipment->hasPickupPointId()) {
            /** @var string $pointId */
            $pointId = $shipment->getPickupPointId();
            $tntCode = TntPickupPointCode::createFromString($pointId);
            $receiver->setType('DROPOFFPOINT')
                ->setTypeId($tntCode->getIdPart())
            ;
        } else {
            $receiver->setContactFirstName($address->getFirstName())
                ->setContactLastName($address->getLastName())
                ->setName($address->getCompany())
                ->setAddress1($address->getStreet())
                ->setCity($address->getCity())
                ->setZipCode($address->getPostcode())
                ->setType($shippingGateway->getConfigValue('receiver_type'))
            ;
        }

        return $receiver;
    }
}
