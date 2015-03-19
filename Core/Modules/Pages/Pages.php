<?

namespace Core;

class Pages extends Controller
{
    private $page = NULL;

    /**
     * @default TEXT
     * @ajax TEXT
     */
    public function OnIndex()
    {
        $this->LoadPage($this->page);
    }

    public function PrepareRequest()
    {
        $this->page = $this->FindPage();
    }

    public function Check()
    {
        return $this->page != NULL;
    }

    private function LoadPage($path)
    {
        if (!Path::FileExist($path))
        {
            if (!$this->Route()->HaveItem('error'))
                Response::Error404();
            else
            {
                $code = $this->Route()->GetInt('error');
                \Core\Internal\Tools::Error("Все плохо, мы хотели показать ошибку $code но даже она не найдена :( \n\nСоздайте папку Pages/error/{code}.html");
            }
        }

        $file = file_get_contents($path);
        $splitPos = strpos($file, '---');
        if ($splitPos !== false)
        {
            $bodyPos = strpos($file, "\n", $splitPos);
            $head = substr($file, 0, $splitPos);
            $body = substr($file, $bodyPos);
        }
        else
        {
            $body = $file;
            $head = '';
        }
        $this->Result()->SetTextData($body);

        $arr = explode("\n", $head);
        foreach ($arr as $it)
        {
            $p = strpos($it, '=');
            if ($p!==false)
            {
                $name = substr($it, 0, $p);
                $value = str_replace("\r", '', substr($it, $p+1));
                $this->LayoutData()->SetItem($name, $value); // title, description, keywords ....
            }
        }
    }

    private function FindPage()
    {
        $name = Request::Instance()->PathAfterRoute();
        $name = str_replace(array('..', '\\', '/', '@'), '', $name);

        if ($this->Route()->HaveItem('error'))
        {
            $code = $this->Route()->GetInt('error');
            $name = "error/$code";
        }

        // Apply config
        if (isset($this->dir) && $this->dir!='')
            $name = $this->dir.'/'.$name;

        // Seporator
        $name = str_replace('/', DIRECTORY_SEPARATOR, $name);

        if(Config::Instance()->GetBool('localization'))
        {
            $base = Path::Relative('Pages', User::Instance()->GetLocale());
            if(!Path::FileExist($base))
                $base = Path::Relative('Pages', Config::Instance()->GetStr('default/locale'));
            Internal\Tools::Assert(Path::FileExist($base), 'Включена локализация приложений. Для данного роутинга требуется папка: '. $base);
        }
        else
            $base = Path::Relative('Pages');

        $path = Path::Combine($base, $name.'.html');
        if (Path::FileExist($path))
            return $path;

        $path = Path::Combine($base, $name.'.htm');
        if (Path::FileExist($path))
            return $path;

        $path = Path::Combine($base, $name, 'index.html');
        if (Path::FileExist($path))
            return $path;

        $path = Path::Combine($base, $name, 'index.htm');
        if (Path::FileExist($path))
            return $path;

        return NULL;
    }
}
?>