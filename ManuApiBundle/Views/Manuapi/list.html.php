<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ($tmpl == 'index') {
    $view->extend('ManuApiBundle:Manuapi:index.html.php');
}

    
if (count($items)):

    ?>
    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered sms-list">
            <thead>
            <tr>
                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'checkall'        => 'true',
                        'routeBase'       => 'manuapi',
                        'templateButtons' => [
                            'delete' => $permissions['manuapi:manuapis:deleteown'] || $permissions['manuapi:manuapis:deleteother'],
                        ],
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'manuapi',
                        'orderBy'    => 'e.name',
                        'text'       => 'mautic.core.name',
                        'class'      => 'col-sms-name',
                        'default'    => true,
                    ]
                );

                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'manuapi',
                        'orderBy'    => 'c.title',
                        'text'       => 'mautic.core.category',
                        'class'      => 'visible-md visible-lg col-sms-category',
                    ]
                );
                ?>

                <th class="visible-sm visible-md visible-lg col-sms-stats"><?php echo $view['translator']->trans('mautic.core.stats'); ?></th>

                <?php
                echo $view->render(
                    'MauticCoreBundle:Helper:tableheader.html.php',
                    [
                        'sessionVar' => 'manuapi',
                        'orderBy'    => 'e.id',
                        'text'       => 'mautic.core.id',
                        'class'      => 'visible-md visible-lg col-sms-id',
                    ]
                );
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            /** @var \MauticPlugin\ManuApiBundle\Entity\ManuApi $item */
            foreach ($items as $item):
                $type = $item->getManuapiType();
                ?>
                <tr>
                    <td>
                        <?php
                        $edit = $view['security']->hasEntityAccess(
                            $permissions['manuapi:manuapis:editown'],
                            $permissions['manuapi:manuapis:editother'],
                            $item->getCreatedBy()
                        );
                        $customButtons = [
                            [
                                'attr' => [
                                    'data-toggle' => 'ajaxmodal',
                                    'data-target' => '#MauticSharedModal',
                                    'data-header' => $view['translator']->trans('mautic.manuapi.manuapis.header.preview'),
                                    'data-footer' => 'false',
                                    'href'        => $view['router']->path(
                                        //'mautic_sms_action',
                                        'manuapi_index',
                                        ['objectId' => $item->getId(), 'objectAction' => 'preview']
                                    ),
                                ],
                                'btnText'   => $view['translator']->trans('mautic.manuapi.preview'),
                                'iconClass' => 'fa fa-share',
                            ],
                        ];
                        echo $view->render(
                            'MauticCoreBundle:Helper:list_actions.html.php',
                            [
                                'item'            => $item,
                                'templateButtons' => [
                                    'edit' => $view['security']->hasEntityAccess(
                                        $permissions['manuapi:manuapis:editown'],
                                        $permissions['manuapi:manuapis:editother'],
                                        $item->getCreatedBy()
                                    ),
                                    'clone'  => $permissions['manuapi:manuapis:create'],
                                    'delete' => $view['security']->hasEntityAccess(
                                        $permissions['manuapi:manuapis:deleteown'],
                                        $permissions['manuapi:manuapis:deleteother'],
                                        $item->getCreatedBy()
                                    ),
                                ],
                                'routeBase'     => 'manuapi',
                                'customButtons' => $customButtons,
                            ]
                        );
                        ?>                        
                    </td>
                    <td>
                        <div>
                            <?php if ($type == 'template'): ?>
                                <?php echo $view->render(
                                    'MauticCoreBundle:Helper:publishstatus_icon.html.php',
                                    ['item' => $item, 'model' => 'manuapi']
                                ); ?>
                            <?php else: ?>
                                <i class="fa fa-fw fa-lg fa-toggle-on text-muted disabled"></i>
                            <?php endif; ?>
                            <a href="<?php echo $view['router']->path(
                                //'mautic_sms_action',
                                //'manuapi_index',
                                'mautic_manuapi_action',
                                ['objectAction' => 'view', 'objectId' => $item->getId()]
                            ); ?>">
                                <?php echo $item->getName(); ?>
                                <?php if ($type == 'list'): ?>
                                    <span data-toggle="tooltip" title="<?php echo $view['translator']->trans('mautic.manupi.icon_tooltip.list_manuapi'); ?>"><i class="fa fa-fw fa-list"></i></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </td>
                    <td class="visible-md visible-lg">
                        <?php $category = $item->getCategory(); ?>
                        <?php $catName  = ($category) ? $category->getTitle() : $view['translator']->trans('mautic.core.form.uncategorized'); ?>
                        <?php $color    = ($category) ? '#'.$category->getColor() : 'inherit'; ?>
                        <span style="white-space: nowrap;"><span class="label label-default pa-4" style="border: 1px solid #d5d5d5; background: <?php echo $color; ?>;"> </span> <span><?php echo $catName; ?></span></span>
                    </td>
                    <td class="visible-sm visible-md visible-lg col-stats">
                        <span class="mt-xs label label-warning"><?php echo $view['translator']->trans(
                                'mautic.sms.stat.sentcount',
                                ['%count%' => $item->getSentCount(true)]
                            ); ?></span>
                    </td>
                    <td class="visible-md visible-lg"><?php echo $item->getId(); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="panel-footer">
        <?php echo $view->render(
            'MauticCoreBundle:Helper:pagination.html.php',
            [
                'totalItems' => $totalItems,
                'page'       => $page,
                'limit'      => $limit,
                'baseUrl'    => $view['router']->path('manuapi_index'),
                'sessionVar' => 'manuapi',
            ]
        ); ?>
    </div>
<?php elseif (!$configured): ?>
    <?php echo $view->render(
        'MauticCoreBundle:Helper:noresults.html.php',
        ['header' => 'mautic.manuapi.disabled', 'message' => 'mautic.manuapi.enable.in.configuration']
    ); ?>
<?php else: ?>
    <?php echo $view->render('MauticCoreBundle:Helper:noresults.html.php', ['message' => 'mautic.manuapi.create.in.campaign.builder']); ?>
<?php endif; ?>
