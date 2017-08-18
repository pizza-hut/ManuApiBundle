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

use Mautic\CampaignBundle\CampaignEvents;
use Mautic\CampaignBundle\Event\CampaignBuilderEvent;
use Mautic\CampaignBundle\Event\CampaignExecutionEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use MauticPlugin\ManuApiBundle\Model\ManuApiModel;
use MauticPlugin\ManuApiBundle\ManuapiEvents;

/**
 * Class CampaignSubscriber.
 */
class CampaignSubscriber extends CommonSubscriber
{
    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var ManuapiModel
     */
    protected $manuapiModel;

    /**
     * CampaignSubscriber constructor.
     *
     * @param IntegrationHelper $integrationHelper
     * @param ManuApiModel          $manuapiModel
     */
    public function __construct(
        IntegrationHelper $integrationHelper,
        ManuApiModel $manuapiModel
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->manuapiModel          = $manuapiModel;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD     => ['onCampaignBuild', 0],
            ManuapiEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    /**
     * @param CampaignBuilderEvent $event
     */
    public function onCampaignBuild(CampaignBuilderEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('ManuApi');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $event->addAction(
                //'sms.send_text_sms',
                'manuapi.send_text_manuapi',
                [
                    'label'            => 'mautic.campaign.manuapi.send_text_manuapi',
                    'description'      => 'mautic.campaign.manuapi.send_text_manuapi.tooltip',
                    'eventName'        => ManuapiEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                    'formType'         => 'manuapisend_list',
                    'formTypeOptions'  => ['update_select' => 'campaignevent_properties_manuapi'],
                    'formTheme'        => 'MauticManuApiBundle:FormTheme\ManuapiSendList',
                    'timelineTemplate' => 'MauticManuApiBundle:SubscribedEvents\Timeline:index.html.php',
                    'channel'          => 'manuapi',
                    'channelIdField'   => 'manuapi',
                ]
            );
        }
    }

    /**
     * @param CampaignExecutionEvent $event
     *
     * @return mixed
     */
    public function onCampaignTriggerAction(CampaignExecutionEvent $event)
    {
        $lead  = $event->getLead();
        $manuapiId = (int) $event->getConfig()['manuapi'];
        $manuapi   = $this->manuapiModel->getEntity($manuapiId);

        if (!$manuapi) {
            return $event->setFailed('mautic.manuapi.campaign.failed.missing_entity');
        }

        $result = $this->manuapiModel->sendManuapi($manuapi, $lead, ['channel' => ['campaign.event', $event->getEvent()['id']]])[$lead->getId()];

        if ('Authenticate' === $result['status']) {
            // Don't fail the event but reschedule it for later
            return $event->setResult(false);
        }

        if (!empty($result['sent'])) {
            $event->setChannel('manuapi', $manuapi->getId());
            $event->setResult($result);
        } else {
            $result['failed'] = true;
            $result['reason'] = $result['status'];
            $event->setResult($result);
        }
    }
}
