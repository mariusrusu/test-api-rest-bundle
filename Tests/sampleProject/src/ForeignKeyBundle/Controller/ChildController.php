<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Controller;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Entity\ChildEntity;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\ForeignKeyBundle\Form\ChildType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * ChildEntity controller.
 *
 */
class ChildController extends Controller
{
    /**
     * Lists all parent entities.
     *
     * @Route("children", name="children_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $children = $em->getRepository(ChildEntity::class)->findAll();


        $response = $serializer->serialize($children, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }

    /**
     * Creates a new child entity.
     *
     * @Route("child/new", name="child_new", methods={"POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $child = new ChildEntity();
        $form = $this->createForm(ChildType::class, $child);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->badRequest("Invalid form");
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($child);
        $em->flush();

        $encoders = array( new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($child, 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }


    /**
     * Deletes a children entity.
     *
     * @Route("children/{id}", name="children_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $child = $em->getRepository(ChildEntity::class)->find($id);

        if(empty($child))
        {
            return $this->notFound();
        }

        $em->remove($child);
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
