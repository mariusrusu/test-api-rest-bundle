<?php
namespace EveryCheck\TestApiRestBundle\Tests\sampleProject\src\EmailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Pattern controller.
 *
 * @Route("email")
 */
class EmailController extends Controller
{
    /**
     * Sends an email.
     *
     * @Route("", name="email_sends", methods={"POST"})
     */
    public function emailAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if($data["nbSend"] <= 0)
        {
            return new Response("No email has been sent", 201, ['Content-Type'=>"application/json"]);
        }

        for($i = 0; $i < $data["nbSend"]; $i++)
        {
            $this->get('email.send_email')->sendEmail();
        }

        return new Response($data['nbSend']." emails have been sent", 201, ['Content-Type'=>"application/json"]);
    }

    private function badRequest($message)
    {
        return new Response($message, 400, ['Content-Type'=>"application/json"]);
    }
}