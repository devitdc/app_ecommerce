<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * find all user orders that have been paid
     * @param $user
     * @return float|int|mixed|string
     */
    public function findSuccessOrder($user): mixed
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isPaid = 1')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->orderBy('o.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * find user order that have not been paid
     * @param $user
     * @return float|int|mixed|string
     */
    public function findUserOrderNotPaid($user): mixed
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user = :user')
            ->andWhere('o.deliveryState = 0')
            ->andWhere('o.createdAt > :dateInterval')
            ->setParameter('dateInterval', new \DateTimeImmutable("- 2 days"))
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * find last orders
     */
    public function findLastOrders()
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isPaid = 1')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();
    }


    /**
     * find all orders that have been paid
     */
    public function totalPaidOrders(): mixed
    {
        return $this->createQueryBuilder('o')
            ->select('count (o.id)')
            ->andWhere('o.isPaid = 1')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * find all pending orders, that has not been paid
     */
    public function totalPendingOrders(): mixed
    {
        return $this->createQueryBuilder('o')
            ->select('count (o.id)')
            ->andWhere('o.isPaid = 1')
            ->andWhere('o.deliveryState = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * find all orders in progress delivery
     */
    public function totalProgressDeliveryOrders(): mixed
    {
        return $this->createQueryBuilder('o')
            ->select('count (o.id)')
            ->andWhere('o.isPaid = 1')
            ->andWhere('o.deliveryState = 5')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * find all delivered orders in progress delivery
     */
    public function totalDeliveredOrders(): mixed
    {
        return $this->createQueryBuilder('o')
            ->select('count (o.id)')
            ->andWhere('o.isPaid = 1')
            ->andWhere('o.deliveryState = 6')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function dayAmount()
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('o')
            ->select('SUM(order_details.total)')
            ->leftJoin('o.orderDetails', 'order_details')
            ->andWhere("DATE_FORMAT(o.createdAt, '%Y-%m-%d') = :now")
            ->setParameter('now', $now->format('Y-m-d'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function monthAmount()
    {
        $month = date('m');
        $year = date('Y');
        $start = "{$year}-{$month}-01 00:00:00";
        $end = "{$year}-{$month}-31 23:59:59";

        return $this->createQueryBuilder('o')
            ->select('SUM(order_details.total)')
            ->leftJoin('o.orderDetails', 'order_details')
            ->andWhere("o.createdAt BETWEEN :start AND :end")
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function yearAmount()
    {
        $year = date('Y');
        $start = "{$year}-01-01 00:00:00";
        $end = "{$year}-12-31 23:59:59";

        return $this->createQueryBuilder('o')
            ->select('SUM(order_details.total)')
            ->leftJoin('o.orderDetails', 'order_details')
            ->andWhere("o.createdAt BETWEEN :start AND :end")
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function salesOverviewByMonth()
    {
        return $this->createQueryBuilder('o')
            ->select("DATE_FORMAT(o.createdAt, '%M') as month, SUM(order_details.total) as total")
            ->leftJoin('o.orderDetails', 'order_details')
            ->andWhere("DATE_FORMAT(o.createdAt, '%Y') = :year")
            ->setParameter('year', date('Y'))
            ->addGroupBy("month")
            ->orderBy("DATE_FORMAT(o.createdAt, '%m')")
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
