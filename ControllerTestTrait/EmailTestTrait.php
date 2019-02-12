<?php
namespace EveryCheck\TestApiRestBundle\ControllerTestTrait;

trait EmailTestTrait
{
    protected function enableMailCatching()
    {
        $this->client->enableProfiler();
    }

    protected function collectEmailAndTestContent($mail,$pcre)
    {
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertLessThan($mailCollector->getMessageCount(),$mail,"cannot read mail ". $mail ." only " . $mailCollector->getMessageCount() . "mail sended");
        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];
        preg_match($pcre, $message->getBody(),$exctractedValue);
        $this->assertGreaterThan(0, count($exctractedValue));
        foreach ($exctractedValue as $key => $value) {
            $this->env['pcre'.$key] = $value;
        }
    }

    protected function assertMailSendedCount($count)
    {
        $mailCollector = $this->client->getProfile()->getCollector('swiftmailer');
        $this->assertEquals($count, $mailCollector->getMessageCount(),"failed to expecting $count mails got ". $mailCollector->getMessageCount());
    }
}