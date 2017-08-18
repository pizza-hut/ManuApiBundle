<?php
/*
 * @copyright   use at your peril
 * @author      acmepong
 *
 * @link        http://eat.me.org
 *
 * description  config for MyDemo bundle
 */

/*
* plugins/ManuApiBundle/Controller/ManuApiController.php
*/ 
namespace MauticPlugin\ManuApiBundle\Controller;
use Mautic\CoreBundle\Controller\FormController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Mautic\CoreBundle\Helper\InputHelper;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Controller\EntityContactsTrait;
use MauticPlugin\ManuApiBundle\Model\ManuApiModel;

/**
 * Class DemoController
 */
class ManuapiController extends FormController {
    
    use EntityContactsTrait;
    
    public function IndexAction($page =1) {
        $api_model = $this->getModel('manuapi');
        
        //$entities = $api_model->getEntities();
        
        /*
        return $this->delegateView(
            [
                'viewParameters' => [
                    'entities' => $entities,
                ],
                'contentTemplate' => "ManuApiBundle:Default:index.html.php",
                'passthroughVars' => [
                    'activeLink'    => '#manuapi_index',
                    'mauticContent' => 'api',
                    'route'         => $this->generateUrl('manuapi_index', ['page' => $page]),
                ]
            ]
        
        );
        
        */
        
        //set some permissions
        $permissions = $this->get('mautic.security')->isGranted(
            [
                'manuapi:manuapis:viewown',
                'manuapi:manuapis:viewother',
                'manuapi:manuapis:create',
                'manuapi:manuapis:editown',
                'manuapi:manuapis:editother',
                'manuapi:manuapis:deleteown',
                'manuapi:manuapis:deleteother',
                'manuapi:manuapis:publishown',
                'manuapi:manuapis:publishother',
            ],
            'RETURN_ARRAY'
        );
        
        if ($this->request->getMethod() == 'POST') {
            $this->setListFilters();
        }

        $session = $this->get('session');

        //set limits        
        
        $limit = $session->get('mautic.manuapi.limit', $this->coreParametersHelper->getParameter('default_pagelimit'));
        $start = ($page === 1) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }
        

        $search = $this->request->get('search', $session->get('mautic.manuapi.filter', ''));
        $session->set('mautic.manuapi.filter', $search);

        $filter = ['string' => $search];

        if (!$permissions['manuapi:manuapis:viewother']) {
            $filter['force'][] =
                [
                    'column' => 'e.createdBy',
                    'expr'   => 'eq',
                    'value'  => $this->user->getId(),
                ];
        }

        $orderBy    = $session->get('mautic.manuapi.orderby', 'e.name');
        $orderByDir = $session->get('mautic.manuapi.orderbydir', 'DESC');
        
        $manuapis = $api_model->getEntities([
            'start'      => $start,
            'limit'      => $limit,
            'filter'     => $filter,
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
        ]);

