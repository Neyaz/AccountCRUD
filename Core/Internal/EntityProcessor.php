<?

namespace Core\Internal;

class EntityProcessor
{
    /**
     * @var \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory
     */
    private $cmf;

    private $links = array();

    public function __construct()
    {
       $this->cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
       $this->cmf->setEntityManager(\Core\Database::Main());
    }

    public function GenerateProxies()
    {
        $db = \Core\Database::Main();
        $destPath = $db->getConfiguration()->getProxyDir();

        if (!is_dir($destPath))
            mkdir($destPath, 0777, true);

        $destPath = realpath($destPath);

        if (!file_exists($destPath))
            throw new \InvalidArgumentException(sprintf("Proxies destination directory '<info>%s</info>' does not exist.", $destPath));
        else if (!is_writable($destPath))
            throw new \InvalidArgumentException(sprintf("Proxies destination directory '<info>%s</info>' does not have write permissions.", $destPath));

        $metadatas = $db->getMetadataFactory()->getAllMetadata();
        $db->getProxyFactory()->generateProxyClasses($metadatas, $destPath);
    }

    public function BuildMethods()
    {
        $metadatas = $this->cmf->getAllMetadata();

        foreach ($metadatas as $meta)
        {
            $methods = $this->GenerateEntityMethods($meta);
            $up = new ClassUpdater($meta->name);
            $up->InsertMethods($methods);
        }
    }

    public function BuildTables()
    {
        $metadatas = $this->cmf->getAllMetadata();
		if (!is_dir(\Core\Path::Relative('Entities', 'Tables')))
		    mkdir(\Core\Path::Relative('Entities', 'Tables'), 0750);

        foreach ($metadatas as $meta)
        {
            $methods = $this->BuildTable($meta);
        }
    }

