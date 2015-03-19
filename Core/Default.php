<?
namespace Core;


try
{
    require_once 'LiteWork.php';
    LiteWork::Instance()->Initialize();
}
catch (LiteWorkInitializationException $e)
{
    Internal\Runtime::ExecuteErrorMode();
}
LiteWork::Instance()->Execute();

?>
