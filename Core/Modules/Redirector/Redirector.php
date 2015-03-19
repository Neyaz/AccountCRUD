<?
class Redirector extends Controller
{
    public $target = '/';

    public function OnIndex()
    {
        Response::Redirect($this->target);
    }

    public function Check()
    {
        return true;
    }
}
?>