<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Controller;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity\ParentEntity;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Form\ParentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * ParentEntity controller.
 *
 */
class ParentController extends Controller
{
    /**
     * Lists all parent entities.
     *
     * @Route("parents", name="parent_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $demos = $em->getRepository(ParentEntity::class)->findAll();


        $response = $serializer->serialize($demos, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }

    /**
     * Creates a new parent entity.
     *
     * @Route("parent/new", name="parent_new", methods={"POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $parent = new ParentEntity();
        $form = $this->createForm(ParentType::class, $parent);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->badRequest("Invalid form");
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($parent);
        $em->flush();

        $encoders = array( new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($parent, 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }


    /**
     * Deletes a parent entity.
     *
     * @Route("parents/{id}", name="parent_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $parent = $em->getRepository(ParentEntity::class)->find($id);


        if(empty($parent))
        {
            return $this->notFound();
        }

        $em->remove($parent);
        $em->flush();
        return new Response('', 204, ['Content-Type'=>"application/json"]);
    }

    private function notFound()
    {
        return new Response("The resource you asked doesn't exist", 404, ['Content-Type'=>"application/json"]);
    }

    private function badRequest($message)
    {
        return new Response($message, 400, ['Content-Type'=>"application/json"]);
    }
}
