<?php

declare(strict_types=1);

namespace Waaz\SyliusTntPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class WaazSyliusTntPlugin extends Bundle
{
    use SyliusPluginTrait;
}
