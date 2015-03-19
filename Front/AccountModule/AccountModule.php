<?

namespace Front;

use Core\BaseTable;
use Core\Request;
use Core\Response;

include "../www/Entities/Account.php";

/**
 * AccountModule
 */
class AccountModule extends \Core\Controller
{
    private $genderList = [
        'male' => 'male',
        'female' => 'female'
    ];

    private $errors = [];

    /**
     * @default Index
     */
    function OnIndex()
    {
        $this->Result()->accounts = \AccountTable::GetAll();
    }

    /**
     * @default Create
     */
    function OnCreate()
    {
        $this->Result()->genderList = $this->genderList;
        $request = Request::Instance();
        if($request->IsPost() && $this->isValid($request->Data())){
            $account = new \Account();
            $account->SetLogin($request->Data()->GetStr('login'));
            $account->SetName($request->Data()->GetStr('name'));
            $account->SetGender($request->Data()->GetStr('gender'));
            $account->SetEmail($request->Data()->GetStr('email'));
            $account->SetBirthday($request->Data()->GetDateTime('birthday'));
            $account->SetComment($request->Data()->GetStr('comment'));
            BaseTable::Database()->Persist($account);
            BaseTable::Database()->Flush();

            Response::Redirect(':index');
        }
        $this->Result()->errors = $this->errors;
    }

    /**
     * @default Edit
     * @arguments [%2:int]
     */
    function OnEdit($id)
    {
        $account = \AccountTable::GetItem($id);
        $this->Result()->account = $account;
        $this->Result()->genderList = $this->genderList;
        $request = Request::Instance();
        if($request->IsPost() && $this->isValid($request->Data())){
            $account->SetLogin($request->Data()->GetStr('login'));
            $account->SetName($request->Data()->GetStr('name'));
            $account->SetGender($request->Data()->GetStr('gender'));
            $account->SetEmail($request->Data()->GetStr('email'));
            $account->SetBirthday($request->Data()->GetDateTime('birthday'));
            $account->SetComment($request->Data()->GetStr('comment'));
            BaseTable::Database()->Persist($account);
            BaseTable::Database()->Flush();

            Response::Redirect(':index');
        }
        $this->Result()->errors = $this->errors;
    }

    /**
     * @default Show
     * @arguments [%2:int]
     */
    function OnShow($id)
    {
        $this->Result()->account = \AccountTable::GetItem($id);
    }

    /**
     * @default Delete
     * @arguments [%2:int]
     */
    function OnDelete($id)
    {
        $account = \AccountTable::GetItem($id);
        BaseTable::Database()->Remove($account);
        BaseTable::Database()->Flush();

        Response::Redirect(':index');
    }

    private function isValid($data)
    {
        if(!$data->GetStr('login')){
            $this->errors[] = 'Логин не должен быть пустым';
        }

        if(count($this->errors) > 0){
            return false;
        }

        return true;
    }
}