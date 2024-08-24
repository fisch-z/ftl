<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\BilletPosition;
use App\Repository\Regiment\BilletPositionRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BilletPositionCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return BilletPositionRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return BilletPosition::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title');
        yield NumberField::new('sort');
        yield NumberField::new('billetAssignments.count')->setDisabled(true);
    }
}
