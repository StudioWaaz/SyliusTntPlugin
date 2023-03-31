<?php

declare(strict_types=1);

namespace Waaz\SyliusTntExportPlugin\Action;

use TNTExpress\Model\City;
use FOS\RestBundle\View\View;
use TNTExpress\Client\TNTClientInterface;
use TNTExpress\Exception\ClientException;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\DataTransformerInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CityChoicesByZipCodeAction
{
    public function __construct(
        private TNTClientInterface $tntClient
    ) {
    }

    public function __invoke(string $postcode): JsonResponse
    {
        if ('' === $postcode) {
            throw new NotFoundHttpException();
        }
        
        try {
            $result = $this->tntClient->getCitiesGuide($postcode);
            $cities = array_map(fn(City $city): string => ucwords(strtolower($city->getName() ?? '')), $result);

        } catch (ClientException $e) {
            $cities = [];
        }

        return new JsonResponse($cities);
    }
}
