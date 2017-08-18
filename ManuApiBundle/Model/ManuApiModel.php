<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\ChannelBundle\Model\MessageQueueModel;
use Mautic\CoreBundle\Event\TokenReplacementEvent;
use Mautic\CoreBundle\Helper\Chart\ChartQuery;
use Mautic\CoreBundle\Helper\Chart\LineChart;
use Mautic\CoreBundle\Model\AjaxLookupModelInterface;
use Mautic\CoreBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\DoNotContact;
use Mautic\LeadBundle\Entity\DoNotContactRepository;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use Mautic\PageBundle\Model\TrackableModel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use MauticPlugin\ManuApiBundle\Entity\Manuapi;
use MauticPlugin\ManuApiBundle\Event\ManuapiSendEvent;
use MauticPlugin\ManuApiBundle\Event\ManuapiEvent;
use MauticPlugin\ManuApiBundle\ManuapiEvents;
use MauticPlugin\ManuApiBundle\api\LocalhostApi;

/**
 * Class ManuApiModel
 * {@inheritdoc}
 */

class ManuApiModel extends FormModel implements AjaxLookupModelInterface {
    
    /**
     * @var TrackableModel
     */
    protected $pageTrackableModel;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var MessageQueueModel
     */
    protected $messageQueueModel;

    
    public function __construct(TrackableModel $pageTrackableModel, LeadModel $leadModel, MessageQueueModel $messageQueueModel, LocalhostApi $localhostApi) {
        $this->pageTrackableModel = $pageTrackableModel;
        $this->leadModel          = $leadModel;
        $this->messageQueueModel  = $messageQueueModel;
        $this->localhostApi       = $localhostApi;
    }
    
    /**
     * {@inheritdoc}
     *
     * @return \MauticPlugin\ManuApiBundle\Entity\ManuApiRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('ManuApiBundle:Manuapi');
        //return $repository;
    }
    
     /**
     * {@inheritdoc}
     */
    public function getPermissionBase()
    {
        //return 'sms:smses';
        return 'manuapi:manuapis';
    }
    
    
    /*
    public function getEntities() {
        return $entities;
    }
    */
    
    public function callApi($id) {
        return $result;
    }
    
