<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\Section;
use App\Repository\Regiment\SectionRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class SectionCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return SectionRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return Section::class;
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
        yield AssociationField::new('serviceBranch')->setRequired(true);
        yield AssociationField::new('platoon')->setRequired(true)->hideOnIndex();
        yield NumberField::new('billetAssignments.count')->setDisabled(true)->setLabel("Billet Types");
        yield TextField::new("bannerUrl")->hideOnIndex();
        yield TextField::new("practiceDay")->hideOnIndex();
        $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        yield ChoiceField::new("practiceDay")->setFormTypeOptions([
            'choices' => array_combine($days, $days),
        ]);
        yield TimeField::new("practiceTime");
        // yield TextField::new('platoon.company.title')->setLabel("Company")->setDisabled(true);
        // yield TextField::new('platoon.company.battalion.title')->setLabel("Battalion")->setDisabled(true);
    }
}

