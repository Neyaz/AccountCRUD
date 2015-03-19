<?

namespace Core\Internal;

/**
 * ClassUpdater
 */
class ClassUpdater
{
    private $reflect;
    private $file;
    private $spaceCount;
    private $lineBreak = "\r\n";
    private $workLine;

    public function __construct($className)
    {
        $this->reflect = new \ReflectionClass($className);
        $this->file = $this->reflect->getFileName();
        $data = file($this->file);
        $this->spaceCount = $this->GetSpacesCount($data, $this->reflect->getStartLine(), $this->reflect->getEndLine());
        $this->workLine = $this->reflect->getEndLine() - 1 - 1; // Счет с нуля а не единицы + берем предыдущую
    }

    public function InsertMethods($methodsArray)
    {
        foreach ($methodsArray as $prop => $methods)
        {
            $ok = false;
            $first = true;
            foreach ($methods as $name => $code)
            {
                $ok |= $this->InsertMethod($name, $code, $first);
                $first = false;
            }
            if ($ok) $this->InsertCode($this->lineBreak, FALSE);
        }
    }

    public function InsertMethod($name, $code, $insertFirstBreak = false)
    {
        if ($this->MethodExist($name))
            return false;
        $this->InsertCode($code.$this->lineBreak, true, $insertFirstBreak);
        return true;
    }

    public function MethodExist($name)
    {
        $name = mb_strtolower($name);
        foreach ($this->reflect->getMethods() as $method)
        {
            if (mb_strtolower($method->name) == $name)
                return true;
        }
        return false;
    }

    public function InsertCode($code, $format = true, $insertFirstBreak = false)
    {
        $data = file($this->file);
        if ($format)
        {
            if ($insertFirstBreak && strlen(trim($data[$this->workLine])) != 0)
                $code = $this->lineBreak.$code;
            $code = $this->Format($code, $this->spaceCount);
        }

        $data = \Core\ArrayTools::InsertArrayAfterPosition($data, $code, $this->workLine);
        $k = count(explode("\n", $code));
        $this->workLine += $k - 1;

        $text = implode('', $data);
        file_put_contents($this->file, $text);
    }

    private function GetSpacesCount($code, $from, $to)
    {
        for ($i = $from; $i <= $to; $i++)
        {
            $txt = $code[$i];
            $p1 = stripos($txt, 'function ');
            $p2 = stripos($txt, 'private ');
            if ($p1 !== FALSE || $p2 !== FALSE)
            {
                $ok = true;
                for ($j = 0; $ok && $j < strlen($txt); $j++)
                {
                    $p = strtolower($txt[$j]);
                    if ($p == '/')
                        $ok = false;
                    else if ($p >= 'a' && $p <= 'z')
                        return $j;
                }
            }
        }
        return 4;
    }

    private function Format($code, $count)
    {
        $spaces = str_repeat(' ', $count);
        $code = str_replace('<tab>', $spaces, $code);

        $lines = explode("\n", $code);
        $result = array();

        foreach ($lines as $value)
        {
            if (strlen(trim($value)) != 0)
                $result[] = $spaces.$value;
            else
                $result[] = $value;
        }

        return implode("\n", $result);
    }
}

?>
