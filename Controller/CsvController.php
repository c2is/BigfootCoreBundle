<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Entity\QuickLink;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * QuickLink Controller
 * @Route("/csv")
 */
class CsvController extends Controller
{
    /**
     * Generate Widget
     *
     * @Route("/generate/{entity}/{fields}", name="admin_csv_generate")
     */
    public function generateAction($entity, $fields)
    {
        $entity   = base64_decode($entity);
        $fields   = unserialize(base64_decode(str_replace('+', '/', $fields)));
        $csvArray = $this->get('bigfoot_core.manager.csv')->generateCsv($entity, $fields);

        return $csvArray;
    }
}
