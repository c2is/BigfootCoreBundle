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

    protected function setManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function getManager()
    {
        return $this->entityManager;
    }

    protected function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    protected function getMailer()
    {
        return $this->mailer;
    }

    protected function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    protected function getTemplating()
    {
        return $this->templating;
    }

    protected function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    protected function getTranslator()
    {
        return $this->translator;
    }

    protected function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    protected function getMailFrom()
    {
        return $this->mailFrom;
    }

    protected function sendMail($subject, $to, $body)
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
