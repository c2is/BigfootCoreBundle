<?php

namespace Bigfoot\Bundle\CoreBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;

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

    public function getManager()
    {
        return $this->entityManager;
    }

    public function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function getMailer()
    {
        return $this->mailer;
    }

    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    public function getTemplating()
    {
        return $this->templating;
    }

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    public function getMailFrom()
    {
        return $this->mailFrom;
    }

    public function sendMail($subject, $to, $body, $cc = null, $bcc = null)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->mailFrom)
            ->setTo($to)
            ->setContentType('text/html')
            ->setBody($body);

        if ($cc) {
            $message->setCc($cc);
        }

        if ($bcc) {
            $message->setBcc($bcc);
        }

        $this->mailer->send($message);
    }
}
