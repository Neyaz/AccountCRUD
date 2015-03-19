<?

namespace Core;

/**
 * Управление доступом пользователя к путям роутинга
 */
class Authorize extends Filter
{
    private $roles;
    private $redirect = NULL;

    public function __construct()
    {
    }

    public function OnInit()
    {
        $this->roles = $this->GetConfigItem('roles');
        $redirect = $this->GetConfigItem('redirect');
        
        if($redirect == "%referer%")
            $redirect = Request::Instance()->Referer();

        
        $this->redirect = $redirect;
        \Core\Internal\Tools::Assert(is_array($this->roles), "Filter->roles должно быть массивом. Проверьте роутинг. Пример:\n\nroles: [user, admin]");
    }

    public function BeforeExecute()
    {
        $ok = FALSE;
        $user = User::Instance()->GetRole();
        foreach ($this->roles as $val)
        {
            if(\Core\Utils::SubstringLeft($val, 1) == "!")
            {
                $ok = true;
                $val = \Core\Utils::SubstringMid($val, 1);
                if(strcasecmp($val, $user) == 0)
                  $this->OnNotAuthorized();
            }
            else
                $ok |= strcasecmp($val, $user) == 0;

        }

        if (!$ok)
            $this->OnNotAuthorized();

        return TRUE;
    }

    /**
     * Перегружаемая функция. Вызывается, если попытка авторизации прошла неудачно.
     */
    public function OnNotAuthorized()
    {
        User::Instance()->SetLastPath();
        if ($this->redirect != NULL)
            Response::Redirect($this->redirect);
        else
            Response::Error403();
    }

    public function AddRole($role)
    {
        $this->roles[] = $role;
    }
}

?>
