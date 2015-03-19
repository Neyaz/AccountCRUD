<?

namespace Core;

class TimeCounter extends Filter
{
    private $started;
    private $finished;
    private $response;

    function BeforeExecute()
    {
        parent::BeforeExecute();
        $this->started = microtime(true);
        return TRUE;
    }

    function AfterExecute()
    {
        $this->finished = microtime(true);
        parent::AfterExecute();
        return TRUE;
    }

    function BeforeResponse()
    {
        $this->response = microtime(true);
        $this->DebugDump();
        parent::BeforeResponse();
        return TRUE;
    }

    function DebugDump()
    {
        $arr = array();
        $arr['execution'] = self::Format($this->ModuleExecutionTime());
        $arr['after'] = self::Format($this->SubModulesExecutionTime());
        DebugPrint($arr, 'Time <i>' . $this->Module()->ModuleTypeName() . '</i>');
    }

    function ModuleExecutionTime()
    {
        return $this->finished - $this->started;
    }

    function SubModulesExecutionTime()
    {
        return $this->response - $this->finished;
    }

    static function Format($val)
    {
        return sprintf('%01.3f ms', $val*1000);
    }
}

?>
