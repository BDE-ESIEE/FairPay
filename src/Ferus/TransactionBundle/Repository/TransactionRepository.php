<?php

namespace Ferus\TransactionBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Ferus\AccountBundle\Entity\Account;
use Ferus\TransactionBundle\Graph\GraphData;

/**
 * TransactionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransactionRepository extends EntityRepository
{
    public function findLast($max = 50)
    {
        return $this->createQueryBuilder('t')
            ->setMaxResults($max)
            ->orderBy('t.completedAt', 'DESC')
            ->getQuery()->getResult();
    }

    public function queryOfAccount(Account $account)
    {
        return $this->createQueryBuilder('t')
            ->where('t.issuer = :account')
            ->orWhere('t.receiver = :account')
            ->setParameter('account', $account)
            ->orderBy('t.completedAt', 'DESC')
            ->getQuery();
    }

    public function accountHasNoTransactions(Account $account)
    {
        $total = $this->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->where('t.issuer = :account')
            ->orWhere('t.receiver = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult();

        return $total == 0;
    }

    public function getGraphData(Account $account)
    {
        $graphData = new GraphData();

        $qb = $this->createQueryBuilder('t')
            ->select('SUM(t.amount)')
            ->addSelect('YEAR(t.completedAt) year, MONTH(t.completedAt) month, DAY(t.completedAt) day')
            ->where('t.issuer = :account')
            ->setParameter('account', $account)
            ->andWhere('t.completedAt > :lastMonth')
            ->setParameter('lastMonth', new \DateTime('-1 month'))
            ->orderBy('year, month, day', 'DESC')
            ->groupBy('year, month, day')
        ;

        $graphData->setOutcomes($qb->getQuery()->getResult());

        $qb
            ->where('t.receiver = :account')
            ->setParameter('account', $account)
            ->andWhere('t.completedAt > :lastMonth')
            ->setParameter('lastMonth', new \DateTime('-1 month'))
        ;

        $graphData->setIncomes($qb->getQuery()->getResult());

        return $graphData;
    }
}
