<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\BilletAssignment;
use App\Repository\Regiment\BilletAssignmentRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BilletAssignmentCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return BilletAssignmentRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return BilletAssignment::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->setPermission(Action::DELETE, "ROLE_ADMIN")
            ->setPermission(Action::NEW, "ROLE_ADMIN");
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('milpacId')->setDisabled($pageName !== Crud::PAGE_NEW);
        yield TextField::new('milpacTitle')->setDisabled($pageName !== Crud::PAGE_NEW);
        yield AssociationField::new('position');
        yield AssociationField::new('section');
        // yield TextField::new('section.serviceBranch.title')->setLabel("Service Branch")->setDisabled(true);
        // yield TextField::new('section.platoon.title')->setLabel("Platoon")->setDisabled(true);
        // yield TextField::new('section.platoon.company.title')->setLabel("Company")->setDisabled(true);
        // yield TextField::new('section.platoon.company.battalion.title')->setLabel("Battalion")->setDisabled(true);
    }
}
