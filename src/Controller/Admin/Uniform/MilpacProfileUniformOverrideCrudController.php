<?php

declare(strict_types=1);

namespace App\Controller\Admin\Uniform;

use App\Entity\Uniform\MilpacProfileUniformOverride;
use App\Repository\Uniform\MilpacProfileUniformOverrideRepository;
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

class MilpacProfileUniformOverrideCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return MilpacProfileUniformOverrideRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return MilpacProfileUniformOverride::class;
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
        yield AssociationField::new('milpacProfile')->setRequired(true)->autocomplete();
        yield AssociationField::new('serviceBranch');
        yield AssociationField::new('preferredPrimarySpecialSkillServiceBranch');
        $choices = [
            "Field Medical Badge" => "Field Medical Badge",
            "Explosive Ordnance Disposal Badge" => "Explosive Ordnance Disposal Badge",
            "Master Gunner Badge" => "Master Gunner Badge",
            "Air Assault Badge" => "Air Assault Badge",
            "Forward Air Controller Badge" => "Forward Air Controller Badge",
            "Army Parachutist Badge" => "Army Parachutist Badge",
            "High Altitude Low Opening Badge" => "High Altitude Low Opening Badge",
        ];
        yield ChoiceField::new("preferredSecondarySpecialSkill1")->setFormTypeOptions(['choices' => $choices]);
        yield ChoiceField::new("preferredSecondarySpecialSkill2")->setFormTypeOptions(['choices' => $choices]);
        yield ChoiceField::new("preferredSecondarySpecialSkill3")->setFormTypeOptions(['choices' => $choices]);
        yield ChoiceField::new("preferredCrest")->setFormTypeOptions(['choices' => [
            "Command Staff-Command Staff" => "Command Staff",
            "JAG" => "JAG",
            "MP" => "MP",
            "RTC-Drill Instructor" => "RTC-Drill Instructor",
            "RTC-HQ" => "RTC-HQ",
            "S1" => "S1",
            "S2" => "S2",
            "S3" => "S3",
            "S5" => "S5",
            "S6" => "S6",
            "S7" => "S7",
            "WAG" => "WAG",
        ]]);
        yield ChoiceField::new("preferredBadge")->setFormTypeOptions(['choices' => [
            "General Staff Badge" => "General Staff Badge",
            "Military Police Badge" => "Military Police Badge",
            "Recruiter Badge" => "Recruiter Badge",
            "Drill Instructor Badge" => "Drill Instructor Badge",
            "Instructor Badge" => "Instructor Badge",
            "Mission Controller Badge" => "Mission Controller Badge",
        ]]);
    }
}
