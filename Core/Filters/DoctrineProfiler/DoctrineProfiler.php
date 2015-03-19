<?

namespace Core;

/**
 * Отображение информации о запросах
 */
class DoctrineProfiler extends Filter
{
    private $logger;

    public function __construct()
    {
    }

    public function OnInit()
    {
        require_once dirname(dirname(realpath(__DIR__))).DIRECTORY_SEPARATOR.'Internal'.DIRECTORY_SEPARATOR.'Doctrine'.DIRECTORY_SEPARATOR .'Profiler.php';

        $this->logger = new \Core\Internal\Doctrine\Profiler();

        \Core\Database::Main()->GetConfiguration()->setSQLLogger($this->logger);

        return TRUE;
    }

    public function BeforeResponse()
    {
        $logger = $this->logger->PrintInfo();
        $title = "Запросы к БД";
        if(count($logger)>5)
            $title="<b>".$title."</b>";

        \Core\DebugPrint($logger,$title,'Запросы к БД('.count($logger).')');
        parent::BeforeResponse();
        return TRUE;
    }

}

?>
