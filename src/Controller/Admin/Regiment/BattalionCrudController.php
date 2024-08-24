<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\Battalion;
use App\Repository\Regiment\BattalionRepository;
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

class BattalionCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return BattalionRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return Battalion::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud);
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_EDIT, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield NumberField::new('sort')->setRequired(true);
        yield TextField::new('title')->setRequired(true);
        yield TextField::new('customName')->hideOnIndex()->setHelp("Unofficial name for this unit. Eg 'Alpha Company', 'Echo Company', 'Phantom Legion', ...");
        yield NumberField::new('companies.count')->setDisabled(true);
        // yield CollectionField::new('companies')
        //     ->setEntryType(CompanyType::class)
        //     ->setFormTypeOptions(['by_reference' => false])
        //     ->onlyOnForms();
        // yield CollectionField::new('platoons')
        //     ->setEntryType(CompanyCrudController::class)
        //     ->setFormTypeOptions([
        //         'by_reference' => false,
        //     ]);
    }
}
