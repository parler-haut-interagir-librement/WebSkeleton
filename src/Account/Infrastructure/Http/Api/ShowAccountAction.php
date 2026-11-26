<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Api;

use App\Account\Application\AccountView;
use App\Account\Application\ShowAccountQuery;
use App\Account\Domain\AccountId;
use App\Account\Domain\Role;
use Phil\DddBundle\Application\Bus\QueryBus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * GET /api/accounts/{account}: an account as JSON, for the administrators. The placeholder reaches the controller as
 * an AccountId, built by the value resolver of phil/ddd-bundle, which answers 404 to anything but a UUIDv7; the query
 * bus answers the view, and the Serializer writes its identifier as the canonical string.
 *
 * @see ShowAccountActionTest
 */
#[AsController]
#[IsGranted(Role::ADMIN)]
final readonly class ShowAccountAction
{
    public function __construct(
        private QueryBus $queryBus,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route(path: '/accounts/{account}', name: self::class, methods: ['GET'])]
    public function __invoke(AccountId $account): JsonResponse
    {
        $view = $this->queryBus->ask(new ShowAccountQuery($account));
        if (!$view instanceof AccountView) {
            throw new NotFoundHttpException('No account has this identifier.');
        }

        return JsonResponse::fromJsonString($this->serializer->serialize($view, 'json'));
    }
}
