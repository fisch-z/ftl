<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\Company;
use App\Repository\Regiment\CompanyRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompanyCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return CompanyRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(["battalion.title" => "ASC", "title" => "ASC", "id" => "ASC"]);
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
        yield TextField::new('title')->onlyOnForms()->setRequired(true);
        yield TextField::new('titleFull')->setDisabled(true);
        yield TextField::new('customName')->hideOnIndex()->setHelp("Unofficial name for this unit. Eg 'Alpha Company', 'Echo Company', 'Phantom Legion', ...");
        yield AssociationField::new('battalion')->setRequired(true)->hideOnIndex();
        yield NumberField::new('platoons.count')->setDisabled(true);
        // yield CollectionField::new('platoons')
        //     ->setEntryType(PlatoonType::class)
        //     ->setFormTypeOptions(['by_reference' => false])
        //     ->onlyOnForms();
    }
}