    public function GetStructure()
    {
        $result = array();
        $metadatas = $this->cmf->getAllMetadata();

        foreach ($metadatas as $meta)
        {
            $result[$meta->name] = array();
            foreach ($meta->getFieldNames() as $name)
            {
                $cfg = $meta->getFieldMapping($name);
                $result[$meta->name][$name] = array();
                $result[$meta->name][$name]['type'] = $cfg['type'] . ($cfg['length'] != null ? ':'.$cfg['length'] : '');
                $result[$meta->name][$name]['id'] = $cfg['id'] === true;
            }

            foreach ($meta->getAssociationNames() as $name)
            {
                $cfg = $meta->getAssociationMapping($name);
                $result[$meta->name][$name] = array();
                $result[$meta->name][$name]['assoc'] = true;
                if ($cfg['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE)
                    $result[$meta->name][$name]['type'] = 'one²one';
                if ($cfg['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY)
                    $result[$meta->name][$name]['type'] = 'one²many';
                if ($cfg['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE)
                    $result[$meta->name][$name]['type'] = 'many²one';
                if ($cfg['type'] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY)
                    $result[$meta->name][$name]['type'] = 'many²many';
                $result[$meta->name][$name]['owning'] = $cfg['isOwningSide'];
                $result[$meta->name][$name]['target'] = $cfg['targetEntity'];
                $result[$meta->name][$name]['nullable'] = $cfg['nullable'];
                $result[$meta->name][$name]['unique'] = $cfg['unique'];

                $result[$meta->name][$name]['info'] = array();
                $info = &$result[$meta->name][$name]['info'];
                if ($cfg['inversedBy'] != NULL) $info['inv'] = $cfg['inversedBy'];
                if ($cfg['joinTable'] != NULL) $info['tbl'] = $cfg['joinTable']['name'];

                if ($cfg['joinColumns'] != NULL)
                {
                    $joinColumns = '';
                    foreach ($cfg['joinColumns'] as $value)
                    {
                        if ($joinColumns != '') $joinColumns .= ', ';
                        $joinColumns .= $value['name'];
                    }
                    $info['clmn'] = $joinColumns;
                }
            }
        }

        return $result;
    }

    public function Validate()
    {
        $metadatas = $this->cmf->getAllMetadata();
        $error = array();

        foreach ($metadatas as $meta)
        {
            $r = $this->ValidateClass($meta);
            if (count($r) != 0)
                $error[$meta->getName()] = $r;
        }
        return $error;
    }

    public function GenerateMappingByDatabase($filter = NULL)
    {
        $em = \Core\Database::Main();
        $databaseDriver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver($em->getConnection()->getSchemaManager());
        $em->getConfiguration()->setMetadataDriverImpl($databaseDriver);

        $cmf = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadata = $cmf->getAllMetadata();

        $dest = \Core\Path::Relative('Entities');

        if ($filter != null)
        {
            foreach ($metadata as $it)
            {
                if ($it->name == $filter)
                {
                    $metadata = array();
                    $metadata[] = $it;
                    break;
                }
            }
        }

        $cme = new \Doctrine\ORM\Tools\Export\ClassMetadataExporter();
        $exporter = $cme->getExporter('annotation', $dest);
        $exporter->setOverwriteExistingFiles(false);
        $entityGenerator = new \Doctrine\ORM\Tools\EntityGenerator();
        $exporter->setEntityGenerator($entityGenerator);
        $entityGenerator->setNumSpaces(4);
        $entityGenerator->setClassToExtend('Core\\BaseEntity');
        $entityGenerator->setGenerateStubMethods(false);

        $exporter->setMetadata($metadata);
        $exporter->export();

        \Core\Response::Redirect(':database');
    }

    public function LoadData($storage)
    {
        $empty = false;
        foreach ($storage as $map)
        {
            if ($map == null) continue;
            foreach ($map as $class => $records)
            {
                $rep = \Core\Database::Main()->GetRepository($class);
                if (count($rep->FindAll()) == 0) $empty = true;
                $reflection = new \ReflectionClass($class);
                $meta = MetadataManager::Instance()->GetMetadata($class);
                foreach ($records as $name => $data)
                {
                    $entity = $reflection->newInstance();
                    if (isset($this->links[$name]))
                        Tools::Error("Имя сущности $name уже было задействовано ранее");
                    $this->links[$name] = $entity;
                }
            }
        }

        if (!$empty)
            Tools::Error("Во всех указанных таблицах уже есть загруженные данные, либо Вы не заполнили Fixtures");

        foreach ($storage as $map)
        {
            if ($map == null) continue;
            foreach ($map as $class => $records)
            {
                $reflection = new \ReflectionClass($class);
                $meta = MetadataManager::Instance()->GetMetadata($class);
                foreach ($records as $name => $data)
                {
                    $entity = $this->links[$name];
                    foreach ($data as $key => $value)
                    {
                        $propReflection = Tools::GetProperty($reflection, $key);
                        if ($propReflection != NULL)
                        {
                            $propName = $propReflection->getName();
                            $assoc = MetadataManager::Instance()->GetAssociaction($class, $propName);
                            $field = MetadataManager::Instance()->GetField($class, $propName);

                            if ($field)
                            {
                                $method = 'Set' . \Core\Utils::DashedToCamelCase($propName);
                                $this->ExecuteEntityMethod($reflection, $entity, $method, array($value));
                            }
                            else if ($assoc)
                            {
                                if ($assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY)
                                {
                                    $arr = explode("\\", $assoc['targetEntity']);
                                    $method = 'Add' . \Core\Utils::DashedToCamelCase(end($arr));
                                }
                                else
                                    $method = 'Set' . \Core\Utils::DashedToCamelCase($propName);

                                if (is_array($value))
                                {
                                    foreach ($value as $subValue)
                                        $this->ExecuteEntityAssociactionMethod($reflection, $entity, $method, $subValue);
                                }
                                else
                                {
                                    $this->ExecuteEntityAssociactionMethod($reflection, $entity, $method, $value);
                                }
                            }
                            else
                                Tools::Error("Для поля $class->$key не найдена информация о мапинге");
                        }
                        else
                        {
                            $method = 'Set' . \Core\Utils::DashedToCamelCase($key);
                            $this->ExecuteEntityMethod($reflection, $entity, $method, array($value));
                        }
                    }
                    $entity->Persist();
                }
            }
        }
        \Core\Database::Main()->Flush();
    }

    private function ExecuteEntityAssociactionMethod($reflection, $entity, $method, $value)
    {
        if (!isset($this->links[$value]))
            Tools::Error("Имя сущности $value не объявлено");

        $valueData = $this->links[$value];
        $args = array($valueData, true);
        $this->ExecuteEntityMethod($reflection, $entity, $method, $args);
    }

    private function ExecuteEntityMethod($reflection, $entity, $method, $args)
    {
        $class = get_class($entity);
        if (!$reflection->hasMethod($method))
            Tools::Error("Метод $class->$method не найден");

        $methodCall = $reflection->getMethod($method);
        $methodCall->invokeArgs($entity, $args);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata
     */
    private function GenerateEntityMethods($meta)
    {
        $methods = array();
        $name = $meta->name;

        foreach ($meta->fieldMappings as $fieldMapping)
        {
            $insertedMethods = array();
            $this->CreateMethod($insertedMethods, 'Get', 'MethodPropGet', $fieldMapping['fieldName'], $fieldMapping['type']).PHP_EOL;
            if (!isset($fieldMapping['id']) || !$fieldMapping['id'] || $metadata->generatorType == \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE)
                $this->CreateMethod($insertedMethods, 'Set', 'MethodPropSet', $fieldMapping['fieldName'], $fieldMapping['type']).PHP_EOL;
            $methods[$fieldMapping['fieldName']] = $insertedMethods;
        }

        foreach ($meta->associationMappings as $assoc)
        {
            $insertedMethods = array();
            if ($assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_ONE)
            {
                $inversedByMany = $assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE;
                $this->CreateMethod($insertedMethods, 'Get', 'MethodPropGet', $assoc['fieldName'], $assoc['targetEntity']).PHP_EOL;
                $this->CreateMethod($insertedMethods, 'Set', 'MethodAssocSet', $assoc['fieldName'], $assoc['targetEntity'], $meta->name, $inversedByMany).PHP_EOL;
            } else if ($assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY) {
                $inversedByMany = $assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY;
                $this->CreateMethod($insertedMethods, 'Get', 'MethodPropGet', $assoc['fieldName'], 'Doctrine\Common\Collections\Collection').PHP_EOL;
                $this->CreateMethod($insertedMethods, 'Add', 'MethodAssocAdd', $assoc['fieldName'], $assoc['targetEntity'], $meta->name, $inversedByMany).PHP_EOL;
            }
            $methods[$assoc['fieldName']] = $insertedMethods;
        }
        return $methods;
    }

    public function GenerateEntityFieldMappingProperties(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->fieldMappings as $fieldMapping)
        {
            $filed = \Core\Internal\MetadataManager::Instance()->GetFieldNameByColumnName($metadata->table['name'], $fieldMapping['columnName']);
            if ($filed != null) continue;

            $lines[] = $this->GenerateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
            $lines[] = $this->_spaces . 'private $' . $fieldMapping['fieldName']
                     . (isset($fieldMapping['default']) ? ' = ' . var_export($fieldMapping['default'], true) : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    private function GenerateFieldMappingPropertyDocBlock(array $fieldMapping, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();
        $lines[] = $this->_spaces . '/**';
        $lines[] = $this->_spaces . ' * @var ' . $fieldMapping['type'] . ' $' . $fieldMapping['fieldName'];

        //if ($this->_generateAnnotations)
        {
            $column = array();
            if (isset($fieldMapping['columnName'])) {
                $column[] = 'name="' . $fieldMapping['columnName'] . '"';
            }

            if (isset($fieldMapping['type'])) {
                $column[] = 'type="' . $fieldMapping['type'] . '"';
            }

            if (isset($fieldMapping['length'])) {
                $column[] = 'length=' . $fieldMapping['length'];
            }

            if (isset($fieldMapping['precision'])) {
                $column[] = 'precision=' .  $fieldMapping['precision'];
            }

            if (isset($fieldMapping['scale'])) {
                $column[] = 'scale=' . $fieldMapping['scale'];
            }

            if (isset($fieldMapping['nullable'])) {
                $column[] = 'nullable=' .  var_export($fieldMapping['nullable'], true);
            }

            if (isset($fieldMapping['columnDefinition'])) {
                $column[] = 'columnDefinition="' . $fieldMapping['columnDefinition'] . '"';
            }

            if (isset($fieldMapping['unique'])) {
                $column[] = 'unique=' . var_export($fieldMapping['unique'], true);
            }

            $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Column(' . implode(', ', $column) . ')';

            if (isset($fieldMapping['id']) && $fieldMapping['id']) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Id';

                $generatorType = $this->GenerateIdGeneratorTypeString($metadata->generatorType);
                if ($generatorType) {
                    $lines[] = $this->_spaces.' * @' . $this->_annotationsPrefix . 'GeneratedValue(strategy="' . $generatorType . '")';
                }

                if ($metadata->sequenceGeneratorDefinition) {
                    $sequenceGenerator = array();

                    if (isset($metadata->sequenceGeneratorDefinition['sequenceName'])) {
                        $sequenceGenerator[] = 'sequenceName="' . $metadata->sequenceGeneratorDefinition['sequenceName'] . '"';
                    }

                    if (isset($metadata->sequenceGeneratorDefinition['allocationSize'])) {
                        $sequenceGenerator[] = 'allocationSize="' . $metadata->sequenceGeneratorDefinition['allocationSize'] . '"';
                    }

                    if (isset($metadata->sequenceGeneratorDefinition['initialValue'])) {
                        $sequenceGenerator[] = 'initialValue="' . $metadata->sequenceGeneratorDefinition['initialValue'] . '"';
                    }

                    $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'SequenceGenerator(' . implode(', ', $sequenceGenerator) . ')';
                }
            }

            if (isset($fieldMapping['version']) && $fieldMapping['version']) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Version';
            }
        }

        $lines[] = $this->_spaces . ' */';

        return implode("\n", $lines);
    }

    private function GenerateIdGeneratorTypeString($type)
    {
        switch ($type) {
            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_AUTO:
                return 'AUTO';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE:
                return 'SEQUENCE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_TABLE:
                return 'TABLE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_IDENTITY:
                return 'IDENTITY';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE:
                return 'NONE';

            default:
                throw new \InvalidArgumentException('Invalid provided IdGeneratorType: ' . $type);
        }
    }

    /**
     * @param ClassMetadata $meta
     */
    private function BuildTable($meta)
    {
        $className = $meta->name;

        $tableName = $className . 'Table';
        if (class_exists($tableName)) return;

        $dir = \Core\Path::Relative('Entities', 'Tables');
        $file = \Core\Path::Combine($dir, $tableName . '.php');

        $replacements = array(
          '<entityName>'        => $className
        );

        $code = str_replace(
            array_keys($replacements),
            array_values($replacements),
            self::$TableClass
        );
        file_put_contents($file, $code);
    }

    /**
     * @param ClassMetadata $meta
     */
    private function ValidateClass($meta)
    {
        $allMethods = true;

        $class = new \ReflectionClass($meta->name);
        $error = array();
        if ($class->getParentClass() == false || $class->getParentClass()->getName() != 'Core\BaseEntity')
            $error[] = 'Класс сущности должен наследоваться от BaseEntity';

        try
        {
            $entity = $class->newInstance();
        } catch (\Exception $exc)
        {
            \Core\DebugPrint($exc, "Error while creating instance of ".$class->getName());
            $name = $class->getName();
            $error[] = "Невозможно создать экземпляр объекта $name. Что Вы там такого понаписали?";
        }

        $tableName = $class->getName() . 'Table';
        if (class_exists($tableName))
        {
            $table = new \ReflectionClass($tableName);
            if ($table->getParentClass() == false || $table->getParentClass()->getName() != 'Core\BaseTable')
                $error[] = "Класс таблицы $tableName должен наследоваться от BaseTable";
        }
        else
        {
            $name = $class->getName();
            $error[] = "Для сущности $name не объявлен класс таблицы. Запустите автогенерацию.";
        }

        foreach ($meta->fieldMappings as $map)
        {
            $name = $map['fieldName'];
            $field = $class->getProperty($name);
            if (!$field->isPrivate())
                $error[] = "Поле $name должно быть объявлено как private";

            if (strpos($name, '_') !== false)
                $error[] = "Полям нужно давать имена без подчеркиваний. Правильное имя для колонки в таблице: 'account_name', имя для поля класса: 'accountName'";

            if (!$class->hasMethod('Get' . \Core\Utils::DashedToCamelCase($name)))
                $allMethods = false;
        }

        foreach ($meta->associationMappings as $assoc)
        {
            $name = $assoc['fieldName'];
            if ($assoc['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY)
            {
                $field = $class->getProperty($name);

                if (!$field->isPrivate())
                    $error[] = "Поле $name должно быть объявлено как private";

                $field->setAccessible(true);
                $val = $field->getValue($entity);
                $ctr = $class->getConstructor() != null;
                if ($val == NULL)
                {
                    $error[] = "Поле $name не инициализировано. Добавте в конструктор класса код: <pre>\$this->$name = new \Doctrine\Common\Collections\ArrayCollection();</pre>";
                    if (!$ctr)
                        $printCtrError = true;
                }
            }

            if (strpos($name, '_') !== false)
                $error[] = "Полям нужно давать имена без подчеркиваний. Правильное имя для колонки в таблице: 'account_name', имя для поля класса: 'accountName'";

            if (!$class->hasMethod('Get' . \Core\Utils::DashedToCamelCase($name)))
                $allMethods = false;
        }

        if($printCtrError)
            $error[] = "И конструктор тоже, кстати, добавить не забудте";

        if (!$allMethods)
            $error[] = "К сожалению, для некоторых полей не объявлены соответствующие им методы. Пожалуйста, запустите автогенерацию.";

        return $error;
    }

    private function CreateMethod(&$methodsArray, $action, $templateName, $fieldName, $typeInfo, $targetType = NULL, $inversedByMany = false)
    {
        if ($action == "Add")
        {
            $addMethod = explode("\\", $typeInfo);
            $addMethod = ucfirst(end($addMethod));
            $methodName = $action . $addMethod;
            $variableName = lcfirst($addMethod);
        } else {
            $methodName = $action . \Core\Utils::DashedToCamelCase($fieldName);
            $variableName = $fieldName;
        }

        $template = self::$$templateName;

        $variableType = $typeInfo ? $typeInfo . ' ' : null;

        $types = \Doctrine\DBAL\Types\Type::getTypesMap();
        $methodTypeHint = $typeInfo && ! isset($types[$typeInfo]) ? '\\' . $typeInfo . ' ' : null;

        if ($inversedByMany)
            $inversedMethod = 'Add'.$targetType;
        else
            $inversedMethod = 'Set'.$targetType;

        $replacements = array(
          '<description>'       => ucfirst($action) . ' ' . $fieldName,
          '<methodTypeHint>'    => $methodTypeHint,
          '<variableType>'      => $variableType,
          '<variableName>'      => $variableName,
          '<methodName>'        => $methodName,
          '<fieldName>'         => $fieldName,
          '<inversedMethod>'    => $inversedMethod
        );

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        $methodsArray[$methodName] = $method;
        $methodsArray = $methodsArray;
    }


    private static $MethodPropGet =
'/**
 * <description>
 * @return <variableType>
 */
public function <methodName>()
{
<tab>return $this-><fieldName>;
}';

    private static $MethodPropSet =
'/**
 * <description>
 * @param <variableType>$<variableName>
 */
public function <methodName>(<methodTypeHint>$<variableName>)
{
<tab>$this-><fieldName> = $<variableName>;
}';
    private static $MethodAssocSet =
'/**
 * <description>
 * @param <variableType>$<variableName>
 * @param bool $bidirectional Применить значение в обоих направлениях (рекомендуется)
 */
public function <methodName>(<methodTypeHint>$<variableName>, $bidirectional = true)
{
<tab>$this-><fieldName> = $<variableName>;
<tab>if ($bidirectional) $<variableName>-><inversedMethod>($this, false);
}';
    private static $MethodAssocAdd =
'/**
 * <description>
 * @param <variableType>$<variableName>
 * @param bool $bidirectional Применить значение в обоих направлениях (рекомендуется)
 */
public function <methodName>(<methodTypeHint>$<variableName>, $bidirectional = true)
{
<tab>$this-><fieldName>[] = $<variableName>;
<tab>if ($bidirectional) $<variableName>-><inversedMethod>($this, false);
}';
    private static $TableClass =
'<?php

class <entityName>Table extends \Core\BaseTable
{
}';

}

?>
