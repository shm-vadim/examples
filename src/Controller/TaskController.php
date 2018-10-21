<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Settings;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserLoader;

/**
 * @Route("/task")
 */
class TaskController extends AbstractController
{
    /**
     * @Route("/", name="task_index", methods="GET")
     */
    public function index(TaskRepository $taskRepository) : Response
    {
        return $this->render('task/index.html.twig', [
            'tasks' => $taskRepository->findAll(),
            'taskRepository' => $taskRepository,
        ]);
    }

    /**
     * @Route("/new", name="task_new", methods="GET|POST")
     */
    public function new(Request $request, UserLoader $userLoader, ProfileRepository $profileRepository, UserRepository $userRepository) : Response
    {
        $currentUser = $userLoader->getUser()
            ->setEntityRepository($userRepository);
        $task = (new Task())
            ->setAuthor($currentUser)
            ->setContractors($currentUser->getStudents())
            ->setLimitTime((new \DT())->add(new \DateInterval('P3D')));
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $profile = $profileRepository->find($request->request->get('profile_id', ''));

            if (!$profile) {
                throw $this->createNotFoundException();
            }

            if (!$this->isGranted('appoint', $profile)) {
                throw $this->createAccessDenyedException();
            }

            if ($form->isValid()) {
                $settings = new Settings();
                Settings::copySettings($profile, $settings);
                $task->setSettings($settings);

                $em = $this->getDoctrine()->getManager();
                $em->persist($settings);
                $em->persist($task);
                $em->flush();

                return $this->redirectToRoute('task_index');
            }
        }

        return $this->render('task/new.html.twig', [
            'jsParams' => [
                'current' => $currentUser->getCurrentProfile()->getId(),
            ],
            'task' => $task,
            'form' => $form->createView(),
            'publicProfiles' => $profileRepository->findByIsPublic(true),
            'profiles' => $profileRepository->findByAuthor($currentUser),
            'profileRepository' => $profileRepository,
        ]);
    }

    /**
     * @Route("/{id}", name="task_show", methods="GET")
     */
    public function show(Task $task) : Response
    {
        return $this->render('task/show.html.twig', ['task' => $task]);
    }

    /**
     * @Route("/{id}/edit", name="task_edit", methods="GET|POST")
     */
    public function edit(Request $request, Task $task) : Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('task_edit', ['id' => $task->getId()]);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="task_delete", methods="DELETE")
     */
    public function delete(Request $request, Task $task) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $task->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($task);
            $em->flush();
        }

        return $this->redirectToRoute('task_index');
    }
}
