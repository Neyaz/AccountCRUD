<?php

namespace Core\Service;

require_once("Mail/PHPMailer.php");

/**
 * Отправка письма
 */
class Mail
{
    private $phpMailer;

    private $defaultConfig = array(
        "need_auth" => false,
        "debug" => false,
        "host" => "mail.titanhost.ru"
    );

    private $canSend = false;

    private $isSMTP = false;

    private $template = null;

    private $locale = null;


    function __construct($isSMTP = false)
    {
        $this->isSMTP = $isSMTP;

        $config = \Core\Config::Instance();
        $configData = $config->GetData("mails");

        $this->phpMailer = new \Core\Service\MailService\PHPMailer();
        $this->phpMailer->CharSet = "UTF-8";
        $this->phpMailer->Subject = "Новое сообщение";

        if($isSMTP)
        {
            $this->phpMailer->IsSMTP();
            $this->phpMailer->SMTPAuth = true;
            if($configData["need_auth"])
                $this->SetFromAccount("default");
            else
                $this->phpMailer->Host = $this->defaultConfig["host"];
        }

        $this->SetItem("site_domain", \Core\Request::Instance()->DomainName());
    }

    private $__data = array();

    public function SetFromAccount($account)
    {
        if(!$this->isSMTP)
            return false;

        $config = \Core\Config::Instance();
        $profile = $config->GetData("mails/accounts/".$account, null);
        if($profile == null)
        {
            \Core\Internal\Tools::Error("В файле конфигурации не найден указанный аккаунт:" . $account);
        }
        $this->phpMailer->Host = $profile["host"];
        $this->phpMailer->Port = $profile["port"];
        $this->phpMailer->Username = $profile["username"];
        $this->phpMailer->Password = $profile["password"];
        $this->phpMailer->SMTPSecure = isset($profile["secure"]) ? $profile["secure"] : "";

        $fromEmail = $profile["address"];
        $fromName = $profile["name"];
        $this->phpMailer->SetFrom($fromEmail, $fromName);
        $this->phpMailer->ClearReplyTos();
        $this->phpMailer->AddReplyTo($fromEmail, $fromName);

        $this->canSend = true;
    }

    public function SetFrom($fromMail, $fromName = NULL)
    {
        if ($fromName == NULL)
            $fromName = $fromMail;

        $this->phpMailer->SetFrom($fromMail, $fromName);

        return $this;
    }

    public function SetTo($toMail, $toName = NULL)
    {
        if ($toName == NULL)
            $toName = $toMail;


        $this->phpMailer->ClearAddresses();
        $this->phpMailer->AddAddress($toMail, $toName);

        return $this;
    }

    public function SetSubject($subject)
    {
        $this->phpMailer->Subject = $subject;
        return $this;
    }

    public function  __set($name,  $value)
    {
        //echo "SET ".$name. " = ". $value."<br>";
        $this->__data[$name] = $value;
        //echo "CHECK ". $name . " = ". $this->$name."<br/>";
        if($name == "to_mail")
            $this->SetTo($this->to_mail, $this->to_name);

        if($name == "to_name")
        {
            if($this->to_mail)
                $this->SetTo($this->to_mail, $this->to_name);
        }

        if($name == "from_mail" && !$this->isSMTP)
                $this->SetFrom($this->from_mail, $this->from_name);

        if($name == "from_name")
        {
            if($this->from_mail && !$this->isSMTP)
                $this->SetFrom($this->from_mail, $this->from_name);
        }

        if($name == "subject")
            $this->SetSubject($this->subject);
    }
    public function  __get($name)
    {

        return $this->__data[$name];
    }



    /**
     * Послать e-mail сообщение с ручной установкой параметров.
     * @param $body Тест письма
     */
    private function SendMail($body = "", $altBody = null)
    {
        if($this->isSMTP && !$this->canSend)
            \Core\Internal\Tools::Error("Установите SMTP-аккаунт перед отправкой почты");

        $tag = \Core\Config::Instance()->GetStr("mails/tag");
        if($tag)
        {
            $this->phpMailer->Tags = $tag;
            $this->phpMailer->AddCustomHeader('X-MC-Tags:'. $tag);
        }

        if ($this->phpMailer->Subject  == '')
            $this->phpMailer->Subject  = 'Новое сообщение';

        if($altBody != null)
            $this->phpMailer->AltBody = $altBody;
        $this->phpMailer->Body = $body;

        return $this->phpMailer->Send();
    }

