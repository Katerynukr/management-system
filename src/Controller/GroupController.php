<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Group;

class GroupController extends AbstractController
{
    /**
     * @Route("/group", name="group_index", methods= {"GET"})
     */
    public function index(Request $r): Response
    {
        $groups = $this->getDoctrine()
        ->getRepository(Group::class);

        if($r->query->get('sort_by') == 'sort_by_title_asc'){
            $groups = $groups->findBy([],['title' => 'asc']);
        }elseif($r->query->get('sort_by') == 'sort_by_title_desc'){
            $groups = $groups->findBy([],['title' => 'desc']);
        }else{
            $groups = $groups->findAll();
        }
        
        return $this->render('group/index.html.twig', [
            'groups' => $groups,
            'sortBy' => $r->query->get('sort_by') ?? 'default',
            //'success' => $r->getSession()->getFlashBag()->get('success', []),
            //'errors' => $r->getSession()->getFlashBag()->get('errors', [])
        ]);
    }

    
     /**
     * @Route("/group/create", name="group_create", methods= {"GET"})
     */
    public function create(Request $r): Response
    {
        $group_title = $r->getSession()->getFlashBag()->get('group_title', []);

        return $this->render('group/create.html.twig', [
        //    'errors' => $r->getSession()->getFlashBag()->get('errors', []),
            'group_title' => $group_title[0] ?? ''
        ]);
    }

     /**
     * @Route("/group/store", name="group_store", methods= {"POST"})
     */
    public function store(Request $r, ValidatorInterface $validator): Response
    {
        $submittedToken = $r->request->get('token');

         if (!$this->isCsrfTokenValid('group_hidden_index', $submittedToken)) {
        //     $r->getSession()->getFlashBag()->add('errors', 'Blogas Tokenas CSRF');
             return $this->redirectToRoute('author_create');
         }

        $group = new Group;
        $group->
        setTitle($r->request->get('group_title'));

       // $errors = $validator->validate($author);

        // dd(count($errors));
        // if (count($errors) > 0){
        //     foreach($errors as $error) {
        //         $r->getSession()->getFlashBag()->add('errors', $error->getMessage());
        //     }
        //     $r->getSession()->getFlashBag()->add('author_name', $r->request->get('author_name'));
        //     $r->getSession()->getFlashBag()->add('author_surname', $r->request->get('author_surname'));
        //     return $this->redirectToRoute('author_create');
        // }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($group);
        $entityManager->flush();

        //$r->getSession()->getFlashBag()->add('success', 'Author was successfully created');

        return $this->redirectToRoute('group_index');
    }

    /**
     * @Route("/group/delete/{id}", name="group_delete", methods= {"POST"})
     */
    public function delete(Request $r, int $id): Response
    {
       $submittedToken = $r->request->get('token');
        
       
        if (!$this->isCsrfTokenValid('group_hidden_index', $submittedToken)) {
            //$r->getSession()->getFlashBag()->add('errors', 'Blogas Tokenas CSRF');
            return $this->redirectToRoute('author_index');
        }

        $group = $this->getDoctrine()
        ->getRepository(Group::class)
        ->find($id);

        // if ($author->getBooks()->count() > 0) {
        //     $r->getSession()->getFlashBag()->add('errors', 'You cannot deleate the author because it has books' );
        //     return $this->redirectToRoute('author_index');
        // }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($group);
        $entityManager->flush();

        return $this->redirectToRoute('group_index');
    }
}
