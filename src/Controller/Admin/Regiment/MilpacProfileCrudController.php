<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\MilpacProfile;
use App\Repository\MilpacProfileRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class MilpacProfileCrudController extends AbstractCrudController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return MilpacProfileRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return MilpacProfile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud);
    }


    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::DELETE)
            ->disable(Action::EDIT)
            ->disable(Action::NEW)
            // ->remove(Crud::PAGE_INDEX, Action::DELETE)
            // ->setPermission(Action::DETAIL, "")
            // ->setPermission(Action::EDIT, "ROLE_ADMIN")
            // ->setPermission(Action::DELETE, "ROLE_ADMIN")
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        $url = $this->adminUrlGenerator
            ->setController(BilletAssignmentCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId("--relatedId--")
            ->unset(EA::FILTERS)
            ->unset(EA::PAGE)
            ->unset(EA::QUERY)
            ->unset(EA::SORT)
            ->generateUrl();
        return [
            TextField::new('keycloakId')->setDisabled(true)->hideOnIndex(),
            IdField::new('forumProfileId')->setDisabled(true)->hideOnIndex(),
            IdField::new('userId')->setDisabled(true)->hideOnIndex(),
            TextField::new('username')->setDisabled(true),
            DateField::new('joinedAt')->setDisabled(true)->hideOnIndex(),
            DateField::new('syncedAt')->setDisabled(true)->hideOnIndex(),
            DateField::new('milpacDataChangeAt')->setDisabled(true)->hideOnIndex(),
            DateField::new('uniformReplacedAt')->setDisabled(true)->hideOnIndex(),
            AssociationField::new("primaryBilletAssignment")
                ->setDisabled(true)
                ->setTemplatePath("admin/field/association.html.twig")
                ->hideOnIndex()

            ,
            AssociationField::new("billetAssignments")
                ->setDisabled(true)
                ->setCustomOption("relatedUrl", $url)
                ->setTemplatePath("admin/field/association.html.twig")
                ->setTextAlign("left")
            ,
            TextField::new('rosterType')->setDisabled(true),
            TextareaField::new('dataJson')->hideOnIndex()->setDisabled(true),
        ];
    }
}
