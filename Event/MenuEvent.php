<?php

namespace Bigfoot\Bundle\CoreBundle\Event;

/**
 * Menu Event
 */
final class MenuEvent
{
    const GENERATE_MAIN = 'bigfoot_core.event.menu.generate_main';
    const TERMINATE     = 'bigfoot_core.event.menu.terminate';
    const RENDER_MENU   = 'bigfoot_core.event.menu.render_menu';
}
