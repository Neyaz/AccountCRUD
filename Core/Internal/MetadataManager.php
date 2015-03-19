<?

namespace Core\Internal;

/**
 * MetadataManager
 */
class MetadataManager
{
    static private $instance = NULL;

    /**
     * @var \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory
     */
    private $cmf;

    /**
     * Синглетон
     * @return MetadataManager
     */
    static function Instance()
    {
        if (self::$instance == NULL)
            self::$instance = new MetadataManager();
        return self::$instance;
    }
    private function __construct()
    {
        $this->cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
        $this->cmf->setEntityManager(\Core\Database::Main());
    }
    private function __clone()
    {
    }

    public function GetAssociaction($className, $assoc)
    {
        $m = $this->GetMetadata($className);
        if (!isset($m->associationMappings[$assoc])) return false;
        $rt = $m->associationMappings[$assoc];
        return $rt;
    }

    /**
     *
     * @param \ReflectionClass $reflectionClass
     * @return string
     */
    public function GetEntityName($reflectionClass)
    {
        if ($reflectionClass->implementsInterface('Doctrine\ORM\Proxy\Proxy'))
            return $reflectionClass->getParentClass()->getName();
        else
            return $reflectionClass->getName();
    }
    
    public function GetField($className, $prop)
    {
        $m = $this->GetMetadata($className);
        if ($m == NULL) return false;
        if (!isset($m->fieldMappings[$prop])) return false;
        $rt = $m->fieldMappings[$prop];
        return $rt;
    }

    public function GetFields($className)
    {
        $m = $this->GetMetadata($className);
        if ($m == NULL) return false;
        $rt = $m->fieldMappings;
        return $rt;
    }

    /**
     * @param string $className
     * @return Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    public function GetMetadata($className)
    {
        return $this->cmf->getMetadataFor($className);
    }

    public function GetTableName($className)
    {
        $list = $this->cmf->GetAllMetadata();
        foreach ($list as $val)
        {
            if ($val->name == $className)
                return $val->table['name'];
        }

        return null;
    }

    public function GetEntityNameByTableName($tableName)
    {
        $list = $this->cmf->GetAllMetadata();
        foreach ($list as $val)
        {
            if ($val->table['name'] == $tableName)
                return $val->name;
        }

        return null;
    }

    public function GetFieldNameByColumnName($tableName, $column)
    {
        $name = $this->GetEntityNameByTableName($tableName);
        $meta = $this->GetMetadata($name);
        foreach ($meta->fieldMappings as $field => $map)
            if ($map['columnName'] == $column)
                return $field;
        
        return null;
    }
}

?>
