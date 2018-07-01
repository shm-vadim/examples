<?php

namespace App\Controller;

use App\Entity\{Session, Ip};
use App\Form\SessionType;
use App\Repository\{
SessionRepository, 
IpRepository,
VisitRepository as VR,
};
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/session")
 */
class SessionController extends Controller
{
    /**
     * @Route("/", name="session_index", methods="GET")
     */
    public function index(SessionRepository $sessionRepository, IpRepository $ipR): Response
    {
$sessions=$sessionRepository->findAll();
foreach ($sessions as $s) {
$ipR->hasOrCreateByIp($s->getSid());
}

        return $this->render('session/index.html.twig', [
'sessions' =>$sessions, 
"ipR"=>$ipR,
]);
    }

    /**
     * @Route("/new", name="session_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($session);
            $em->flush();

            return $this->redirectToRoute('session_index');
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="session_show", methods="GET")
     */
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', ['session' => $session]);
    }

    /**
     * @Route("/{id}/edit", name="session_edit", methods="GET|POST")
     */
    public function edit(Request $request, Session $session): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('session_edit', ['id' => $session->getId()]);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="session_delete", methods="DELETE")
     */
    public function delete(Request $request, Session $session): Response
    {
        if ($this->isCsrfTokenValid('delete'.$session->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($session);
            $em->flush();
        }

        return $this->redirectToRoute('session_index');
    }

    /**
     * @Route("/{id}/visits", name="session_visits", methods="GET")
     */
public function visits(Session $s, VR $vR) {
return $this->render("session/visits.html.twig", [
"visits"=>$vR->findBySession($s),
]);
}
}
