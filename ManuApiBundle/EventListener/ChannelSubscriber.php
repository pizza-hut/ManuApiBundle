<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\EventListener;

use Mautic\ChannelBundle\ChannelEvents;
use Mautic\ChannelBundle\Event\ChannelEvent;
use Mautic\ChannelBundle\Model\MessageModel;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\ReportBundle\Model\ReportModel;

/**
 * Class ChannelSubscriber.
 */
class ChannelSubscriber extends CommonSubscriber
{
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * ChannelSubscriber constructor.
     *
     * @param IntegrationHelper $integrationHelper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 90],
        ];
    }

    /**
     * @param ChannelEvent $event
     */
    public function onAddChannel(ChannelEvent $event)
    {
        //$integration = $this->integrationHelper->getIntegrationObject('Twilio');
        $integration = $this->integrationHelper->getIntegrationObject('ManuApi');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $event->addChannel(
                'manuapi',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'manuapi.send_text_manuapi',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => 'manuapi_list',
                        'repository'     => 'ManuApiBundle:ManuApi',
                    ],
                    LeadModel::CHANNEL_FEATURE   => [],
                    ReportModel::CHANNEL_FEATURE => [
                        'table' => 'manuapi_messages',
                    ],
                ]
            );
        }
    }
}
