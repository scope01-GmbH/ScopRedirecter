<?php
/**
 * Implemented by scope01 GmbH team https://scope01.com
 *
 * @copyright scope01 GmbH https://scope01.com
 * @license MIT License
 * @link https://scope01.com
 */

namespace ScopRedirecter\Models;

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

class ScopRedirecterRepository extends ModelRepository
{
    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of Redirecter
     *
     * @param array|null $filter
     * @param array|null $orderBy
     * @param int $offset
     * @param int $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter, $orderBy, $offset = null, $limit = null)
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
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(array('redirecter'))
            ->from($this->getEntityName(), 'redirecter');

        if (!empty($filter)) {
            $this->addFilter($builder, $filter);
        }

        if (!empty($orderBy)) {
            $this->addOrderBy($builder, $orderBy);
        }

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
        $data = $builder->getQuery()->getArrayResult();
        return $data;
    }
}
