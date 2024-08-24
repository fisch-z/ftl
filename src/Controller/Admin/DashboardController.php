<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Regiment\BilletAssignmentCrudController;
use App\Entity\MilpacProfile;
use App\Entity\Regiment\Battalion;
use App\Entity\Regiment\BilletAssignment;
use App\Entity\Regiment\BilletPosition;
use App\Entity\Regiment\Company;
use App\Entity\Regiment\Platoon;
use App\Entity\Regiment\Rank;
use App\Entity\Regiment\Section;
use App\Entity\Regiment\ServiceBranch;
use App\Entity\Uniform\MilpacProfileUniformOverride;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(BilletAssignmentCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Regimental Data Admin');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->showEntityActionsInlined(true)
            ->setPaginatorPageSize(100)
            ->setDefaultSort(["id" => "ASC"]);
    }

    public function configureActions(): Actions
    {
        return parent::configureActions()
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE);
    }

    public function configureMenuItems(): iterable
    {
        // FTL app_home_index
        // SQD app_regimentaldata_index
        // UAV app_uniform_index
        yield MenuItem::linkToUrl("FTL", "", $this->generateUrl("app_home_index"));
        yield MenuItem::linkToUrl("SQD", "", $this->generateUrl("app_regimentaldata_index"));
        yield MenuItem::linkToUrl("UAV", "", $this->generateUrl("app_uniform_index"));
        yield MenuItem::section("Regimental Data");
        yield MenuItem::linkToCrud('Milpac Profiles', 'fas fa-list', MilpacProfile::class);
        yield MenuItem::linkToCrud('Billet Assignments', 'fas fa-list', BilletAssignment::class);
        yield MenuItem::linkToCrud('Sections', 'fas fa-list', Section::class);
        yield MenuItem::linkToCrud('Platoons', 'fas fa-list', Platoon::class);
        yield MenuItem::linkToCrud('Companies', 'fas fa-list', Company::class);
        yield MenuItem::linkToCrud('Battalions', 'fas fa-list', Battalion::class);
        yield MenuItem::linkToCrud('Service Branches', 'fas fa-list', ServiceBranch::class);
        yield MenuItem::linkToCrud('Billet Positions', 'fas fa-list', BilletPosition::class);
        yield MenuItem::linkToCrud('Ranks', 'fas fa-list', Rank::class);
        yield MenuItem::linkToCrud('Uniform Overrides', 'fas fa-list', MilpacProfileUniformOverride::class);

        // yield MenuItem::section("Uniform Settings");
    }
}
