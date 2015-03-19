<?

namespace Core;

/**
 * Database
 */
class Database
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    private static $mainManager = null;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    static function Main()
    {
        global $doctrineDefaultHelperSet;
        if (self::$mainManager != null)
            return self::$mainManager;

        if (!defined('DOCTRINE_LOADED'))
            require_once Path::Relative('Core', 'External', 'DoctrineConfig.php');
        self::$mainManager = $doctrineDefaultHelperSet['em']->getEntityManager();
        return self::$mainManager;
    }
}

?>
