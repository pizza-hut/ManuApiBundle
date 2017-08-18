<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\EventListener;

use Mautic\ChannelBundle\ChannelEvents;
use Mautic\ChannelBundle\Entity\MessageQueue;
use Mautic\ChannelBundle\Event\MessageQueueBatchProcessEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use MauticPlugin\ManuApiBundle\Model\ManuApiModel;

/**
 * Class MessageQueueSubscriber.
 */
class MessageQueueSubscriber extends CommonSubscriber
{
    /**
     * @var ManuApiModel
     */
    protected $model;

    /**
     * MessageQueueSubscriber constructor.
     *
     * @param ManuApiModel $model
     */
    public function __construct(ManuApiModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ChannelEvents::PROCESS_MESSAGE_QUEUE_BATCH => ['onProcessMessageQueueBatch', 0],
        ];
    }

    /**
     * Sends campaign manuapi.
     *
     * @param MessageQueueBatchProcessEvent $event
     */
    public function onProcessMessageQueueBatch(MessageQueueBatchProcessEvent $event)
    {
        if (!$event->checkContext('manuapi')) {
            return;
        }

        $messages          = $event->getMessages();
        $id                = $event->getChannelId();
        $manuapi           = $this->model->getEntity($id);
        $sendTo            = [];
        $messagesByContact = [];

        /** @var MessageQueue $message */
        foreach ($messages as $id => $message) {
            if ($manuapi && $message->getLead()) {
                $contact = $message->getLead();
                $mobile  = $contact->getMobile();
                $phone   = $contact->getPhone();
                if (empty($mobile) && empty($phone)) {
                    $message->setProcessed();
                    $message->setSuccess();
                }
                $sendTo[$contact->getId()]            = $contact;
                $messagesByContact[$contact->getId()] = $message;
            } else {
                $message->setFailed();
            }
        }

        if (count($sendTo)) {
            $options['resend_message_queue'] = $messagesByContact;
            $results                         = $this->model->sendManuapi($manuapi, $sendTo, $options);

            foreach ($messagesByContact as $contactId => $message) {
                if (!$message->isProcessed()) {
                    $message->setProcessed();
                    $message->setMetadata($results[$contactId]);
                    if ($results[$contactId]['sent']) {
                        $message->setSuccess();
                    }
                }
            }
        }

        $event->stopPropagation();
    }
}
