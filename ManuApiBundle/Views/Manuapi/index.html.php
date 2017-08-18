<?php
$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('mauticContent', 'manuapi');
$view['slots']->set('headerTitle', $view['translator']->trans('mautic.manuapi.manuapis'));

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                //'new' => $permissions['sms:smses:create'],
                'new' => $permissions['manuapi:manuapis:create'],
            ],
            'routeBase' => 'manuapi',
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'mautic.manuapi.help.searchcommands',
            'searchId'    => 'manuapi-search',
            'action'      => $currentRoute,
            // 'filters'     => $filters // @todo
        ]
    ); ?>    

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>        
    </div>
</div>