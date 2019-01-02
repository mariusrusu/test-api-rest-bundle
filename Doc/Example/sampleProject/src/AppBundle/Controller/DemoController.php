<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\Demo;
use AppBundle\Form\DemoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
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
     * @Route("", name="demo_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $demos = $em->getRepository('AppBundle:Demo')->findAll();
        $response = $serializer->serialize($demos, 'json');

        return new Response($response, 200);
    }

    /**
     * Creates a new demo entity.
     *
     * @Route("/new", name="demo_new")
     * @Method({"POST"})
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

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($demo, 'json');
        return new Response($response, 201);
    }

    /**
     * Deletes a demo entity.
     *
     * @Route("/{id}", name="demo_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $demo = $em->getRepository('AppBundle\Entity\Demo')->find($request->get('id'));


        if(empty($demo))
        {
            return $this->notFound();
        }

        $em->remove($demo);
        $em->flush();
        return new Response('', 204);
    }

    private function notFound()
    {
        return new Response("The resource you asked doesn't exist", 404);
    }

    private function badRequest($message)
    {
        return new Response($message, 400);
    }
}
