<?php

declare(strict_types=1);

namespace App\Controller\Admin\Regiment;

use App\Entity\Regiment\Rank;
use App\Repository\Regiment\RankRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RankCrudController extends AbstractCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return RankRepository::update_query_builder(parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters), "entity");
    }

    public static function getEntityFqcn(): string
    {
        return Rank::class;
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
        yield TextField::new('titleShort');
        yield TextField::new('rankImageUrl');
        $rankTypes = ['officer', 'nonCommissionedOfficer', 'warrantOfficer', 'trooper'];
        yield ChoiceField::new("rankType")->setFormTypeOptions([
            'choices' => array_combine($rankTypes, $rankTypes),
        ]);
        yield NumberField::new('sort');
        // yield NumberField::new('milpacProfiles.count')->setDisabled(true);
    }
}
