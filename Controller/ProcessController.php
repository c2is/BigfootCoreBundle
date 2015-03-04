<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Entity\Process;
use Bigfoot\Bundle\CoreBundle\Entity\ProcessRepository;
use JMS\Serializer\SerializerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/process")
 */
class ProcessController extends BaseController
{
    /**
     * @Route("/widget", name="bigfoot_process_widget")
     */
    public function widgetAction()
    {
        $processRepo = $this->getProcessRepository();
        $processes = $processRepo->findAllOngoing();

        return $this->render($this->getThemeBundle().':process:widget.html.twig', array('processes' => $processes, 'count' => count($processes)));
    }

    /**
     * @Route("/progress/list", name="bigfoot_process_list", options={"expose" = true})
     */
    public function listAction()
    {
        $processRepo = $this->getProcessRepository();
        $processes = $processRepo->findAllOngoing();
        $result = array();

        /** @var Process $process */
        foreach ($processes as $process) {
            $result[] = $process->getToken();
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/progress/{token}", name="bigfoot_process_progress", options={"expose" = true})
     * @ParamConverter("process", class="BigfootCoreBundle:Process", options={"token" = "token"})
     */
    public function progressAction($process)
    {
        $serializer = SerializerBuilder::create()->build();

        return new Response($serializer->serialize($process, 'json'), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @return ProcessRepository
     */
    private function getProcessRepository()
    {
        return $this->getRepository('BigfootCoreBundle:Process');
    }
}
