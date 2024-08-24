<?php

declare(strict_types=1);

namespace App\Controller\Accountability;

use App\Entity\MilpacProfile;
use App\Entity\Regiment\Section;
use App\Entity\Regiment\SectionPractice;
use App\Entity\RosterTypeEnum;
use App\Form\Regiment\SectionPracticeType;
use App\Repository\MilpacProfileRepository;
use App\Repository\Regiment\SectionPracticeRepository;
use App\Repository\Regiment\SectionRepository;
use App\Service\ChainOfCommandService;
use App\Service\OrganisationalWeekService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;

#[Route("/ace")]
class AccountabilityController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        Environment                                $twig,
        private readonly MilpacProfileRepository   $milpacProfileRepository,
        private readonly SectionRepository         $sectionRepository,
        private readonly SectionPracticeRepository $sectionPracticeRepository,
        private readonly OrganisationalWeekService $organisationalWeekService,
        private readonly ChainOfCommandService     $chainOfCommandService,
        private readonly CacheInterface            $cache,
        private readonly HttpClientInterface       $httpClient,
        private readonly EntityManagerInterface    $entityManager
    )
    {
    }

    private static array $allowed_status = [
        "rcStatus" => [
            "N/A",
            "YES",
            "NO",
            "LOA",
            "TRANS",
            "AWOL",
            "DISCH",
            "RTC",
        ],
        "spStatus" => [
            "N/A",
            "YES",
            "NO",
        ],
    ];

    #[Route("/", name: "app_accountability_index", methods: ["GET"])]
    public function index(): Response
    {
        // TODO query battlemetrics for SP attendance once we have an api key
        // https://api.battlemetrics.com/sessions?filter%5Bservers%5D=15016784&filter%5Brange%5D=2024-07-14T00:00:00Z%3A2024-07-15T19:30:56Z&page%5Bsize%5D=100
        $week = $this->organisationalWeekService->getCurrentWeek()->getNextWeek();
        // $rollCallTemplates = [];
        $tree = [];
        foreach ($this->sectionRepository->findBy(["practiceDay" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"]]) as $section) {
            // $rollCallTemplates[] = [
            //     "section" => $section,
            // ];
            $platoon = $section->getPlatoon();
            $company = $platoon->getCompany();
            $battalion = $company->getBattalion();
            $tree[$battalion->getId()] = $tree[$battalion->getId()] ?? ["battalion" => $battalion, "companies" => []];
            $tree[$battalion->getId()]["companies"][$company->getId()] = $tree[$battalion->getId()]["companies"][$company->getId()] ?? ["company" => $company, "platoons" => []];
            $tree[$battalion->getId()]["companies"][$company->getId()]["platoons"][$platoon->getId()] = $tree[$battalion->getId()]["companies"][$company->getId()]["platoons"][$platoon->getId()] ?? ["platoon" => $platoon, "sections" => []];
            $tree[$battalion->getId()]["companies"][$company->getId()]["platoons"][$platoon->getId()]["sections"][] = ["section" => $section];
        }
        return $this->render("accountability/index.html.twig", [
            "week" => $week,
            "battalions" => $tree,
        ]);
    }

    #[Route("/section/{id<\d+>}", name: "app_accountability_section", methods: ["GET"])]
    public function section(Section $section)
    {
        $week = $this->organisationalWeekService->getCurrentWeek()->getNextWeek();
        return $this->render("accountability/section.html.twig", [
            "section" => $section,
            // "week" => $week,
            // "bbCode" => $this->renderRollCallTemplateForSection($section, $week),
        ]);
    }

    #[Route("/section/{sectionId<\d+>}/practice/{id<\d+>}", name: "app_accountability_section_practice", methods: ["GET"])]
    public function sectionPractice(int $sectionId, SectionPractice $sectionPractice)
    {
        $section = $sectionPractice->getSection();
        return $this->render("accountability/sectionPractice.html.twig", [
            "section" => $section,
            "sectionPractice" => $sectionPractice,
            "allowedStatus" => self::$allowed_status,
            "bbCode" => $this->renderRollCallTemplateForSection(
                $section,
                $sectionPractice,
            ),
        ]);
    }

    #[Route("/section/{sectionId<\d+>}/practice/{id<\d+>}/updateTrooper", name: "app_accountability_section_practice_update_trooper", methods: ["GET"])]
    public function sectionPracticeUpdateTroopers(int $sectionId, SectionPractice $sectionPractice, Request $request)
    {
        // TODO refactor this to use turbo and a stimulus controller to post a form instead of doing a get request with query parameters
        $attendance = $sectionPractice->getAttendance();;
        $i = (int)$request->get("index");
        $key = array_intersect([$request->get("key")], ["rcStatus", "spStatus"])[0] ?? null;
        $value = array_intersect([$request->get("value")], match ($key) {
            "rcStatus" => self::$allowed_status["rcStatus"],
            "spStatus" => self::$allowed_status["spStatus"],
            default => null,
        })[0] ?? null;
        if (!$key || !$value || !isset($attendance[$i][$key])) {
            throw new BadRequestHttpException();
        }
        $attendance[$i][$key] = $value;
        $sectionPractice->setAttendance($attendance);
        $this->entityManager->persist($sectionPractice);
        $this->entityManager->flush();
        return $this->redirectToRoute("app_accountability_section_practice", ["sectionId" => $sectionId, "id" => $sectionPractice->getId()]);
    }

    #[Route("/section/{id<\d+>}/practice/new", name: "app_accountability_section_practice_new", methods: ['GET', 'POST'])]
    public function sectionPracticeNew(Section $section, Request $request)
    {
        $week = $this->organisationalWeekService->getCurrentWeek()->getNextWeek();

        $sectionPractice = new SectionPractice();
        $dateTime = $week->getFirstDay()->modify("next {$section->getPracticeDay()}")->setTime((int)$section->getPracticeTime()->format("H"), (int)$section->getPracticeTime()->format("i"));
        $sectionPractice->setDateTime($dateTime);

        $form = $this->createForm(SectionPracticeType::class, $sectionPractice);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sectionPractice->setSection($section);

            $query = $this->milpacProfileRepository->createQueryBuilder("entity");
            $query->where("entity.rosterType IN (:rosterType)")->setParameter("rosterType", RosterTypeEnum::COMBAT);
            $query->andWhere("section.id LIKE :sectionId")->setParameter("sectionId", $section->getId());
            /** @var MilpacProfile[] $milpacProfiles */
            $milpacProfiles = $query->getQuery()->execute();
            $attendance = [];
            foreach ($milpacProfiles as $milpacProfile) {
                $attendance[] = [
                    "userId" => $milpacProfile->getUserId(),
                    // TODO we shouldn't save rosterId into the attendance data, we should fetch it on by userId on demand
                    "rosterId" => $milpacProfile->getForumProfileId(),
                    "userNameWithRank" => $milpacProfile->getUserNameWithRank(),
                    "positionTitle" => $milpacProfile->getPrimaryBilletAssignment()->getPosition()->getTitle(),
                    "rcStatus" => "N/A",
                    "spStatus" => "N/A",
                ];
            }
            $sectionPractice->setAttendance($attendance);

            $this->entityManager->persist($sectionPractice);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_accountability_section', ["id" => $section->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('accountability/sectionPracticeNew.html.twig', [
            'section' => $section,
            'section_practice' => $sectionPractice,
            'form' => $form,
        ]);

    }

    #[Route("/section/{sectionId<\d+>}/practice/{id<\d+>}/delete", name: "app_accountability_section_practice_delete", methods: ["POST"])]
    public function sectionPracticeDelete(int $sectionId, SectionPractice $sectionPractice, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($sectionPractice);
        $entityManager->flush();
        return $this->redirectToRoute('app_accountability_section', ["id" => $sectionId], Response::HTTP_SEE_OTHER);
    }

    protected function renderRollCallTemplateForSection(Section $section, SectionPractice $sectionPractice)
    {
        $sectionPracticeDate = $sectionPractice->getDateTime();
        $week = $sectionPractice->getWeek();
        // TODO weekly events show the wrong date and carry no repeat information in the ICS feed. so we currently have events hardcoded to squad, we could probably just check if an event is in the past, and if so, assume just it's repeating weekly
        $upcomingEvents = [
            [
                "title" => "[B]SQUAD | Section Practice[/B]",
                "link" => "",
                "date" => $sectionPracticeDate,
            ],
            [
                "title" => "[B]SQUAD | Section Practice[/B]",
                "link" => "",
                "date" => $week->getNextWeek()->getFirstDay()->modify("next {$section->getPracticeDay()}")->setTime((int)$section->getPracticeTime()->format("H"), (int)$section->getPracticeTime()->format("i")),
            ],
            [
                "title" => "SQUAD | Warrior Wednesdays",
                "link" => "https://7cav.us/events/967/",
                "date" => $week->getPreviousWeek()->getFirstDay()->modify("next Wednesday")->setTime(22, 0),
            ],
            [
                "title" => "SQUAD | Warrior Wednesdays",
                "link" => "https://7cav.us/events/967/",
                "date" => $week->getNextWeek()->getFirstDay()->modify("next Wednesday")->setTime(22, 0),
            ],
            [
                "title" => "SQUAD | Warrior Wednesdays",
                "link" => "https://7cav.us/events/967/",
                "date" => $week->getFirstDay()->modify("next Wednesday")->setTime(22, 0),
            ],
            [
                "title" => "SQUAD | Squad Saturdays",
                "link" => "https://7cav.us/events/904/",
                "date" => $week->getPreviousWeek()->getFirstDay()->modify("next Saturdays")->setTime(20, 0),
            ],
            [
                "title" => "SQUAD | Squad Saturdays",
                "link" => "https://7cav.us/events/904/",
                "date" => $week->getNextWeek()->getFirstDay()->modify("next Saturdays")->setTime(20, 0),
            ],
            [
                "title" => "SQUAD | Squad Saturdays",
                "link" => "https://7cav.us/events/904/",
                "date" => $week->getFirstDay()->modify("next Saturdays")->setTime(20, 0),
            ],
        ];
        $calendarIcs = $this->cache->get("accountability_events_34", function (CacheItemInterface $cacheItem) {
            $cacheItem->expiresAfter(300); // 5 minutes
            $response = $this->httpClient->request("GET", "https://7cav.us/events/calendar/34/export");
            return $response->getContent();
        });
        if ($calendarIcs) {
            // TODO description contains unescaped line breaks which trips up the ICS parser, the feed is probably not following the specs
            $str = preg_replace("@DESCRIPTION:.*?DTSTART:@s", "DESCRIPTION:\nDTSTART:", $calendarIcs);

            $calendar = Reader::read($str, Reader::OPTION_FORGIVING);
            foreach ($calendar->children() as $event) {
                if ($event instanceof VEvent) {
                    if (!in_array((int)$event->UID->getValue(), [1064, 967])) {
                        $upcomingEvents[] = [
                            "title" => "[B]{$event->SUMMARY->getValue()}[/B]",
                            "link" => $event->URL->getValue(),
                            "date" => new \DateTimeImmutable($event->DTSTART->getValue()),
                        ];
                    }
                }
            }
        }
        usort($upcomingEvents, fn($a, $b) => $a["date"]->getTimestamp() <=> $b["date"]->getTimestamp());
        return $this->renderView("accountability/sectionRollCallTemplate.txt.twig", [
            "sectionPractice" => $sectionPractice,
            "section" => $section,
            "upcomingEvents" => $upcomingEvents,
            "chainOfCommand" => $this->chainOfCommandService->getForSection($section),
        ]);
    }
}