    /**
     * Отправить письмо с заданным шаблоном
     * @param string $template файл шаблона
     */
    public function Send($template=null)
    {
    	if (empty($template) && !empty($this->template)) {
    		$template = $this->template;
		}

        $base = \Core\Path::Relative('Templates');
        if(\Core\Config::Instance()->GetBool('localization'))
        {
            if($this->locale == null)
                $this->locale = \Core\User::Instance()->GetLocale();

            $dir = \Core\Path::Combine($base, $this->locale);
            if(!\Core\Path::FileExist($dir))
                $dir = \Core\Path::Combine($base, \Core\Config::Instance()->GetStr('default/locale'));
            \Core\Internal\Tools::Assert(\Core\Path::FileExist($dir), 'Включена локализация, требуется папка ' . $dir);
        }
        else
            $dir = $base;

        $template = \Core\Path::Combine(explode('/', $template));

        $pathText = \Core\Path::Combine($dir, $template.'.txt');
        $text = $this->LoadData($pathText);

        $pathHtml = \Core\Path::Combine($dir, $template.'.html');
        $html = $this->ParseHtml($pathHtml);

        if($text == "" && $html == "")
            \Core\Internal\Tools::Error("Файл шаблона $pathText или $pathHtml не найден :(");

        if($html != "")
        {
            $this->SendAsHtml(true);
            if($text != "")
                return $this->SendMail($html, $text);
            else
                return $this->SendMail($html);
        }
        return $this->SendText($text);
    }

    /**
     * Отправить письмо задав текстовое содержимое
     * @param string $text файл шаблона
     */
    public function SendText($text)
    {
        return $this->SendMail($text);
    }

    public function AddAddress($email, $name = '')
    {
        $this->phpMailer->AddAddress($email, $name);
    }

    public function AddBCC($email, $name = '')
    {
        $this->phpMailer->AddBCC($email, $name);
    }

    public function SetItems($data = array())
    {
        foreach($data as $key => $item)
            $this->SetItem($key, $item);
    }

    public function SetItem($name, $value)
    {
        $this->__set($name, $value);
    }

    public function AddCC($email, $name = '')
    {
        $this->phpMailer->AddCC($address, $name);
    }

    public function AddAttachment($path, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
    {
        $this->phpMailer->AddAttachment($path, $name, $encoding, $type);
    }

    public function AddReplyTo($address, $name = '')
    {
        $this->phpMailer->AddReplyTo($address, $name);
    }

    public function ClearReplyTos()
    {
        $this->phpMailer->ClearReplyTos();
    }

    public static function ValidateEmail($email)
    {
        return \Core\Service\MailService\PHPMailer::ValidateAddress($email);
    }

    public function SendAsHtml($isHtml = true)
    {
        $this->phpMailer->IsHTML($isHtml);
    }

    public function AddEmbeddedImage($path, $cid, $name)
    {
        $this->phpMailer->IsHTML(true);
        $this->phpMailer->AddEmbeddedImage($path, $cid, $name);
    }

    private function LoadData($path)
    {
        $text = "";
        if (\Core\Path::FileExist($path))
        {
            $text = file_get_contents($path);

            foreach ($this->__data as $k => $v)
                $text = str_replace("%$k%", $v, $text);
        }
        return $text;
    }

    private function ParseHtml($path)
    {

        $html = $this->LoadData($path);

        $splitPos = mb_strpos($html, '---');
        if ($splitPos !== false)
        {
            $bodyPos = strpos($html, "\n", $splitPos);
            $head = \Core\Utils::SubstringLeft($html, $splitPos);
            $body = \Core\Utils::SubstringMid($html, $bodyPos);
        }
        else
        {
            $body = $html;
            $head = '';
        }

        $arr = explode("\n", $head);
        foreach ($arr as $it)
        {
            $p = explode("=", $it);
            if (count($p) > 1)
            {
                $imagePath = explode("/", $p[1]);
                $imagePath = \Core\Path::Combine($imagePath);
                $imagePath = \Core\Path::Relative($imagePath);
                $this->AddEmbeddedImage($imagePath, $p[0], $p[0]);
            }
        }

        return $body;
    }

    public function SetSecure($type)
    {
        $this->phpMailer->SMTPSecure = $type;
    }

    public function SetTemplate($template)
    {
        $this->template = $template;
    }

    public function SetLocale($locale)
    {
        $this->locale = $locale;
    }
}
?>
