<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AdherentEmailSubscriptionHistory;
use AppBundle\Entity\ReferentTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class AdherentEmailSubscriptionHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdherentEmailSubscriptionHistory::class);
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findByAdherent(Adherent $adherent, $withoutInactives = true): array
    {
        $qb = $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->setParameter('adherent', $adherent)
            ->orderBy('h.subscribedAt', 'DESC')
        ;

        if ($withoutInactives) {
            $qb->andWhere('h.unsubscribedAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findAllByAdherentAndType(Adherent $adherent, string $subscriptionType): array
    {
        return $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->andWhere('h.subscribedEmailsType = :type')
            ->orderBy('h.subscribedAt', 'DESC')
            ->setParameter('adherent', $adherent)
            ->setParameter('type', $subscriptionType)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Adherent $adherent
     *
     * @return AdherentEmailSubscriptionHistory[]
     */
    public function findAllByAdherentAndReferentTag(Adherent $adherent, ReferentTag $tag): array
    {
        return $this
            ->createQueryBuilder('h')
            ->where('h.adherent = :adherent')
            ->andWhere('h.referentTag = :tag')
            ->setParameter('adherent', $adherent)
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Count active adherents (not users) history for each specified subscription types in the referent managed area before the specified date.
     *
     * @param Adherent  $referent
     * @param array     $subscriptionsTypes
     * @param \DateTime $from
     *
     * @return array
     */
    public function countAllByTypeForReferentManagedArea(Adherent $referent, array $subscriptionsTypes, \DateTime $beforeDate, $cache = false): array
    {
        if (!$referent->isReferent()) {
            throw new \InvalidArgumentException('Adherent must be a referent.');
        }

        $qb = $this->createQueryBuilder('h', 'h.subscribedEmailsType')
            ->select('h.subscribedEmailsType, COUNT(h) AS count')
            ->distinct('a.id')
            ->innerJoin('h.adherent', 'a')
            ->where('a.adherent = 1')
            ->andWhere('a.status = :status')
            ->andWhere('h.referentTag IN (:tags)')
            ->andWhere('h.subscribedEmailsType IN (:subscriptions)')
            ->andWhere('h.subscribedAt < :beforeDate')
            ->andWhere('h.unsubscribedAt IS NULL OR h.unsubscribedAt >= :beforeDate')
            ->setParameter('status', Adherent::ENABLED)
            ->setParameter('tags', $referent->getManagedArea()->getTags())
            ->setParameter('subscriptions', $subscriptionsTypes)
            ->setParameter('beforeDate', $beforeDate)
            ->groupBy('h.subscribedEmailsType')
            ->getQuery()
        ;

        if ($cache) {
            $qb
                ->useQueryCache(true)
                ->useResultCache(true)
                ->setResultCacheLifetime(25920000) // 30 days
            ;
        }

        return $qb->getArrayResult();
    }
}
