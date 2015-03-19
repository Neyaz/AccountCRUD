<?php

namespace DoctrineExtensions\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class GuidType extends Type
{
    const GUID = 'guid';

    public function getName()
    {
        return self::GUID;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'BINARY(16)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return '0x'.bin2hex($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if(is_string($value))
        {
            if(preg_match("/^0x[0-9a-fA-F]{32}$/", $value))
                return pack('H*', substr($value, 2));
            else 
                return $value;
        }
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return $sqlExpr;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return $sqlExpr;
    }
}
