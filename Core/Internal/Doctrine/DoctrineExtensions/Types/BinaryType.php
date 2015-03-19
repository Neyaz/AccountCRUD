<?php

namespace DoctrineExtensions\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class BinaryType extends Type
{
    const BINARY = 'binary';

    public function getName()
    {
        return self::BINARY;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'BINARY';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return bin2hex($value);
    }
}
