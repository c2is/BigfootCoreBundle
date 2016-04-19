<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

use Bigfoot\Bundle\CoreBundle\Form\Type\SettingsType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

use Bigfoot\Bundle\CoreBundle\Entity\Settings;

/**
 * Settings Controller.
 *
 * @Cache(maxage="0", smaxage="0", public="false")
 * @Route("/settings")
 *
 * @package BigfootCoreController
 */
class SettingsController extends BaseController
{
    /**
     * Globale settings action
     *
     * @param Request $request
     *
     * @Route("/global", name="admin_settings_global")
     * @Template("BigfootCoreBundle:settings:form.html.twig")
     * @method({"GET", "POST"})
     *
     * @return array
     */
    public function globalAction(Request $request)
    {
        $settings = $this->getRepository('BigfootCoreBundle:Settings')->findAll();
        $settings = !empty($settings) ? current($settings) : null;

        $form = $this->createForm(SettingsType::class, !empty($settings) ? $settings->getSettings() : null);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            $datas = $form->getData();

            $settings = !empty($settings) ? $settings : new Settings();
            $settings->setSettings($datas);

            $this->persistAndFlush($settings);
        }

        return array(
            'form' => $form->createView()
        );
    }
}
