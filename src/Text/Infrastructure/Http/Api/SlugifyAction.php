<?php

declare(strict_types=1);

namespace App\Text\Infrastructure\Http\Api;

use App\Text\Application\SlugifyTitleQuery;
use Phil\DddBundle\Application\Bus\QueryBus;
use Phil\DddBundle\Domain\Exception\InvalidValue;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * GET /api/slugify?title=…: the slug of a title as JSON, asked of the application through the query bus of
 * phil/ddd-bundle. A title the domain refuses answers 422 with the reason.
 *
 * @see SlugifyActionTest
 */
#[AsController]
final readonly class SlugifyAction
{
    public function __construct(
        private QueryBus $queryBus,
    ) {
    }

    #[Route(path: '/slugify', name: self::class, methods: ['GET'], stateless: true)]
    public function __invoke(#[MapQueryParameter] string $title = ''): JsonResponse
    {
        try {
            $slug = $this->queryBus->ask(new SlugifyTitleQuery($title));
        } catch (InvalidValue $invalidValue) {
            return new JsonResponse(['error' => $invalidValue->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['slug' => $slug->value]);
    }
}
