<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/")]
class HomeController extends AbstractController
{

    public function __construct()
    {
    }

    #[Route("/", name: "app_home_index", methods: ["GET"])]
    public function index(MailerInterface $mailer, Request $request): Response
    {
        // TODO build billet specific dashboards depending on the logged in user once we have SSO
        return $this->render("home/index.html.twig", []);
    }
}
