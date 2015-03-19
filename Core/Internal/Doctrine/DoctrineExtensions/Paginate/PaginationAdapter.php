<?php
/**
 * DoctrineExtensions Paginate
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. This license can also be viewed
 * at http://hobodave.com/license.txt
 *
 * @category    DoctrineExtensions
 * @package     DoctrineExtensions\Paginate
 * @author      David Abdemoulaie <dave@hobodave.com>
 * @copyright   Copyright (c) 2010 David Abdemoulaie (http://hobodave.com/)
 * @license     http://hobodave.com/license.txt New BSD License
 */

namespace DoctrineExtensions\Paginate;

use Doctrine\ORM\Query;

/**
 * Implements the Zend_Paginator_Adapter_Interface for use with Zend_Paginator
 *
 * Allows pagination of Doctrine\ORM\Query objects and DQL strings
 *
 * @category    DoctrineExtensions
 * @package     DoctrineExtensions\Paginate
 * @author      David Abdemoulaie <dave@hobodave.com>
 * @copyright   Copyright (c) 2010 David Abdemoulaie (http://hobodave.com/)
 * @license     http://hobodave.com/license.txt New BSD License
 */
class PaginationAdapter implements \Zend_Paginator_Adapter_Interface
{
    /**
     * The SELECT query to paginate
     *
     * @var Query
     */
    protected $query = null;

    /**
     * Total item count
     *
     * @var integer
     */
    protected $rowCount = null;

    /**
     * Use Array Result
     *
     * @var boolean
     */
    protected $arrayResult = false;

    /**
     * Namespace to use for bound parameters
     * If you use :pgid_# as a parameter, then
     * you must change this.
     *
     * @var string
     */
    protected $namespace = 'pgid';

    /**
     * Constructor
     *
     * @param Query $query
     * @param string $ns Namespace to prevent named parameter conflicts
     */
    public function __construct(Query $query, $ns = 'pgid')
    {
        $this->query = $query;
        $this->namespace = $ns;
    }

    /**
     * Set use array result flag
     *
     * @param boolean $flag True to use array result
     */
    public function UseArrayResult($flag = true)
    {
        $this->arrayResult = $flag;
    }

    /**
     * Sets the total row count for this paginator
     *
     * Can be either an integer, or a Doctrine\ORM\Query object
     * which returns the count
     *
     * @param Query|integer $rowCount
     * @return void
     */
    public function SetRowCount($rowCount)
    {
        if ($rowCount instanceof Query) {
            $this->rowCount = $rowCount->getSingleScalarResult();
        } else if (is_integer($rowCount)) {
            $this->rowCount = $rowCount;
        } else {
            throw new \InvalidArgumentException("Invalid row count");
        }
    }

    /**
     * Sets the namespace to be used for named parameters
     *
     * Parameters will be in the format 'namespace_1' ... 'namespace_N'
     *
     * @param string $ns
     * @return void
     * @author David Abdemoulaie
     */
    public function SetNamespace($ns)
    {
        $this->namespace = $ns;
    }

    /**
     * Gets the current page of items
     *
     * @param string $offset
     * @param string $itemCountPerPage
     * @return void
     * @author David Abdemoulaie
     */
    public function GetItems($offset, $itemCountPerPage)
    {
        $ids = $this->createLimitSubquery($offset, $itemCountPerPage)
            ->getScalarResult();

        $ids = array_map(
            function ($e) { return current($e); },
            $ids
        );

        if ($this->arrayResult) {
            return $this->createWhereInQuery($ids)->getArrayResult();
        } else {
            return $this->createWhereInQuery($ids)->getResult();
        }
    }

    /**
     * @param Query $query
     * @return int
     */
    public function Count()
    {
        if (is_null($this->rowCount)) {
            $this->setRowCount(
                $this->createCountQuery()
            );
        }
        return $this->rowCount;
    }

    /**
     * @return Query
     */
    protected function CreateCountQuery()
    {
        return Paginate::createCountQuery($this->query);
    }

    /**
     * @return Query
     */
    protected function CreateLimitSubquery($offset, $itemCountPerPage)
    {
        return Paginate::createLimitSubQuery($this->query, $offset, $itemCountPerPage);
    }

    /**
     * @return Query
     */
    protected function CreateWhereInQuery($ids)
    {
        return Paginate::createWhereInQuery($this->query, $ids, $this->namespace);
    }
}