<?php

namespace Bigfoot\Bundle\CoreBundle\Controller;

interface AdminControllerInterface
{
    /**
     * @return string Route to be used as the homepage for this controller
     */
    public function getControllerIndex();

    /**
     * @return string Title to be used in the BackOffice for routes implemented by this controller
     */
    public function getControllerTitle();
}
