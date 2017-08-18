<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Api;

use Joomla\Http\Http;
use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PageBundle\Model\TrackableModel;
use Mautic\LeadBundle\Entity\Company;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Helper\IdentifyCompanyHelper;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\UserBundle\Entity\User;
use MauticPlugin\ManuApiBundle\Integration\ManuApiIntegration;
//use Mautic\PluginBundle\Integration\AbstractIntegration;

abstract class AbstractManuapi
{
    /**
     * @var MauticFactory
     */
    protected $pageTrackableModel;
    
    protected $integration;

    /**
     * AbstractSmsApi constructor.
     *
     * @param TrackableModel $pageTrackableModel
     */
    public function __construct(TrackableModel $pageTrackableModel, ManuApiIntegration $integration)
    {
        $this->pageTrackableModel = $pageTrackableModel;
        $this->integration        = $integration;
    }

    /**
     * @param string $number
     * @param string $content
     *
     * @return mixed
     */
    abstract public function sendManuapi($number, $content);

    /**
     * Convert a non-tracked url to a tracked url.
     *
     * @param string $url
     * @param array  $clickthrough
     *
     * @return string
     */
    public function convertToTrackedUrl($url, array $clickthrough = [])
    {
        /* @var \Mautic\PageBundle\Entity\Redirect $redirect */
        $trackable = $this->pageTrackableModel->getTrackableByUrl($url, 'manuapi', $clickthrough['manuapi']);

        return $this->pageTrackableModel->generateTrackableUrl($trackable, $clickthrough, true);
    }
}
