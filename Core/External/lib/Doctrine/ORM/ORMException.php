<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM;

use Exception;

/**
 * Base exception class for all ORM exceptions.
 *
 * @author Roman Borschel <roman@code-factory.org>
 * @since 2.0
 */
class ORMException extends Exception
{
    /**
     * @return ORMException
     */
    public static function MissingMappingDriverImpl()
    {
        return new self("It's a requirement to specify a Metadata Driver and pass it ".
            "to Doctrine\\ORM\\Configuration::setMetadataDriverImpl().");
    }

    /**
     * @param string $queryName
     *
     * @return ORMException
     */
    public static function NamedQueryNotFound($queryName)
    {
        return new self('Could not find a named query by the name "' . $queryName . '"');
    }

    /**
     * @param string $nativeQueryName
     *
     * @return ORMException
     */
    public static function NamedNativeQueryNotFound($nativeQueryName)
    {
        return new self('Could not find a named native query by the name "' . $nativeQueryName . '"');
    }

    /**
     * @param object $entity
     * @param object $relatedEntity
     *
     * @return ORMException
     */
    public static function EntityMissingForeignAssignedId($entity, $relatedEntity)
    {
        return new self(
            "Entity of type " . get_class($entity) . " has identity through a foreign entity " . get_class($relatedEntity) . ", " .
            "however this entity has no identity itself. You have to call EntityManager#persist() on the related entity " .
            "and make sure that an identifier was generated before trying to persist '" . get_class($entity) . "'. In case " .
            "of Post Insert ID Generation (such as MySQL Auto-Increment or PostgreSQL SERIAL) this means you have to call " .
            "EntityManager#flush() between both persist operations."
        );
    }

    /**
     * @param object $entity
     * @param string $field
     *
     * @return ORMException
     */
    public static function EntityMissingAssignedIdForField($entity, $field)
    {
        return new self("Entity of type " . get_class($entity) . " is missing an assigned ID for field  '" . $field . "'. " .
            "The identifier generation strategy for this entity requires the ID field to be populated before ".
            "EntityManager#persist() is called. If you want automatically generated identifiers instead " .
            "you need to adjust the metadata mapping accordingly."
        );
    }

    /**
     * @param string $field
     *
     * @return ORMException
     */
    public static function UnrecognizedField($field)
    {
        return new self("Unrecognized field: $field");
    }

    /**
     * @param string $className
     * @param string $field
     *
     * @return ORMException
     */
    public static function InvalidOrientation($className, $field)
    {
        return new self("Invalid order by orientation specified for " . $className . "#" . $field);
    }

    /**
     * @param string $mode
     *
     * @return ORMException
     */
    public static function InvalidFlushMode($mode)
    {
        return new self("'$mode' is an invalid flush mode.");
    }

    /**
     * @return ORMException
     */
    public static function EntityManagerClosed()
    {
        return new self("The EntityManager is closed.");
    }

    /**
     * @param string $mode
     *
     * @return ORMException
     */
    public static function InvalidHydrationMode($mode)
    {
        return new self("'$mode' is an invalid hydration mode.");
    }

    /**
     * @return ORMException
     */
    public static function MismatchedEventManager()
    {
        return new self("Cannot use different EventManager instances for EntityManager and Connection.");
    }

    /**
     * @param string $methodName
     *
     * @return ORMException
     */
    public static function FindByRequiresParameter($methodName)
    {
        return new self("You need to pass a parameter to '".$methodName."'");
    }

    /**
     * @param string $entityName
     * @param string $fieldName
     * @param string $method
     *
     * @return ORMException
     */
    public static function InvalidFindByCall($entityName, $fieldName, $method)
    {
        return new self(
            "Entity '".$entityName."' has no field '".$fieldName."'. ".
            "You can therefore not call '".$method."' on the entities' repository"
        );
    }

    /**
     * @param string $entityName
     * @param string $associationFieldName
     *
     * @return ORMException
     */
    public static function InvalidFindByInverseAssociation($entityName, $associationFieldName)
    {
        return new self(
            "You cannot search for the association field '".$entityName."#".$associationFieldName."', ".
            "because it is the inverse side of an association. Find methods only work on owning side associations."
        );
    }

    /**
     * @return ORMException
     */
    public static function InvalidResultCacheDriver()
    {
        return new self("Invalid result cache driver; it must implement Doctrine\\Common\\Cache\\Cache.");
    }

    /**
     * @return ORMException
     */
    public static function NotSupported()
    {
        return new self("This behaviour is (currently) not supported by Doctrine 2");
    }

    /**
     * @return ORMException
     */
    public static function QueryCacheNotConfigured()
    {
        return new self('Query Cache is not configured.');
    }

    /**
     * @return ORMException
     */
    public static function MetadataCacheNotConfigured()
    {
        return new self('Class Metadata Cache is not configured.');
    }

    /**
     * @return ORMException
     */
    public static function ProxyClassesAlwaysRegenerating()
    {
        return new self('Proxy Classes are always regenerating.');
    }

    /**
     * @param string $entityNamespaceAlias
     *
     * @return ORMException
     */
    public static function UnknownEntityNamespace($entityNamespaceAlias)
    {
        return new self(
            "Unknown Entity namespace alias '$entityNamespaceAlias'."
        );
    }

    /**
     * @param string $className
     *
     * @return ORMException
     */
    public static function InvalidEntityRepository($className)
    {
        return new self("Invalid repository class '".$className."'. It must be a Doctrine\Common\Persistence\ObjectRepository.");
    }

    /**
     * @param string $className
     * @param string $fieldName
     *
     * @return ORMException
     */
    public static function MissingIdentifierField($className, $fieldName)
    {
        return new self("The identifier $fieldName is missing for a query of " . $className);
    }

    /**
     * @param string $functionName
     *
     * @return ORMException
     */
    public static function OverwriteInternalDQLFunctionNotAllowed($functionName)
    {
        return new self("It is not allowed to overwrite internal function '$functionName' in the DQL parser through user-defined functions.");
    }
}
