<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use Override;
use Symfony\Component\HttpKernel\Kernel;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * The versions of Symfony and PHP and the server API, written in the footer.
 *
 * @see EnvExtensionTest
 */
final class EnvExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @return array<string,string>
     */
    #[Override]
    public function getGlobals(): array
    {
        return [
            'sf_version' => Kernel::VERSION,
            'php_version' => \PHP_VERSION,
            'php_sapi' => \PHP_SAPI,
        ];
    }
}