        /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof Manuapi) {
            throw new MethodNotAllowedHttpException(['Manuapi']);
        }
        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create('manuapi', $entity, $options);
    }
    
    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
       /**
     * Dispatches events for child classes.
     *
     * @param       $action
     * @param       $entity
     * @param bool  $isNew
     * @param Event $event
     *
     * @return Event|null
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        //...

        return $event;
    }
    
    /**
     * Joins the page table and limits created_by to currently logged in user.
     *
     * @param QueryBuilder $q
     */
    public function limitQueryToCreator(QueryBuilder &$q)
    {
        $q->join('t', MAUTIC_TABLE_PREFIX.'manuapi_messages', 's', 's.id = t.manuapi_id')
            ->andWhere('s.created_by = :userId')
            ->setParameter('userId', $this->userHelper->getUser()->getId());
    }
    
    /**
     * @param        $type
     * @param string $filter
     * @param int    $limit
     * @param int    $start
     * @param array  $options
     *
     * @return array
     */
    public function getLookupResults($type, $filter = '', $limit = 10, $start = 0, $options = [])
    {
        $results = [];
        switch ($type) {
            case 'manuapi':
                $entities = $this->getRepository()->getManuapiList(
                    $filter,
                    $limit,
                    $start,
                    $this->security->isGranted($this->getPermissionBase().':viewother'),
                    isset($options['template']) ? $options['template'] : false
                );

                foreach ($entities as $entity) {
                    $results[$entity['language']][$entity['id']] = $entity['name'];
                }

                //sort by language
                ksort($results);

                break;
        }

        return $results;
    }
    
    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return null|Manuapi
     */
    public function getEntity($id = null)
    {
        if ($id === null) {
            $entity = new Manuapi();
        } else {
            $entity = parent::getEntity($id);
        }

        return $entity;
    }
    
       /**
     * Get line chart data of hits.
     *
     * @param char      $unit          {@link php.net/manual/en/function.date.php#refsect1-function.date-parameters}
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @param string    $dateFormat
     * @param array     $filter
     * @param bool      $canViewOthers
     *
     * @return array
     */
    public function getHitsLineChartData($unit, \DateTime $dateFrom, \DateTime $dateTo, $dateFormat = null, $filter = [], $canViewOthers = true)
    {
        $flag = null;

        if (isset($filter['flag'])) {
            $flag = $filter['flag'];
            unset($filter['flag']);
        }

        $chart = new LineChart($unit, $dateFrom, $dateTo, $dateFormat);
        $query = new ChartQuery($this->em->getConnection(), $dateFrom, $dateTo);

        if (!$flag || $flag === 'total_and_unique') {
            $q = $query->prepareTimeDataQuery('manuapi_message_stats', 'date_sent', $filter);

            if (!$canViewOthers) {
                $this->limitQueryToCreator($q);
            }

            $data = $query->loadAndBuildTimeData($q);
            $chart->setDataset($this->translator->trans('mautic.manuapi.show.total.sent'), $data);
        }

        return $chart->render();
    }
    
        /**
     * Get an array of tracked links.
     *
     * @param $manuapiId
     *
     * @return array
     */
    public function getManuapiClickStats($manuapiId)
    {
        return $this->pageTrackableModel->getTrackableList('manuapi', $manuapiId);
    }
    
        /**
     * @param Manuapi   $manuapi
     * @param           $sendTo
     * @param array     $options
     *
     * @return array
     */
    public function sendManuapi(Manuapi $manuapi, $sendTo, $options = [])
    {
        $channel = (isset($options['channel'])) ? $options['channel'] : null;

        if ($sendTo instanceof Lead) {
            $sendTo = [$sendTo];
        } elseif (!is_array($sendTo)) {
            $sendTo = [$sendTo];
        }

        $sentCount     = 0;
        $results       = [];
        $contacts      = [];
        $fetchContacts = [];
        foreach ($sendTo as $lead) {
            if (!$lead instanceof Lead) {
                $fetchContacts[] = $lead;
            } else {
                $contacts[$lead->getId()] = $lead;
            }
        }

        if ($fetchContacts) {
            $foundContacts = $this->leadModel->getEntities(
                [
                    'ids' => $fetchContacts,
                ]
            );

            foreach ($foundContacts as $contact) {
                $contacts[$contact->getId()] = $contact;
            }
        }
        $contactIds = array_keys($contacts);

        /** @var DoNotContactRepository $dncRepo */
        $dncRepo = $this->em->getRepository('MauticLeadBundle:DoNotContact');
        $dnc     = $dncRepo->getChannelList('manuapi', $contactIds);

        if (!empty($dnc)) {
            foreach ($dnc as $removeMeId => $removeMeReason) {
                $results[$removeMeId] = [
                    'sent'   => false,
                    'status' => 'mautic.sms.campaign.failed.not_contactable',
                ];

                unset($contacts[$removeMeId], $contactIds[$removeMeId]);
            }
        }

        if (!empty($contacts)) {
            $messageQueue    = (isset($options['resend_message_queue'])) ? $options['resend_message_queue'] : null;
            $campaignEventId = (is_array($channel) && 'campaign.event' === $channel[0] && !empty($channel[1])) ? $channel[1] : null;

            $queued = $this->messageQueueModel->processFrequencyRules(
                $contacts,
                'manuapi',
                $manuapi->getId(),
                $campaignEventId,
                3,
                MessageQueue::PRIORITY_NORMAL,
                $messageQueue,
                'manuapi_message_stats'
            );

            if ($queued) {
                foreach ($queued as $queue) {
                    $results[$queue] = [
                        'sent'   => false,
                        'status' => 'mautic.manuapi.timeline.status.scheduled',
                    ];

                    unset($contacts[$queue]);
                }
            }

            $stats = [];
            if (count($contacts)) {
                /** @var Lead $lead */
                foreach ($contacts as $lead) {
                    $leadId = $lead->getId();

                    $leadPhoneNumber = $lead->getMobile();
                    if (empty($leadPhoneNumber)) {
                        $leadPhoneNumber = $lead->getPhone();
                    }

                    if (empty($leadPhoneNumber)) {
                        $results[$leadId] = [
                            'sent'   => false,
                            'status' => 'mautic.manuapi.campaign.failed.missing_number',
                        ];
                    }

                    $manuapiEvent = new ManuapiSendEvent($manuapi->getMessage(), $lead);
                    $manuapiEvent->setManuapiId($manuapi->getId());
                    $this->dispatcher->dispatch(ManuapiEvents::MANUAPI_ON_SEND, $manuapiEvent);

                    $tokenEvent = $this->dispatcher->dispatch(
                        ManuapiEvents::TOKEN_REPLACEMENT,
                        new TokenReplacementEvent(
                            $manuapiEvent->getContent(),
                            $lead,
                            ['channel' => ['manuapi', $manuapi->getId()]]
                        )
                    );

                    $sendResult = [
                        'sent'    => false,
                        'type'    => 'mautic.manuapi.manuapi',
                        'status'  => 'mautic.manuapi.timeline.status.delivered',
                        'id'      => $manuapi->getId(),
                        'name'    => $manuapi->getName(),
                        'content' => $tokenEvent->getContent(),
                    ];

                    $metadata = $this->localhostApi->sendManuapi($leadPhoneNumber, $tokenEvent->getContent());

                    if (true !== $metadata) {
                        $sendResult['status'] = $metadata;
                    } else {
                        $sendResult['sent'] = true;
                        $stats[]            = $this->createStatEntry($manuapi, $lead, $channel, false);
                        ++$sentCount;
                    }

                    $results[$leadId] = $sendResult;

                    unset($manuapiEvent, $tokenEvent, $sendResult, $metadata);
                }
            }
        }

        if ($sentCount) {
            $this->getRepository()->upCount($manuapi->getId(), 'sent', $sentCount);
            $this->getStatRepository()->saveEntities($stats);
            $this->em->clear(Stat::class);
        }

        return $results;
    }
    
}