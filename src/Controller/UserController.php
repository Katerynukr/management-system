<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Entity\Group;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_index", methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $users = $this->getDoctrine()
        ->getRepository(User::class)
        ->findAll();

        return $this->render('user/index.html.twig', [
            'users'=>$users,
            'success' => $r->getSession()->getFlashBag()->get('success', []),
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/user/create", name="user_create", methods={"GET"})
     */
    public function create(Request $r): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user_name = $r->getSession()->getFlashBag()->get('user_name', []);

        $groups = $this->getDoctrine()
        ->getRepository(Group::class)
        ->findAll();
        
        return $this->render('user/create.html.twig', [
            'groups' => $groups,
            'user_name' => $user_name[0] ?? '',
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    /**
     * @Route("/user/create", name="user_store", methods={"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $submittedToken = $r->request->get('token');
        
        if (!$this->isCsrfTokenValid('user_hidden', $submittedToken)) {
             $r->getSession()->getFlashBag()->add('errors', 'Bad Token CSRF');
             return $this->redirectToRoute('user_create');
        }

        $user = new User;
        $user->
        setName($r->request->get('user_name'));

        $errors = $validator->validate($user);
        if (count($errors) > 0){
            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            $r->getSession()->getFlashBag()->add('user_name', $r->request->get('user_name'));
            return $this->redirectToRoute('user_create');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'User was successfully created');

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete", methods= {"POST"})
     */
    public function delete(Request $r, int $id): Response
    {
        $submittedToken = $r->request->get('token');

        if (!$this->isCsrfTokenValid('user_hidden', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad Token CSRF');
            return $this->redirectToRoute('user_index');
        }

        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'User was successfully deleted');

        return $this->redirectToRoute('user_index');
    }

     /**
     * @Route("/user/edit/{id}", name="user_edit_group", methods= {"GET"})
     */
    public function edit(Request $r, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);

        $groups = $this->getDoctrine()
        ->getRepository(Group::class)
        ->findAll();
        
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'groups' => $groups,
            'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

     /**
     * @Route("/user/update/{id}", name="user_update_group", methods= {"POST"})
     */
    public function update(Request $r, int $id,  ValidatorInterface $validator): Response
    {
        $user = $this->getDoctrine()
        ->getRepository(User::class)
        ->find($id);

        $submittedToken = $r->request->get('token');
        if (!$this->isCsrfTokenValid('user_hidden', $submittedToken)) {
            $r->getSession()->getFlashBag()->add('errors', 'Bad Token CSRF');
            return $this->redirectToRoute('user_edit_group',  ['id'=>$user->getId()] );
        }
        
        $group = $this->getDoctrine()
        ->getRepository(Group::class)
        ->find($r->request->get('user_group'));
        
        $groups = $user->getGroup();

        if($groups->contains($group)){ 
             $r->getSession()->getFlashBag()->add('errors', 'User was already added to this group');
             return $this->redirectToRoute('user_index');
        }

        $user->
        addGroup($group);

        $errors = $validator->validate($user);
        if (count($errors) > 0){
            foreach($errors as $error) {
                $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
            }
            return $this->redirectToRoute('user_edit', ['id'=>$book->getId()] );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $r->getSession()->getFlashBag()->add('success', 'User was successfully added to the group');

        return $this->redirectToRoute('user_index');
    }
}
