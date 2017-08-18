<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use MauticPlugin\ManuApiBundle\Entity\Manuapi;

/**
 * Class ManuapiEvent.
 */
class ManuapiEvent extends CommonEvent
{
    /**
     * @param Sms  $sms
     * @param bool $isNew
     */
    public function __construct(Manuapi $manuapi, $isNew = false)
    {
        $this->entity = $manuapi;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Manuapi entity.
     *
     * @return Manuapi
     */
    public function getManuapi()
    {
        return $this->entity;
    }

    /**
     * Sets the Manuapi entity.
     *
     * @param Manuapi $manuapi
     */
    public function setManuapi(Manuapi $manuapi)
    {
        $this->entity = $manuapi;
    }
}
