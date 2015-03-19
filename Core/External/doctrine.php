<?
$configFile = realpath(__DIR__).DIRECTORY_SEPARATOR.'DoctrineConfig.php';

require_once $configFile;

\Doctrine\ORM\Tools\Console\ConsoleRunner::run(new \Symfony\Component\Console\Helper\HelperSet($doctrineDefaultHelperSet));
?>