        $count = count($manuapis);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if ($count === 1) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($count / $limit)) ?: 1;
            }

            $session->set('mautic.manuapi.page', $lastPage);
            $returnUrl = $this->generateUrl('manuapi_index', ['page' => $lastPage]);

            
            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $lastPage],
                'contentTemplate' => 'ManuApiBundle:Manuapi:index.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#manuapi_index',
                    'mauticContent' => 'manuapi',
                ],
            ]);
            
        }
        $session->set('mautic.manuapi.page', $page);
        $integration = $this->get('mautic.helper.integration')->getIntegrationObject('ManuApi');
        
                return $this->delegateView([
            'viewParameters' => [
                'searchValue' => $search,
                'items'       => $manuapis,
                'totalItems'  => $count,
                'page'        => $page,
                'limit'       => $limit,
                'tmpl'        => $this->request->get('tmpl', 'index'),
                'permissions' => $permissions,
                'model'       => $api_model,
                'security'    => $this->get('mautic.security'),
                'configured'  => ($integration && $integration->getIntegrationSettings()->getIsPublished()),
                'configured'  => true,
            ],
            //'contentTemplate' => 'MauticSmsBundle:Sms:list.html.php',
            'contentTemplate' => 'ManuApiBundle:Manuapi:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#manuapi_index',
                'mauticContent' => 'manuapi',
                'route'         => $this->generateUrl('manuapi_index', ['page' => $page]),
            ],
        ]);
        
    }
    
        /**
     * Loads a specific form into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \MauticPlugin\ManuApiBundle\Model\ManuApiModel $model */
        $model    = $this->getModel('manuapi');
        $security = $this->get('mautic.security');

        /** @var \MauticPlugin\ManuApiBundle\Entity\ManuApi $manuapi */
        $manuapi = $model->getEntity($objectId);
        //set the page we came from
        $page = $this->get('session')->get('mautic.manuapi.page', 1);

        if ($manuapi === null) {
            //set the return URL
            $returnUrl = $this->generateUrl('manuapi_index', ['page' => $page]);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'ManuApiBundle:Manuapi:index',
                'passthroughVars' => [
                    'activeLink'    => '#manuapi_index',
                    'mauticContent' => 'manuapi',
                ],
                'flashes' => [
                    [
                        'type'    => 'error',
                        'msg'     => 'mautic.manuapi.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ],
                ],
            ]);
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'manuapi:manuapis:viewown',
            'manuapi:manuapis:viewother',
            $manuapi->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        // Audit Log
        $logs = $this->getModel('core.auditLog')->getLogForObject('manuapi', $manuapi->getId(), $manuapi->getDateAdded());

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('mautic_manuapi_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create('daterange', $dateRangeValues, ['action' => $action]);
        $entityViews     = $model->getHitsLineChartData(
            null,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData()),
            null,
            ['manuapi_id' => $manuapi->getId()]
        );

        // Get click through stats
        $trackableLinks = $model->getManuapiClickStats($manuapi->getId());

        return $this->delegateView([
            'returnUrl'      => $this->generateUrl('mautic_manuapi_action', ['objectAction' => 'view', 'objectId' => $manuapi->getId()]),
            'viewParameters' => [
                'manuapi'     => $manuapi,
                'trackables'  => $trackableLinks,
                'logs'        => $logs,
                'isEmbedded'  => $this->request->get('isEmbedded') ? $this->request->get('isEmbedded') : false,
                'permissions' => $security->isGranted([
                    'manuapi:manuapis:viewown',
                    'manuapi:manuapis:viewother',
                    'manuapi:manuapis:create',
                    'manuapi:manuapis:editown',
                    'manuapi:manuapis:editother',
                    'manuapi:manuapis:deleteown',
                    'manuapi:manuapis:deleteother',
                    'manuapi:manuapis:publishown',
                    'manuapi:manuapis:publishother',
                ], 'RETURN_ARRAY'),
                'security'    => $security,
                'entityViews' => $entityViews,
                'contacts'    => $this->forward(
                    'ManuApiBundle:Manuapi:contacts',
                    [
                        'objectId'   => $manuapi->getId(),
                        'page'       => $this->get('session')->get('mautic.manuapi.contact.page', 1),
                        'ignoreAjax' => true,
                    ]
                )->getContent(),
                'dateRangeForm' => $dateRangeForm->createView(),
            ],
            'contentTemplate' => 'ManuApiBundle:Manuapi:details.html.php',
            'passthroughVars' => [
                'activeLink'    => '#manuapi_index',
                'mauticContent' => 'manuapi',
            ],
        ]);
    }
    
    /**
     * Generates new form and processes post data.
     *
     * @param Msnuapi $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        /** @var \Mautic\SmsBundle\Model\SmsModel $model */
        $model = $this->getModel('manuapi');

        if (!$entity instanceof Manuapi) {
            /** @var \MauticPlugin\ManuApiBundle\Entity\ManuApi $entity */
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');

        if (!$this->get('mautic.security')->isGranted('manuapi:manuapis:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page   = $session->get('mautic.manuapi.page', 1);
        $action = $this->generateUrl('mautic_manuapi_action', ['objectAction' => 'new']);

        $updateSelect = ($method == 'POST')
            ? $this->request->request->get('manuapi[updateSelect]', false, true)
            : $this->request->get('updateSelect', false);

        if ($updateSelect) {
            $entity->setManuapiType('template');
        }

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if ($method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'manuapi_index',
                            '%url%'       => $this->generateUrl(
                                'mautic_sms_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $entity->getId(),
                        ];
                        $returnUrl = $this->generateUrl('mautic_manuapi_action', $viewParameters);
                        $template  = 'ManuApiBundle:Manuapi:view';
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('manuapi_index', $viewParameters);
                $template       = 'ManuApiBundle:Manuapi:index';
                //clear any modified content
                $session->remove('mautic.manuapi.'.$entity->getId().'.content');
            }

            $passthrough = [
                'activeLink'    => 'manuapi_index',
                'mauticContent' => 'manuapi',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,                        
                        'contentTemplate' => $template,
                        //'contentTemplate' => 'ManuApiBundle:Manuapi:form.html.php',
                        'passthroughVars' => $passthrough,
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $this->setFormTheme($form, 'ManuApiBundle:Manuapi:form.html.php', 'ManuApiBundle:FormTheme\Manuapi'),
                    'manuapi'  => $entity,
                ],
                'contentTemplate' => 'ManuApiBundle:Manuapi:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#manuapi_index',
                    'mauticContent' => 'manuapi',
                    'updateSelect'  => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'         => $this->generateUrl(
                        'mautic_manuapi_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     * @param bool $forceTypeSelection
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        /** @var \MauticPlugin\ManuApiBundle\Model\ManuaApiModel $model */
        $model   = $this->getModel('manuapi');
        $method  = $this->request->getMethod();
        $entity  = $model->getEntity($objectId);
        $session = $this->get('session');
        $page    = $session->get('mautic.manuapi.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('manuapi_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'ManuApiBundle:Manuapi:index',
            'passthroughVars' => [
                'activeLink'    => 'manuapi_index',
                'mauticContent' => 'manuapi',
            ],
        ];

        //not found
        if ($entity === null) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mautic.manuapi.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('mautic.security')->hasEntityAccess(
            'manuapi:manuapis:viewown',
            'manuapi:manuapis:viewother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'manuapi');
        }

        //Create the form
        $action = $this->generateUrl('mautic_manuapi_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        $updateSelect = ($method == 'POST')
            ? $this->request->request->get('manuapi[updateSelect]', false, true)
            : $this->request->get('updateSelect', false);

        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if (!$ignorePost && $method == 'POST') {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash(
                        'mautic.core.notice.updated',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'manuapi_index',
                            '%url%'       => $this->generateUrl(
                                'mautic_manuapi_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ],
                        'warning'
                    );
                }
            } else {
                //clear any modified content
                $session->remove('mautic.manuapi.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            $passthrough = [
                'activeLink'    => 'manuapi_index',
                'mauticContent' => 'manuapi',
            ];

            $template = 'ManuApiBundle:Manuapi:view';

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('mautic_manuapi_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                        ]
                    )
                );
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'               => $this->setFormTheme($form, 'ManuApiBundle:Manuapi:form.html.php', 'ManuApiBundle:FormTheme\Manuapi'),
                    'manuapi'            => $entity,
                    'forceTypeSelection' => $forceTypeSelection,
                ],
                'contentTemplate' => 'ManuApiBundle:Manuapi:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#manuapi_index',
                    'mauticContent' => 'manuapi',
                    'updateSelect'  => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'         => $this->generateUrl(
                        'mautic_manuapi_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Clone an entity.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        $model  = $this->getModel('manuapi');
        $entity = $model->getEntity($objectId);

        if ($entity != null) {
            if (!$this->get('mautic.security')->isGranted('manuapi:manuapis:create')
                || !$this->get('mautic.security')->hasEntityAccess(
                    'manuapi:manuapis:viewown',
                    'manuapi:manuapis:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity = clone $entity;
        }

        return $this->newAction($entity);
    }

    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('mautic.manuapi.page', 1);
        $returnUrl = $this->generateUrl('manuapi_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'ManuApiBundle:Manuapi:index',
            'passthroughVars' => [
                'activeLink'    => 'manuapi_index',
                'mauticContent' => 'manuapi',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model  = $this->getModel('manuapi');
            $entity = $model->getEntity($objectId);

            if ($entity === null) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mautic.manuapi.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('mautic.security')->hasEntityAccess(
                'sms:smses:deleteown',
                'sms:smses:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'manuapi');
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mautic.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                ['flashes' => $flashes]
            )
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic.manuapi.page', 1);
        $returnUrl = $this->generateUrl('manuapi_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'ManuApiBundle:Manuapi:index',
            'passthroughVars' => [
                'activeLink'    => '#manuapi_index',
                'mauticContent' => 'manuapi',
            ],
        ];

        if ($this->request->getMethod() == 'POST') {
            $model = $this->getModel('manuapi');
            $ids   = json_decode($this->request->query->get('ids', '{}'));

            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if ($entity === null) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mautic.manuapi.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->hasEntityAccess(
                    'sms:smses:viewown',
                    'sms:smses:viewother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'manuapi', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mautic.manuapi.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                ['flashes' => $flashes]
            )
        );
    }

    /**
     * @param $objectId
     *
     * @return JsonResponse|Response
     */
    public function previewAction($objectId)
    {
        /** @var \MauticPlugin\ManuApiBundle\Model\ManuApiModel $model */
        $model    = $this->getModel('manuapi');
        $sms      = $model->getEntity($objectId);
        $security = $this->get('mautic.security');

        if ($sms !== null && $security->hasEntityAccess('manuapi:manuapis:viewown', 'manuapi:manuapis:viewother')) {
            return $this->delegateView([
                'viewParameters' => [
                    'manuapi' => $manuapi,
                ],
                'contentTemplate' => 'ManuApiBundle:Manuapi:preview.html.php',
            ]);
        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }

    /**
     * @param     $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contactsAction($objectId, $page = 1)
    {
        return $this->generateContactsGrid(
            $objectId,
            $page,
            'manuapi:manuapis:view',
            'manuapi',
            'manuapi_message_stats',
            'manuapi',
            'manuapi_id'
        );
    }
}