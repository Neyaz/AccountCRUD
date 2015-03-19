<?

namespace Core\Internal;
use \Core\Utils;

/**
 * AnnotationParser
 */
class AnnotationParser
{
    private $annotationsSimple = array('default', 'ajax', 'layout');
    private $annotationsClasses = array('filter');

    private $reflect;

    public function __construct($className)
    {
        $this->reflect = new \ReflectionClass($className);
    }

    public function GetClassAnnotations()
    {
        $doc = $this->reflect->getDocComment();
        return $this->GetAnnotationsFromText($doc);
    }

    public function GetForMethod($name)
    {
        $method = $this->reflect->getMethod($name);
        $doc = $method->getDocComment();
        return $this->GetAnnotationsFromText($doc);
    }

    private function GetAnnotationsFromText($text)
    {
        $lines = explode("\n", $text);

        foreach ($lines as $key => $val)
        {
            $val = trim($val, "\r\t */");
            if ($val != '' && Utils::SubstringLeft($val, 1) == '@')
                $lines[$key] = mb_substr($val, 1);
            else
                unset($lines[$key]);
        }

        $result = array();
        foreach ($lines as $val)
            $this->ParseAnnotation($result, $val);

        return $result;
    }

    private function ParseAnnotation(&$result, $code)
    {
        $main = explode(" ", $code, 2);
        if (count($main) != 2) return false;

        $name = trim($main[0]);
        $val = trim($main[1]);

        if (in_array($name, $this->annotationsSimple))
        {
            $result[$name] = $val;
        }
        else if (in_array($name, $this->annotationsClasses))
        {
            if (!isset($result[$name]))
                $result[$name] = array();
            $this->ParseExtendedAnnotation($result[$name], $val);
        }
        else if ($name == 'arguments')
        {
            $arr = $this->ParseArgumentsAnnotation($val);
            if ($arr != false)
                $result[$name] = $arr;
        }
        else
            return false;

        return true;
    }

    /**
     *
     * @param type $value
     * @return type
     */
    private function ParseArgumentsAnnotation($value)
    {
        $result = array();
        $arr = $this->ExpandParameterValue($value);
        if (!is_array($arr)) return false;

        foreach ($arr as $it)
        {
            $items = $this->SplitToItems($it, ':');
            if (!is_array($items) || count($items) != 2) return false;
            if (Utils::SubstringLeft($items[0], 1) != '%') return false;
            $id = Utils::SubstringMid($items[0], 1);
            $rec = array();
            $rec['source'] = 'after-route-item';
            $rec['value'] = $id;

            $data = $this->SplitToItems($items[1], '=');
            if (count($data) == 1)
            {
                $rec['type'] = $items[1];
                $rec['required'] = true;
            }
            elseif (count($data) == 2)
            {
                $rec['type'] = $data[0];
                $rec['required'] = false;
                $rec['default'] = $this->ExpandParameterValue($data[1]);
            }
            else
                return false;


            $result[] = $rec;
        }
        return $result;
    }

    private function ParseExtendedAnnotation(&$result, $value)
    {
        $data = explode("(", $value, 2);
        $class = trim($data[0]); // class name

        if (count($data) == 1)
        {
            $result[$class] = array();
            return true;
        }

        $params = trim($data[1]);  // initialization params
        if (Utils::SubstringRight($params, 1) == ')')
            $params = Utils::SubstringLeft($params, -1);

        $paramArr = $this->ExtractParameters($params);
        if ($paramArr === false) return false;

        $result[$class] = $paramArr;
        return true;
    }

    private function ExtractParameters($data)
    {
        $result = array();

        $data = $this->SplitToItems($data);
        foreach ($data as $val)
        {
            $arr = explode('=', $val, 2);
            if (count($arr) != 2) return false;
            $arr = \Core\Utils::Trim($arr);
            $result[$arr[0]] = $this->ExpandParameterValue($arr[1]);
        }

        return $result;
    }

    private function ExpandParameterValue($data)
    {
        if (Utils::SubstringLeft($data, 1) == '[' && Utils::SubstringRight($data, 1) == ']')
        {
            $text = mb_substr($data, 1, -1);
            $arr = $this->SplitToItems($text);
            return \Core\Utils::Trim($arr);
        }

        if (Utils::SubstringLeft($data, 1) == '"' && Utils::SubstringRight($data, 1) == '"')
            $data = Utils::SubstringMid($data, 1, mb_strlen($data) - 2);
        else if (Utils::SubstringLeft($data, 1) == "'" && Utils::SubstringRight($data, 1) == "'")
            $data = Utils::SubstringMid($data, 1, mb_strlen($data) - 2);

        return \Core\Utils::Trim($data);
    }

    private function SplitToItems($data, $delimiters = ',;')
    {
        $result = array();

        $quotes = 0;
        $apostrophes = 0;
        $brackets = 0;
        $parentheses = 0;

        $last = 0;
        for ($i = 0; $i < mb_strlen($data); $i++)
        {
            $p = $data[$i];
            if ($p == '"')
                if ($quotes == 0)
                    $quotes++;
                else
                    $quotes--;

            if ($p == '\'')
                if ($apostrophes == 0)
                    $apostrophes++;
                else
                    $apostrophes--;

            if ($p == "[") $brackets++;
            if ($p == "]") $brackets--;
            if ($p == "(") $parentheses++;
            if ($p == ")") $parentheses--;

            $splitter = strpos($delimiters, $p) !== false;

            if ($splitter && $quotes == 0 && $apostrophes == 0 && $brackets == 0 && $parentheses == 0)
            {
                $sub = mb_substr($data, $last, $i - $last);
                $result[] = trim($sub);
                $last = $i+1;
            }
        }

        if ($i > $last)
        {
            $sub = mb_substr($data, $last, $i - $last);
            $result[] = trim($sub);
        }

        return $result;
    }
}

?>
