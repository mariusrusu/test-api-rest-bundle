<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Controller;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Entity\Demo;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\DemoBundle\Form\DemoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Demo controller.
 *
 * @Route("demo")
 */
class DemoController extends Controller
{
    /**
     * Lists all demo entities.
     *
     * @Route("", name="demo_index", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $demos = $em->getRepository(Demo::class)->findAll();


        $response = $serializer->serialize($demos, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }

    /**
     * Creates a new demo entity.
     *
     * @Route("/new", name="demo_new", methods={"POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $demo = new Demo();
        $form = $this->createForm(DemoType::class, $demo);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->badRequest("Invalid form");
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($demo);
        $em->flush();

        $encoders = array( new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($demo, 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }


    /**
     * Creates a new demo entity via multipart form.
     *
     * @Route("/multipart", name="demo_multipart", methods={"POST"})
     */
    public function multipartAction(Request $request)
    {
        $encoders = array( new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($request->request->all(), 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }

    /**
     * Deletes a demo entity.
     *
     * @Route("/{id}", name="demo_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $demo = $em->getRepository(Demo::class)->find($id);


        if(empty($demo))
        {
            return $this->notFound();
        }

        $em->remove($demo);
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
