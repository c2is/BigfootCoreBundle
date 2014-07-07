<?php

namespace Bigfoot\Bundle\CoreBundle\Event;

/**
 * Settings Event
 *
 * @package BigfootCoreBundle
 */
final class SettingsEvent
{
    const GENERATE = 'bigfoot_core.event.settings.generate';
    const COMPLETE = 'bigfoot_core.event.settings.complete';
}
