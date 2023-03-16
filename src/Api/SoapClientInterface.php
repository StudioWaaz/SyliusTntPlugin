<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin\Api;

interface SoapClientInterface
{
    public function createShipment(array $requestData, string $wsdl);
}
