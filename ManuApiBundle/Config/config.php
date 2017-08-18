<?php
/*
 * @copyright   use at your peril
 * @author      acmepong
 *
 * @link        http://eat.me.org
 *
 * description  config for ManuApi bundle
 */

//demonstrate routing configuration
return [
    'name' => 'ManuApi Plugin',
    'description' => 'ManuApi Plugin',
    'author'    => 'acmepong',
    'version'   => '1.0.0',
    
    
    'services' => [
        'models' => [            
            'mautic.manuapi.model.manuapi' => [
                'class' => 'MauticPlugin\ManuApiBundle\Model\ManuApiModel',                
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.lead.model.lead',
                    'mautic.channel.model.queue',
                    'mautic.manuapi.localhostapi',
                ],                
            ],
        ],
        
        'other' => [
            'mautic.manuapi.localhostapi' => [
                'class'     => 'MauticPlugin\ManuApiBundle\Api\LocalhostApi',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                    'mautic.factory',
                ],
                'alias' => 'localhost_api',
            ],
        ],
        
        /*
        'other' => [
            'mautic.sms.api' => [
                'class'     => 'Mautic\SmsBundle\Api\TwilioApi',
                'arguments' => [
                    'mautic.page.model.trackable',
                    'mautic.helper.phone_number',
                    'mautic.helper.integration',
                    'monolog.logger.mautic',
                ],
                'alias' => 'sms_api',
            ],
        ],
        
        */
        
        'events' => [
            'mautic.manuapi.campaignbundle.subscriber' => [
                'class'     => 'MauticPlugin\ManuApiBundle\EventListener\CampaignSubscriber',
                'arguments' => [
                    'mautic.helper.integration',
                    'mautic.manuapi.model.manuapi',
                ],
            ],
            'mautic.manuapi.manuapibundle.subscriber' => [
                'class'     => 'MauticPlugin\ManuApiBundle\EventListener\ManuapiSubscriber',
                'arguments' => [
                    'mautic.core.model.auditlog',
                    'mautic.page.model.trackable',
                    'mautic.page.helper.token',
                    'mautic.asset.helper.token',
                ],
            ],
            'mautic.manuapi.channel.subscriber' => [
                'class'     => \MauticPlugin\ManuApiBundle\EventListener\ChannelSubscriber::class,
                'arguments' => [
                    'mautic.helper.integration',
                ],
            ],
            'mautic.manuapi.message_queue.subscriber' => [
                'class'     => \MauticPlugin\ManuApiBundle\EventListener\MessageQueueSubscriber::class,
                'arguments' => [
                    'mautic.manuapi.model.manuapi',
                ],
            ],
            'mautic.manuapi.stats.subscriber' => [
                'class'     => \MauticPlugin\ManuApiBundle\EventListener\StatsSubscriber::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                ],
            ],
        ],
        
        'forms' => [
            'mautic.form.type.manuapi' => [
                'class'     => 'MauticPlugin\ManuApiBundle\Form\Type\ManuapiType',
                'arguments' => 'mautic.factory',
                'alias'     => 'manuapi',
            ],
            'mautic.form.type.manuapiconfig' => [
                'class' => 'MauticPlugin\ManuApiBundle\Form\Type\ConfigType',
                'alias' => 'manuapiconfig',
            ],
            'mautic.form.type.manuapisend_list' => [
                'class'     => 'MauticPlugin\ManuApiBundle\Form\Type\ManuapiSendType',
                'arguments' => 'router',
                'alias'     => 'manuapisend_list',
            ],
            'mautic.form.type.manuapi_list' => [
                'class' => 'MauticPlugin\ManuApiBundle\Form\Type\ManuapiListType',
                'alias' => 'manuapi_list',
            ],
        ],
        
    ],    
                
    'routes' => [
        'main' => [            
            'manuapi_index' => [            
                'path'       => '/manuapi/{page}',                
                'controller' => 'ManuApiBundle:Manuapi:index',                
            ],
            'mautic_manuapi_index' => [            
                'path'       => '/manuapi/{page}',                
                'controller' => 'ManuApiBundle:Manuapi:index',                
            ],
            //perhaps Helper class is adding mautic as prefix
            'mautic_manuapi_action' => [
              'path'        => '/manuapi/{objectAction}/{objectId}',
              'controller'  => 'ManuApiBundle:Manuapi:execute',
            ],
        ],
    ],
    
        'menu' => [
        'main' => [
            'items' => [
                'Manu APIs' => [
                    'route'  => 'manuapi_index',
                    //'access' => ['sms:smses:viewown', 'sms:smses:viewother'],
                    'parent' => 'mautic.core.channels',                    
                    /*
                    'checks' => [
                        'integration' => [
                            'ManuApi' => [
                                'enabled' => true,
                            ],
                        ],
                    ],
                    */
                    'priority' => 70,
                ],
            ],
        ],
    ],
    'parameters' => [
        'manuapi_enabled'   =>true,
        'manuapi_username'  => null,
        'manuapi_password'  => null,
        'manuapi_sending_phone_number' => null,
        'manuapi_frequency_number'     => null, 
        'manuapi_frequency_time'       => null,
    ],

];       