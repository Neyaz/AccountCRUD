<?php

namespace Core\Internal;

/**
 * MetadataFromDatabase
 */
class MetadataFromDatabase
{
    private static $dbcmf = NULL;

    public static function GetDBMetadata()
    {
        if (self::$dbcmf == NULL)
        {
            $config = new \Doctrine\ORM\Configuration();
            $yamlConfig = \Symfony\Component\Yaml\Yaml::parse(\Core\Path::Relative('Config.yml'));
            $connectionOptions = $yamlConfig['database'];
            if (!isset($connectionOptions['driver'])) $connectionOptions['driver'] = 'pdo_mysql';
            $connectionOptions['charset'] = 'utf8';
            $config->setMetadataDriverImpl(new \Doctrine\ORM\Mapping\Driver\DriverChain());
            $config->setProxyDir(\Core\Path::Relative("Entities", 'Proxies'));
            $config->setProxyNamespace('Proxies');
            $config->setAutoGenerateProxyClasses(false);
            $dbEM = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

            $cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
            $cmf->setEntityManager($dbEM);

            $databaseDriver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver($dbEM->getConnection()->getSchemaManager());
            $config->setMetadataDriverImpl($databaseDriver);
            self::$dbcmf = $cmf;
        }

        return self::$dbcmf->getAllMetadata();
    }

    public static function FindNewDatatbaseEntities()
    {
        $fromClasses = \Core\Database::Main()->getMetadataFactory()->getAllMetadata();
        $fromDB = self::GetDBMetadata();

        $result = array();
        foreach ($fromDB as $val)
        {
            $add = true;
            foreach ($fromClasses as $cmp)
                if ($cmp->table['name'] == $val->table['name'])
                    $add = false;
            if ($add) $result[] = $val;
        }

        return $result;
    }

    public static function FindNewFieldsInfo($fromDbMeta, $fromClassesMeta)
    {
        $result = array();
        foreach ($fromDbMeta->fieldMappings as $value)
        {
            $name = $value['fieldName'];
            if (!\array_key_exists($name, $fromClassesMeta->fieldMappings))
                $result[] = $value;
        }

        return $result;
    }

    public static function GetDBMetadataEntity($name)
    {
        $fromDB = self::GetDBMetadata();

        $result = array();
        foreach ($fromDB as $meta)
        {
            if ($meta->name == $name)
                return $meta;
        }

        return null;
    }

    public static function FindNewDatatbaseFields()
    {
        $fromClasses = \Core\Database::Main()->getMetadataFactory()->getAllMetadata();
        $fromDB = \Core\Internal\MetadataFromDatabase::GetDBMetadata();

        $result = array();
        foreach ($fromDB as $dbit)
        {
            $type = array();
            foreach ($fromClasses as $cls)
            {
                $add = null;
                if ($cls->table['name'] == $dbit->table['name'])
                {
                    $add = self::FindNewFieldsInfo($dbit, $cls);
                }
                if (count($add) > 0)
                    $result[$cls->name] = $add;
            }
        }

        return $result;
    }

    public static function GetEntityNameByTableName($tableName)
    {
        $list = self::GetDBMetadata();
        foreach ($list as $key => $val)
        {
            if ($val->table['name'] == $tableName)
                return $val->name;
        }

        return null;
    }
}

?>
