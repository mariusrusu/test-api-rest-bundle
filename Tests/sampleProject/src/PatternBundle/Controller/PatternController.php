<?php

namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Controller;

use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Entity\Pattern;
use EveryCheck\TestApiRestBundle\Tests\sampleProject\src\PatternBundle\Form\PatternType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Pattern controller.
 *
 * @Route("pattern")
 */
class PatternController extends Controller
{
    /**
     * Lists all pattern entities.
     *
     * @Route("", name="pattern_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $patterns = $em->getRepository(Pattern::class)->findAll();


        $response = $serializer->serialize($patterns, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }

    /**
     * Creates a new pattern entity.
     *
     * @Route("/new", name="pattern_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $pattern = new Pattern();
        $form = $this->createForm(PatternType::class, $pattern);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->badRequest("Invalid form");
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($pattern);
        $em->flush();

        $encoders = array( new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($pattern, 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }

    /**
     * Deletes a pattern entity.
     *
     * @Route("/{id}", name="pattern_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $pattern = $em->getRepository(Pattern::class)->find($request->get('id'));


        if(empty($pattern))
        {
            return $this->notFound();
        }

        $em->remove($pattern);
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
