<?
$curPath = realpath(__DIR__).DIRECTORY_SEPARATOR;
$libs = $curPath.'lib'.DIRECTORY_SEPARATOR;
$loader = $libs.'Doctrine'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'ClassLoader.php';

require_once $loader;

$classLoader = new \Doctrine\Common\ClassLoader('Symfony', $libs);
$classLoader->register();
?>
