<?php

namespace ScopRedirecter\Models;

use Shopware\Components\Model\ModelRepository;


class ScopRedirecterRepository extends ModelRepository
{

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of Redirecter
     *
     * @param null $filter
     * @param null $orderBy
     * @param      $offset
     * @param      $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $orderBy = null, $offset, $limit)
    {
        $builder = $this->getListQueryBuilder($filter, $orderBy);
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param null $filter
     * @param null $orderBy
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('redirecter'))
            ->from($this->getEntityName(), 'redirecter');

        $this->addFilter($builder, $filter);
        $this->addOrderBy($builder, $orderBy);

        return $builder;
    }

    public function getRedirect($requestedUri)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('redirecter')
            ->from($this->getEntityName(), 'redirecter')
            ->where('redirecter.startUrl = :startUrl')
            ->setParameter("startUrl", $requestedUri)
            ->setMaxResults(1);
            $data =$builder->getQuery()->getArrayResult();
        return $data;
    }
}
