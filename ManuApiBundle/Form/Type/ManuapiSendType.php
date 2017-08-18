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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ManuapiSendType.
 */
class ManuapiSendType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'manuapi',
            'manuapi_list',
            [
                'label'      => 'mautic.manuapi.send.selectmanuapi',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mautic.manuapi.choose.manuapis',
                    'onchange' => 'Mautic.disabledManuapiAction()',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        ['message' => 'mautic.manuapi.choosemanuapi.notblank']
                    ),
                ],
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'mautic_manuapi_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newManuapiButton',
                'button',
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'Mautic.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'fa fa-plus',
                    ],
                    'label' => 'mautic.manuapi.send.new.manuapi',
                ]
            );

            $manuapi = $options['data']['manuapi'];

            // create button edit manuapi
            $windowUrlEdit = $this->router->generate(
                'mautic_manuapi_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'manuapiId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editManuapiButton',
                'button',
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        //'onclick'  => 'Mautic.loadNewWindow(Mautic.standardManuapiUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
                        'onclick'  => 'Mautic.loadNewWindow(Mautic.standardSmsUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
                        'disabled' => !isset($manuapi),
                        'icon'     => 'fa fa-edit',
                    ],
                    'label' => 'mautic.manuapi.send.edit.manuapi',
                ]
            );
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(['update_select']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'manuapisend_list';
    }
}
