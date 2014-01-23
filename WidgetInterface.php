<?php
/**
 * Created by PhpStorm.
 * User: splancon
 * Date: 21/01/14
 * Time: 18:00
 */

namespace Bigfoot\Bundle\CoreBundle;

interface WidgetInterface
{
    public function __construct($container);

    public function render();
}