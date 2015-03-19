<?php

namespace Core\Internal\Doctrine;

class BaseNamingStrategy implements \Doctrine\ORM\Mapping\NamingStrategy
{
    public function classToTableName($className)
    {
        return self::decamelize($className);
    }

    public function propertyToColumnName($propertyName)
    {
        return self::decamelize($propertyName);
    }

    public function referenceColumnName()
    {
        return 'id';
    }

    public function joinColumnName($propertyName)
    {
        return self::decamelize($propertyName) . '_' . $this->referenceColumnName();
    }

    public function joinTableName($sourceEntity, $targetEntity, $propertyName = null)
    {
        return $this->classToTableName($sourceEntity) . '_' . $this->classToTableName($targetEntity);
    }

    public function joinKeyColumnName($entityName, $referencedColumnName = null)
    {
        return $this->classToTableName($entityName) . '_' . ($referencedColumnName ?: $this->referenceColumnName());
    }

    private static function decamelize($str, $glue='_')
    {
        $str = lcfirst($str);
        $new_str  = '';
        $str_len  = strlen($str);

        for ($x = 0; $x < $str_len; ++$x)
        {
            $ascii_val = ord($str[$x]);
            if ($ascii_val >= 65 && $ascii_val <= 90)
            {
                $new_str .= $glue . chr($ascii_val + 32);
            }
            else
            {
                $new_str .= $str[$x];
            }
        }

        return $new_str;
    }

}