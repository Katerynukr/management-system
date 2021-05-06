<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Entity\Group;

class UserGroupsController extends AbstractController
{
    /**
     * @Route("/user/groups", name="user_groups_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $groups =  $this->getDoctrine()
        ->getRepository(Group::class)
        ->findAll();
        
        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();

        return $this->render('user_groups/index.html.twig', [
            'groups' => $groups,
            'users'=>$users,
        ]);
    }

    /**
     * @Route("/user/groups/delete/{id}", name="user_groups_delete", methods= {"POST"})
     */
    public function delete(Request $r, int $id): Response
    {
        $submittedToken = $r->request->get('token');

        if (!$this->isCsrfTokenValid('user_group_hidden', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad Token CSRF');
            return $this->redirectToRoute('user_groups_index');
        }

        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);

        $group = $this->getDoctrine()
        ->getRepository(Group::class)
        ->find($r->request->get('group_id'));

        $user->
        removeGrade($group); 

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('user_groups_index');
    }
}
