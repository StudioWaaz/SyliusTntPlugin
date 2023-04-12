<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Exception\TimeoutException;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCodeInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\Provider;
use Sylius\Component\Core\Model\OrderInterface;
use TNTExpress\Client\TNTClientInterface;
use TNTExpress\Exception\ClientException;
use TNTExpress\Model\DropOffPoint;
use Waaz\SyliusTntPlugin\Model\TntPickupPointCode;
use Webmozart\Assert\Assert;

final class TntProvider extends Provider
{
    public function __construct(private TNTClientInterface $client)
    {
    }

    public function findPickupPoints(OrderInterface $order): iterable
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return [];
        }

        $postcode = $shippingAddress->getPostcode();
        $city = $shippingAddress->getCity();
        if (null === $city || null === $postcode) {
            return [];
        }

        try {
            $city = strtoupper($city);
            $points = $this->client->getDropOffPoints($postcode, $city);
        } catch (ClientException $e) {
            throw new TimeoutException($e);
        }

        foreach ($points as $item) {
            yield $this->transform($item);
        }
    }

    public function findPickupPoint(PickupPointCodeInterface $code): ?PickupPointInterface
    {
        Assert::isInstanceOf($code, TntPickupPointCode::class);

        $zipcode = $code->getZipCodePart();
        Assert::string($zipcode);

        $city = $code->getCityPart();
        Assert::string($city);

        $result = $this->client->getDropOffPoints($zipcode, $city);

        foreach ($result as $item) {
            if ($item->getXETTCode() === $code->getIdPart()) {
                return $this->transform($item);
            }
        }

        return null;
    }

    public function findAllPickupPoints(): iterable
    {
        return [];
    }

    private function transform(DropOffPoint $dropOffPoint): PickupPoint
    {
        $countryCode = 'FR'; // TNT only operates in France

        $pickupPoint = new PickupPoint();

        $pickupPointCode = new TntPickupPointCode($dropOffPoint->getXETTCode(), $this->getCode(), $countryCode);
        $pickupPointCode->setZipcode((string) $dropOffPoint->getZipCode());
        $pickupPointCode->setCity((string) $dropOffPoint->getCity());

        $pickupPoint->setCode($pickupPointCode);
        $pickupPoint->setName((string) $dropOffPoint->getName());
        $pickupPoint->setAddress((string) $dropOffPoint->getAddress1() . ' ' . (string) $dropOffPoint->getAddress2());
        $pickupPoint->setZipCode((string) $dropOffPoint->getZipCode());
        $pickupPoint->setCity((string) $dropOffPoint->getCity());
        $pickupPoint->setCountry($countryCode);
        $pickupPoint->setLatitude((float) $dropOffPoint->getLatitude());
        $pickupPoint->setLongitude((float) $dropOffPoint->getLongitude());

        return $pickupPoint;
    }

    public function getCode(): string
    {
        return 'tnt';
    }

    public function getName(): string
    {
        return 'TNT';
    }
}
