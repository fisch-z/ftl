<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\NavigationGenerator;
use App\Service\RegimentalDataExporter;
use League\Csv\Writer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Twig\Environment;

#[Route("/sqd")]
class RegimentalDataController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        Environment                    $twig,
        private RegimentalDataExporter $regimentalDataExporter,
        private CacheInterface         $cache,
        private KernelInterface        $appKernel,
        private NavigationGenerator    $navigationGenerator,
    )
    {
        // $twig->addGlobal("tabs", $this->getTabs());
    }

    // protected function getTabs(): array
    // {
    //     return [];
    //     return $this->navigationGenerator->generateNavigationItems([
    //         [
    //             "title" => "Assignments",
    //             "route" => "app_regimentalData_assignments",
    //         ],
    //         [
    //             "title" => "Sections",
    //             "route" => "app_regimentalData_section",
    //         ],
    //         [
    //             "title" => "Platoons",
    //             "route" => "app_regimentalData_platoon",
    //         ],
    //         [
    //             "title" => "Companies",
    //             "route" => "app_regimentalData_company",
    //         ],
    //         [
    //             "title" => "Platoons",
    //             "route" => "app_regimentalData_battalion",
    //         ],
    //         [
    //             "title" => "Service Branches",
    //             "route" => "app_regimentalData_serviceBranches",
    //         ],
    //     ]);
    // }

    #[Route("/", name: "app_regimentaldata_index", methods: ["GET"])]
    public function index(): Response
    {
        // return $this->redirectToRoute("app_regimentalData_assignments");
        return $this->render("regimentalData/index.html.twig");
    }

    protected function getResponseForData(\Generator $data): Response
    {
        $rows = iterator_to_array($data);
        $writer = Writer::createFromString();
        if ($rows) {
            $writer->insertOne(array_keys($rows[0]));
            $writer->insertAll($rows);
        }
        $response = new Response();
        // $response->headers->set("Content-Type", "text/csv");
        $response->headers->set("Content-Type", "text/plain");
        $response->setContent($writer->toString());
        return $response;
    }

    #[Route("/s1-api-master-rawmilpacs.csv", name: "app_regimentaldata_s1apimasterrawmilpacscsv", methods: ["GET"])]
    public function s1ApiMasterRawMilpacs(): Response
    {
        return $this->getResponseForData($this->regimentalDataExporter->s1ApiMasterRawMilpacs());
    }

    #[Route("/s1-operations-profiles.csv", name: "app_regimentaldata_s1operationsprofilescsv", methods: ["GET"])]
    public function s1ApiOperationsProfiles(): Response
    {
        return $this->getResponseForData($this->regimentalDataExporter->s1ApiOperationsProfiles());
    }

    #[Route("/s1-operations-awards.csv", name: "app_regimentaldata_s1operationsawardscsv", methods: ["GET"])]
    public function s1ApiOperationsAwards(Request $request): Response
    {
        return $this->getResponseForData($this->regimentalDataExporter->s1ApiOperationsAwards($request->get("battalion")));
    }

    #[Route("/s1-operations-operations-records.csv", name: "app_regimentaldata_s1operationsoperationsrecordscsv", methods: ["GET"])]
    public function s1ApiOperationsOperationsRecords(Request $request): Response
    {
        return $this->getResponseForData($this->regimentalDataExporter->s1ApiOperationsOperationsRecords($request->get("battalion")));
    }

    #[Route("/billet-assignments.csv", name: "app_regimentaldata_billetassignmentscsv", methods: ["GET"])]
    public function billetAssignmentsCsv(): Response
    {
        return $this->getResponseForData($this->regimentalDataExporter->billetAssignmentsCsv());
    }

    #[Route("/profiles.csv", name: "app_regimentaldata_profilescsv", methods: ["GET"])]
    public function profiles(Request $request): Response
    {
        $roster = $request->get("roster");
        return $this->getResponseForData($this->regimentalDataExporter->profiles($request->get("battalion"), $request->get("company"), $roster ? explode(",", $roster) : null));
    }
}
