<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle;

/**
 * Class ManuApiEvents
 * Events available for ManuApiBundle.
 */
final class ManuapiEvents
{
    /**
     * The mautic.manuapi_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * Mautic\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    const TOKEN_REPLACEMENT = 'mautic.manuapi_token_replacement';

    /**
     * The mautic.manuapi_on_send event is thrown when a manuapi is sent.
     *
     * The event listener receives a
     * MauticPlugin\ManuApiBundle\Event\ManuapiSendEvent instance.
     *
     * @var string
     */
    const MANUAPI_ON_SEND = 'mautic.manuapi_on_send';

    /**
     * The mautic.sms_pre_save event is thrown right before a manuapi is persisted.
     *
     * The event listener receives a
     * MauticPlugin\ManuApiBundle\Event\ManuapiEvent instance.
     *
     * @var string
     */
    const MANUAPI_PRE_SAVE = 'mautic.manuapi_pre_save';

    /**
     * The mautic.manuapi_post_save event is thrown right after a manuapi is persisted.
     *
     * The event listener receives a
     * MauticPlugin\ManuApiBundle\Event\ManuapiEvent instance.
     *
     * @var string
     */
    const MANUAPI_POST_SAVE = 'mautic.manuapi_post_save';

    /**
     * The mautic.manuapi_pre_delete event is thrown prior to when a manuapi is deleted.
     *
     * The event listener receives a
     * MauticPlugin\MauApiBundle\Event\ManuapiEvent instance.
     *
     * @var string
     */
    const MANUAPI_PRE_DELETE = 'mautic.manuapi_pre_delete';

    /**
     * The mautic.sms_post_delete event is thrown after a manuapi is deleted.
     *
     * The event listener receives a
     * MauticPlugin\ManuApiBundle\Event\ManuapiEvent instance.
     *
     * @var string
     */
    const MANUAPI_POST_DELETE = 'mautic.manuapi_post_delete';

    /**
     * The mautic.sms.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * Mautic\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    const ON_CAMPAIGN_TRIGGER_ACTION = 'mautic.manuapi.on_campaign_trigger_action';
}
