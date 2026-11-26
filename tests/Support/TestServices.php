<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;

/**
 * The services the test environment alone declares, in the when@test section of config/services.yaml, fetched with
 * their type checked: PHPStan reads the container of the dev environment, which does not have them.
 */
final class TestServices
{
    /**
     * @template T of object
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    public static function get(ContainerInterface $container, string $id, string $type): object
    {
        $service = $container->get($id);

        if (!$service instanceof $type) {
            throw new UnexpectedValueException(\sprintf('The service "%s" is not a %s.', $id, $type));
        }

        return $service;
    }
}
