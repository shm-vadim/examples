<?php

namespace App\Controller;

use App\Repository\SessionRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\ProfileRepository as PR;

class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(\App\Service\UserLoader $ul, \App\Repository\UserRepository $uR)
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
