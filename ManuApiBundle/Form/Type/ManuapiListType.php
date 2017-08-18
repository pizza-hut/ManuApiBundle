<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\EntityLookupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ManuapiListType.
 */
class ManuapiListType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'modal_route'         => 'mautic_manuapi_action',
                'modal_header'        => 'mautic.manuapi.header.new',
                'model'               => 'manuapi',
                'model_lookup_method' => 'getLookupResults',
                'lookup_arguments'    => function (Options $options) {
                    return [
                        'type'    => 'manuapi',
                        'filter'  => '$data',
                        'limit'   => 0,
                        'start'   => 0,
                        'options' => [
                            'manuapi_type' => $options['manuapi_type'],
                        ],
                    ];
                },
                'ajax_lookup_action' => function (Options $options) {
                    $query = [
                        'manuapi_type' => $options['manuapi_type'],
                    ];

                    return 'manuapi:getLookupChoiceList&'.http_build_query($query);
                },
                'multiple' => true,
                'required' => false,
                'manuapi_type' => 'template',
            ]
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'manuapi_list';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return EntityLookupType::class;
    }
}
