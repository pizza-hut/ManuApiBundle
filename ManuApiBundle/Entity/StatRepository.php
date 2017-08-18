<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\ManuApiBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;
use Mautic\CoreBundle\Helper\DateTimeHelper;

/**
 * Class StatRepository.
 */
class StatRepository extends CommonRepository
{
    /**
     * @param $trackingHash
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getManuapiStatus($trackingHash)
    {
        $q = $this->createQueryBuilder('s');
        $q->select('s')
            ->leftJoin('s.lead', 'l')
            ->leftJoin('s.manuapi', 'e')
            ->where(
                $q->expr()->eq('s.trackingHash', ':hash')
            )
            ->setParameter('hash', $trackingHash);

        $result = $q->getQuery()->getResult();

        return (!empty($result)) ? $result[0] : null;
    }

    /**
     * @param      $manuapiId
     * @param null $listId
     *
     * @return array
     */
    public function getSentStats($manuapiId, $listId = null)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->select('s.lead_id')
            ->from(MAUTIC_TABLE_PREFIX.'manuapi_messages_stats', 's')
            ->where('s.manuapi_id = :manuapi')
            ->setParameter('manuapi', $manuapiId);

        if ($listId) {
            $q->andWhere('s.list_id = :list')
                ->setParameter('list', $listId);
        }

        $result = $q->execute()->fetchAll();

        //index by lead
        $stats = [];
        foreach ($result as $r) {
            $stats[$r['lead_id']] = $r['lead_id'];
        }

        unset($result);

        return $stats;
    }

    /**
     * @param int|array $manuapiIds
     * @param int       $listId
     *
     * @return int
     */
    public function getSentCount($manuapiIds = null, $listId = null)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->select('count(s.id) as sent_count')
            ->from(MAUTIC_TABLE_PREFIX.'manuapi_message_stats', 's');

        if ($manuapiIds) {
            if (!is_array($manuapiIds)) {
                $manuapiIds = [(int) $manuapiIds];
            }
            $q->where(
                $q->expr()->in('s.manuapi_id', $manuapiIds)
            );
        }

        if ($listId) {
            $q->andWhere('s.list_id = '.(int) $listId);
        }

        $q->andWhere('s.is_failed = :false')
            ->setParameter('false', false, 'boolean');

        $results = $q->execute()->fetchAll();

        return (isset($results[0])) ? $results[0]['sent_count'] : 0;
    }

    /**
     * Get a lead's email stat.
     *
     * @param int   $leadId
     * @param array $options
     *
     * @return array
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLeadStats($leadId, array $options = [])
    {
        $query = $this->createQueryBuilder('s');

        $query->select('IDENTITY(s.manuapi) AS manuapi_id, s.id, s.dateSent, e.title, IDENTITY(s.list) AS list_id, l.name as list_name, s.trackingHash as idHash')
            ->leftJoin('ManuApiBundle:ManuApi', 'e', 'WITH', 'e.id = s.manuapi')
            ->leftJoin('MauticLeadBundle:LeadList', 'l', 'WITH', 'l.id = s.list')
            ->where(
                $query->expr()->eq('IDENTITY(s.lead)', $leadId)
            );

        if (isset($options['search']) && $options['search']) {
            $query->andWhere(
                $query->expr()->like('e.title', $query->expr()->literal('%'.$options['search'].'%'))
            );
        }

        if (isset($options['order'])) {
            list($orderBy, $orderByDir) = $options['order'];

            switch ($orderBy) {
                case 'eventLabel':
                    $orderBy = 'e.title';
                    break;
                case 'timestamp':
                default:
                    $orderBy = 's.dateSent';
                    break;
            }

            $query->orderBy($orderBy, $orderByDir);
        }

        if (!empty($options['limit'])) {
            $query->setMaxResults($options['limit']);

            if (!empty($options['start'])) {
                $query->setFirstResult($options['start']);
            }
        }

        if (isset($options['fromDate']) && $options['fromDate']) {
            $dt = new DateTimeHelper($options['fromDate']);
            $query->andWhere(
                $query->expr()->gte('s.dateSent', $query->expr()->literal($dt->toUtcString()))
            );
        }

        $stats = $query->getQuery()->getArrayResult();

        return $stats;
    }

    /**
     * Updates lead ID (e.g. after a lead merge).
     *
     * @param $fromLeadId
     * @param $toLeadId
     */
    public function updateLead($fromLeadId, $toLeadId)
    {
        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->update(MAUTIC_TABLE_PREFIX.'manuapi_message_stats')
            ->set('manuapi_id', (int) $toLeadId)
            ->where('manuapi_id = '.(int) $fromLeadId)
            ->execute();
    }

    /**
     * Delete a stat.
     *
     * @param $id
     */
    public function deleteStat($id)
    {
        $this->_em->getConnection()->delete(MAUTIC_TABLE_PREFIX.'manuapi_message_stats', ['id' => (int) $id]);
    }

    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 's';
    }
}
