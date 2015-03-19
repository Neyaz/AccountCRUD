<?

namespace Core;

/**
 * BaseTable
 * @method mixed FindBy*(mixed $value) magic finders; @see __call()
 * @method mixed FindOneBy*(mixed $value) magic finders; @see __call()
 */
class BaseTable
{
    /**
     * Database EntityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public static function Database()
    {
        return \Core\Database::Main();
    }

    /**
     * EntityRepository
     * @return \Doctrine\ORM\EntityRepository
     */
    protected static function Instance()
    {
        return self::Database()->GetRepository(self::EntityName());
    }

    /**
     * Adds support for magic finders.
     * @return array|object The found entity/entities.
     * @throws BadMethodCallException  If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    static function __callStatic($name, $arguments)
    {
        return self::Instance()->__call($name, $arguments);
    }

    /**
     * Finds an entity by its primary key / identifier.
     * @param $id The identifier.
     * @param int $lockMode
     * @return \Core\BaseEntity The entity.
     */
    public static function GetItem($id, $lockMode = \Doctrine\DBAL\LockMode::NONE)
    {
        return self::Instance()->Find($id, $lockMode, NULL);
    }

    /**
	 * Получить массив сущностей по массиву id. Полагает, что первичный ключ называется id.
	 * @param type $idArr
	 * @param type $keepOrder Если установлено, то порядок сущностей будет соответствовать порядку ключей в массиве
	 * @param type $lockMode
	 * @return array Массив сущностей
	 */
	public static function GetItemsArray($idArr, $keepOrder = true)
    {
        $joins = array();
        $params = array();
        $wheres = array();

        $wheres[] = DQLGenerator::GetWhereInPartQuery('entity.id', $idArr, $params);

        $dql = DQLGenerator::Select(self::EntityName()." entity", $wheres, $joins);
        $unorderedArr = self::Database()->CreateQuery($dql)->SetParameters($params)->GetResult();

        if ($keepOrder)
        {
            $mapById = array();
            foreach($unorderedArr as $entity)
            {
                $mapById[$entity->GetId()] = $entity;
            }

            $orderedArr = array();
            foreach($idArr as $id)
            {
                $orderedArr[] = $mapById[$id];
            }
            return $orderedArr;
        } else {
            return $unorderedArr;
        }
    }

    /**
     * Count entries in table
     * @return int Count of entries in table.
     */
    public static function CountEntries($alias = 'entity', $wheres = array())
    {
        $dql = DQLGenerator::Select(self::EntityName() . " " . $alias, $wheres, array(), null, "COUNT(" . $alias . ")");
        return self::Database()->CreateQuery($dql)->GetSingleScalarResult();
    }

    /**
     * Finds all entities in the repository.
     * @return array The entities.
     */
    public static function GetAll()
    {
        return self::Instance()->FindBy(array());
    }

    /**
     * Clears the repository, causing all managed entities to become detached.
     */
    public static function Clear()
    {
        return self::Instance()->Clear();
    }

    /**
     * Finds entities by a set of criteria.
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array The objects.
     */
    public static function FindBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return self::Database()->GetUnitOfWork()->GetEntityPersister(self::EntityName())->loadAll($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Finds a single entity by a set of criteria.
     * @param array $criteria
     * @return object
     */
    public static function FindOneBy(array $criteria)
    {
        return self::Database()->GetUnitOfWork()->GetEntityPersister(self::EntityName())->load($criteria);
    }

    /**
     * @return string
     */
    public static function EntityName()
    {
        $class = get_called_class();
        if (substr_compare($class, 'Table', strlen($class) - 5) != 0) throw new \Exception ('Invalid class name. Correct name: <Entity>Table');
        $name = substr($class, 0, strlen($class) - 5);
        return $name;
    }
}

?>
