<?

namespace Front;

use \Core\LiteWork,
    \Core\Database,
    \Core\User,
    \Core\Response;

/**
 * Test Module
 */
class Test extends \Core\Controller
{
    /**
     * @default ViewIndex
     */
    function OnIndex()
    {
    }

    /**
     * @default ViewCheck
     * @ajax JSON
     * @arguments [%2:int]
     */
    function OnCheck($param)
    {
        $this->Result()->data = $param;
    }
}

?>
