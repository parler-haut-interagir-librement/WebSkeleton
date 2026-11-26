<?php

declare(strict_types=1);

namespace App\Text\Infrastructure\Http\Controller;

use App\Text\Application\SlugifyTitleQuery;
use Phil\DddBundle\Application\Bus\QueryBus;
use Phil\DddBundle\Domain\Exception\InvalidValue;
use Phil\FeatureFlagsBundle\Infrastructure\Http\IsFeatureEnabled;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The page of the slug of a title, /fr/slug and /en/slug: a form sent by GET, which works without JavaScript, and the
 * slug asked of the application through the query bus. It is the feature flag of the template: the page answers 404,
 * and the home page leaves out its link, until an administrator turns the flag on under /admin/flags.
 *
 * @see SlugPageActionTest
 */
#[IsFeatureEnabled(self::FLAG)]
final class SlugPageAction extends AbstractController
{
    /**
     * The feature flag of the page, declared in config/packages/phil_feature_flags.yaml.
     */
    public const string FLAG = 'text.slug-page';

    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route(path: '/slug', name: self::class, methods: ['GET', 'HEAD'])]
    public function __invoke(#[MapQueryParameter] string $title = ''): Response
    {
        $slug = null;
        $error = null;

        if ('' !== $title) {
            try {
                $slug = $this->queryBus->ask(new SlugifyTitleQuery($title))->value;
            } catch (InvalidValue $invalidValue) {
                $error = $invalidValue->getMessage();
            }
        }

        return $this->render('text/slug.html.twig', [
            'title' => $title,
            'slug' => $slug,
            'error' => $error,
        ], new Response(status: null === $error ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
