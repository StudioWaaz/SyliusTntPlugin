<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Model;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCodeInterface;

interface TntPickupPointCodeInterface extends PickupPointCodeInterface
{
    public function getZipCodePart(): ?string;

    public function getCityPart(): ?string;
}
