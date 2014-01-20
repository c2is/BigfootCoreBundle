<?php

namespace Bigfoot\Bundle\CoreBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;

abstract class AbstractMailer
{
    protected $entityManager;
    protected $mailer;
    protected $templating;
    protected $translator;
    protected $mailFrom;

    public function setManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    public function sendMail($subject, $to, $body)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->mailFrom)
            ->setTo($to)
            ->setContentType('text/html')
            ->setBody($body);

        $this->mailer->send($message);
    }
}
