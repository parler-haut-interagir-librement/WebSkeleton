<?php

declare(strict_types=1);

namespace App\Tests\Unit\Account\Infrastructure\Http\Admin;

use App\Account\Infrastructure\Http\Admin\UserCrudController;
use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserCrudController::class)]
final class UserCrudControllerTest extends TestCase
{
    public function testTheAccountsAreListedAndShownOnly(): void
    {
        $controller = new UserCrudController();
        self::assertSame(User::class, UserCrudController::getEntityFqcn());

        $crud = $controller->configureCrud(Crud::new())->getAsDto();
        self::assertSame(['username' => 'ASC'], $crud->getDefaultSort());
        self::assertSame('admin.user.singular', $crud->getEntityLabelInSingular());
        self::assertSame('admin.user.plural', $crud->getEntityLabelInPlural());

        $actions = $controller->configureActions(Actions::new())->getAsDto(Crud::PAGE_INDEX);
        self::assertSame([Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE], $actions->getDisabledActions());
        self::assertNotNull($actions->getAction(Crud::PAGE_INDEX, Action::DETAIL));

        $fields = array_map(
            static fn (FieldInterface $field): array => [$field->getAsDto()->getProperty(), $field->getAsDto()->getLabel()],
            [...$controller->configureFields(Crud::PAGE_INDEX)],
        );
        self::assertSame([
            ['username', 'admin.user.username'],
            ['email', 'admin.user.email'],
            ['roles', 'admin.user.roles'],
            ['createdAt', 'admin.user.created_at'],
        ], $fields);
    }
}
