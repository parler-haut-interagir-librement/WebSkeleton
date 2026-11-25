<?php

declare(strict_types=1);

namespace App\Account\Infrastructure\Http\Admin;

use App\Account\Infrastructure\Persistence\Doctrine\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;

/**
 * The accounts in the administration, listed and shown: they are created by signing up and deleted from the privacy
 * choices of their member, so the administration neither creates, edits nor deletes them.
 *
 * @extends AbstractCrudController<User>
 *
 * @see UserCrudControllerTest
 */
final class UserCrudController extends AbstractCrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.user.singular')
            ->setEntityLabelInPlural('admin.user.plural')
            ->setDefaultSort(['username' => 'ASC'])
        ;
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE, Action::BATCH_DELETE)
        ;
    }

    /**
     * @return iterable<TextField|EmailField|ArrayField|DateTimeField>
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', 'admin.user.username');
        yield EmailField::new('email', 'admin.user.email');
        yield ArrayField::new('roles', 'admin.user.roles');
        yield DateTimeField::new('createdAt', 'admin.user.created_at');
    }
}
