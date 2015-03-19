<?
include_once 'SymfonyConfig.php';

$baseDir = dirname(dirname(realpath(__DIR__))).DIRECTORY_SEPARATOR;
$curPath = realpath(__DIR__).DIRECTORY_SEPARATOR;
$doctrineLibs = $baseDir.'Core'.DIRECTORY_SEPARATOR.'Internal'.DIRECTORY_SEPARATOR.'Doctrine'.DIRECTORY_SEPARATOR;

include_once $baseDir.DIRECTORY_SEPARATOR.'Core'.DIRECTORY_SEPARATOR.'BaseEntity.php';

$libraryLoad = new \Doctrine\Common\ClassLoader('DoctrineExtensions', $doctrineLibs);
$libraryLoad->register();

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(array($baseDir."Entities")));

$config->setProxyDir($baseDir."Entities".DIRECTORY_SEPARATOR.'Proxies');
$config->setProxyNamespace('Proxies');

//require_once dirname(realpath(__DIR__)).DIRECTORY_SEPARATOR.'Internal'.DIRECTORY_SEPARATOR.'Doctrine'.DIRECTORY_SEPARATOR .'BaseNamingStrategy.php';
//$config->SetNamingStrategy(new \Core\Internal\Doctrine\BaseNamingStrategy());

// MySQL Functions
$config->addCustomNumericFunction("DEGREES", 'DoctrineExtensions\Query\Mysql\Degrees');
$config->addCustomStringFunction("RANDOM", 'DoctrineExtensions\Query\Mysql\Random');
$config->addCustomDatetimeFunction("DATE", 'DoctrineExtensions\Query\Mysql\Date');
$config->addCustomDatetimeFunction("YEAR", 'DoctrineExtensions\Query\Mysql\Year');
$config->addCustomDatetimeFunction("MONTH", 'DoctrineExtensions\Query\Mysql\Month');
$config->addCustomStringFunction("IF", 'DoctrineExtensions\Query\Mysql\IfElse');

$database = \Core\Config::Instance()->GetData('database');

if(isset($database['prod'], $database['dev']))
    if(defined('TEST_SERVER'))
        $connectionOptions = $database['test'];
    else
        $connectionOptions = $database[\Core\Config::Instance()->GetStr("mode")];
else
    $connectionOptions = $database;

if (!isset($connectionOptions['driver'])) $connectionOptions['driver'] = 'pdo_mysql';
$connectionOptions['charset'] = 'utf8'; // Кроме UTF-8 мы ничего не поддерживаем принципиально :)

$production = !\Core\Config::Instance()->IsDebug();

if ($production)
{
    $config->setAutoGenerateProxyClasses(false);
    $cache = new \Doctrine\Common\Cache\ArrayCache;
    //$cache = new \Doctrine\Common\Cache\ApcCache;
}
else
{
    $config->setAutoGenerateProxyClasses(true);
    $cache = null;
}

if ($cache != null)
{
    $config->setQueryCacheImpl($cache);
    $config->setMetadataCacheImpl($cache);
}

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

// UTF8 для MySQL
if ($connectionOptions['driver'] == 'pdo_mysql')
    $em->getEventManager()->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit('utf8', 'utf8_general_ci'));


global $doctrineDefaultHelperSet;
$doctrineDefaultHelperSet = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
);


?>