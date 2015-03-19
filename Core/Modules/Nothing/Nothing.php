<?

namespace Core;

class Nothing extends Controller
{
    /**
     * @default TEXT
     */
    public function OnIndex()
    {
    }

    public function Check()
    {
        return true;
    }
}