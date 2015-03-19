<?

/**
 * Html Helpers
 */
class Html extends Core\Helper
{
    /**
     * Создать форму ссылающуюся на адрес, начальная часть пути для которого определяется автоматичеси.
     * Тип формы по умолчанию - POST
     * @see Подробнее - см. SmartURL
     * @param string $path Путь внутри роутинга\абсолютный
     * @param type $id ID
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function Form($path, $id = NULL, $params = array())
    {
        $url = Url::Make($path);

        $basicParams = array("method"=>"POST", "action"=>$url);
        if ($id != NULL) $basicParams["id"] = $id;

        if(isset($params["upload"]) && $params["upload"]) // Если форма для загрузки файлов
        {
            unset($params["upload"]);
            $params["enctype"] = "multipart/form-data";
        }

        $params = \Core\ArrayTools::Merge($basicParams, $params);
        return '<form ' .\Core\ArrayTools::ArrayToAttributesString($params). '>';
    }

    /**
     * Закрывает форму
     * @return string
     */
    public static function EndForm()
    {
        return '</form>';
    }

    /**
     * Создает чекбокс
     * @param string $name Иимя и ID элемента
     * @param bool $checked Установлен ли флаг по умолчанию?
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function CheckBox($name, $checked, $params = NULL)
    {
        if ($checked)$basicParams = array("checked"=>"checked");
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        return  self::CustomInputBox($name, "checkbox", 1, $params);
    }

    /**
     * Создает чекбокс с пояснительной меткой.
     * @param string $name Иимя и ID элемента
     * @param bool $checked Установлен ли флаг по умолчанию?
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function CheckBoxLabeled($name, $checked, $text, $params = NULL)
    {
        $basicParams = array("id" => $name, 'mode' => 'after');
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        $check = self::CheckBox($name, $checked, $params);
        return self::MakeLabel($check, $params['id'], $text, $params);
    }

    /**
     * Создает радиокнопку.
     * @param string $groupName Имя группы кнопок
     * @param string $value Значение, которе передастся в запросе если буде выбран данный пункт
     * @param bool $checked Установлен ли по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function RadioBox($groupName, $value, $checked, $params = NULL)
    {
        $defId = $groupName.'-'.$value;
        $basicParams = array("id" => $defId);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        if ($checked) $params['checked'] = 'checked';
        unset($params['mode']);
        unset($params['break']);
        return self::CustomInputBox($groupName, 'radio', $value, $params);
    }

    /**
     * Создает радиокнопку с пояснительной меткой.
     * @param string $groupName Имя группы кнопок
     * @param string $value Значение, которе передастся в запросе если будет выбран данный пункт
     * @param bool $checked Установлен ли по умолчанию
     * @param type $text Текст для метки
     * @param type $params Любые HTML параметры
     * @return string
     */
    public static function RadioBoxLabeled($groupName, $value, $checked, $text, $params = NULL)
    {
        $defId = $groupName.'-'.$value;
        $basicParams = array("id" => $defId, 'mode' => 'after');
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        $radio = self::RadioBox($groupName, $value, $checked, $params);
        return self::MakeLabel($radio, $params['id'], $text, $params);
    }

    private function MakeLabel($element, $id, $text, $params)
    {
        $lblStart = ' <label for="'.$id.'">';

        if ($params['mode'] == 'after')
            $rt = $element . $lblStart . $text . '</label>';
        else if ($params['mode'] == 'before')
            $rt = $lblStart . $text . '</label>' . $element;
        else if ($params['mode'] == 'contained')
            $rt = '<label>' . $element . ' ' . $text . '</label>';
        else
            throw new \Exception('invalid mode');

        return $rt;
    }

    /**
     * Создает группу радиокнопок
     * @param string $name Имя группы кнопок
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function RadioList($name, $array, $selectedValue, $params = NULL)
    {
        $basicParams = array('mode' => 'after', 'break' => false);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        if ($params['break'] == true)
            $delim = "<br/>\r\n";
        else
            $delim = "\r\n";

        $array = \Core\ArrayTools::MapArrayIfPossible($array);
        $rt = '';
        $attrib = \Core\Internal\Tools::ProcessListItems($array, $params);
        $selected = \Core\Internal\Tools::FindEqualsItem(array_keys($array), $selectedValue);
        foreach ($array as $val => $text)
        {
            $checked = $val === $selected;
            $rt .= self::RadioBoxLabeled($name, (string)$val, $checked, (string)$text, $params).$delim;
        }
        return $rt;
    }

    /**
     * Создает группу радиокнопок c пояснительной пометкой
     * @param string $name Имя группы кнопок
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param type $text Текст для метки
     * @param type $params Любые HTML параметры
     * @return string
     */
    public static function RadioListLabeled($name, $array, $selectedValue, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label>'.$text."</label><br/>\r\n";
        $rt .= self::RadioList($name, $array, $selectedValue, $size, $params);
        return $rt;
    }

    /**
     * Создает тег <select>
     * @param string $name Имя и id у тега <select>
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param int $size Количество отображаемых элементов
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function ListBox($name, $array, $selectedValue, $size = 5, $params = NULL)
    {
        if(!is_array($selectedValue))
            $selectedValue = array($selectedValue);

        $basicParams = array('name' => $name, 'id' => $name, 'size' => $size);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        $array = \Core\ArrayTools::MapArrayIfPossible($array);
        $attrib = \Core\Internal\Tools::ProcessListItems($array, $params);
        $rt = '<select ';
        $rt .= $attrib;
        $rt .= '>';
        foreach ($array as $val => $text)
        {
            $selected = false;
            foreach($selectedValue as $key=>$sVal)
            {
                $selected = \Core\Internal\Tools::FindEqualsItem(array_keys($array), $sVal);;
                if($selected !== false)
                {
                    if($selected === $val)
                        break;
                }
            }
            $rt .= '<option value="'.(string)$val.'"';
            if ($val === $selected) $rt .= ' selected="selected"';
            $rt .= '>'.(string)$text.'</option>';
        }
        $rt .= '</select>';
        return $rt;
    }

    /**
     * Создает тег <select> c пояснительной пометкой
     * @param string $name Имя и id у тега <select>
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param int $size Количество отображаемых элементов
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function ListBoxLabeled($name, $array, $selectedValue, $size = 5, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text."</label><br/>\r\n";
        $rt .= self::ListBox($name, $array, $selectedValue, $size, $params);
        return $rt;
    }

    /**
     * Создает внизспадающий список
     * @param string $name Имя и id у тега <select>
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param array $params Любые HTML параметры
     * @return type
     */
    public static function ComboBox($name, $array, $selectedValue, $params = NULL)
    {
        $rt = self::ListBox($name, $array, $selectedValue, 1, $params);
        return $rt;
    }

    /**
     * Создает внизспадающий список c пояснительной пометкой
     * @param string $name Имя и id у тега <select>
     * @param array $array Массив с данными: Ключ массив передасться в запросе при выборе пункта. Значение массива будет выведено в текстовую метку
     * @param type $selectedValue
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function ComboBoxLabeled($name, $array, $selectedValue, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text.'</label> ';
        $rt .= self::ComboBox($name, $array, $selectedValue, $params);
        return $rt;
    }

    /**
     * Создает текстовое поле
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function TextBox($name, $value = '', $params = NULL)
    {
        return self::CustomInputBox($name, "text", $value, $params);
    }

    /**
     * Создает текстовое поле c пояснительной пометкой
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function TextBoxLabeled($name, $value, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text.'</label> ';
        $rt .= self::TextBox($name, $value, $params);
        return $rt;
    }

    /**
     * Создает поле для ввода электронной почты
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function EmailBox($name, $value, $params = NULL)
    {
        return self::CustomInputBox($name, "email", $value, $params);
    }

    /**
     * Создает поле для ввода электронной почты c пояснительной пометкой
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function EmailBoxLabeled($name, $value, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text.'</label> ';
        $rt .= self::EmailBox($name, $value, $params);
        return $rt;
    }

    /**
     * Создает поле для ввода пароля
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function PasswordBox($name, $value = '', $params = NULL)
    {
        return self::CustomInputBox($name, "password", $value, $params);
    }

    /**
     * Создает поле для ввода пароля c пояснительной пометкой
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function PasswordBoxLabeled($name, $value, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text.'</label> ';
        $rt .= self::PasswordBox($name, $value, $params);
        return $rt;
    }

    /**
     * Создает текстовое поле
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function DateTimeBox($name, $value = '', $params = NULL)
    {
        return self::CustomInputBox($name, "date", $value, $params);
    }

    /**
     * Создает текстовое поле c пояснительной пометкой
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function DateTimeBoxLabeled($name, $value, $text, $params = NULL)
    {
        if ($text != '') $rt = '<label for="'.$name.'">'.$text.'</label> ';
        $rt .= self::DateTimeBox($name, $value, $params);
        return $rt;
    }

    /**
     * Создает поле для выбора файла с компьютера
     * @param string $name Имя элемента
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function SelectFileBox($name, $params = NULL)
    {
        return self::CustomInputBox($name, "file", '', $params);
    }

    /**
     * Создает скрытое поле
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function HiddenValue($name, $value, $params = NULL)
    {
        return self::CustomInputBox($name, "hidden", $value, $params);
    }

    /**
     * Создает кнопку-картинку для отправки формы
     * @param string $name Имя и id кнопки
     * @param string $src Адрес изображения
     * @param int $width Ширина кнопки
     * @param int $height Высота кнопки
     * @param string $alt Альтернативный текст
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function ImageInputBox($name, $src, $width = NULL, $height = NULL, $alt = NULL, $params = NULL)
    {
        if ($alt === NULL)
        {
            $namearr = pathinfo($src);
            $alt = $namearr['filename'];
        }
        $basicParams = array("src"=>$src, "border"=>0, "alt"=>$alt);
        if ($width !== NULL) $basicParams["width"] = $width;
        if ($height !== NULL) $basicParams["height"] = $height;
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        return  self::CustomInputBox($name, "image", NULL, $params);
    }

    /**
     * Создает форму отправки формы
     * @param string $text Текст кнопки
     * @param string $name Имя и id кнопки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function SubmitButton($text = 'OK', $name='submit', $params = NULL)
    {
        return self::CustomInputBox($name, "submit", $text, $params);
    }

    /**
     * Создает скрытое поле
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function TextArea($name, $value, $params = array())
    {
        $basicParams = array('name' => $name, "id" => $name);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        $rt = '<textarea ';
        $rt .= \Core\ArrayTools::ArrayToAttributesString($params);
        $rt .= '>';
        $rt .= $value;
        $rt .= '</textarea>';
        return $rt;
    }

    /**
     * Создает скрытое поле c пояснительной пометкой
     * @param string $name Имя элемента
     * @param string $value Значение по умолчанию
     * @param string $text Текст для метки
     * @param array $params Любые HTML параметры
     * @return string
     */
    public static function TextAreaLabeled($name, $value, $text, $params = NULL)
    {
        $basicParams = array('break' => false);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        if ($params['break'] == true)
            $delim = "<br/>\r\n";
        else
            $delim = "\r\n";

        if ($text != '') $rt = '<label for="' . $name . '">' . $text . "</label>" . $delim;
        $rt .= self::TextArea($name, $value, $params);
        return $rt;
    }

    /**
     * Принимает через запятую набор картинок, которые должны быть предзагружены
     * @param string1
     * @param string2...
     * @param string3...
     */
    public static function PreloadImages()
    {
        $images = func_get_args();

        $rt = '<script type="text/javascript">';
        foreach ($images as $img)
        {
            $rt .= 'var tmp=new Image(); tmp.src="' . $img . '";';
        }
        $rt .= '</script>';
        return $rt;
    }

    /**
     * Гиперссылка, автоматичеси определяющая начальную часть пути<br/>
     * @see Подробнее - см. SmartURL
     * @param string $path Путь внутри роутинга\абсолютный
     * @param string $text Текст ссылки
     * @param array $params Набор HTML атрибутов ссылки
     * @return string
     */
    public static function Link($path, $text = "", $params = NULL)
    {
        $url = Url::Make($path);
        if($url == '')
            return '';
        if ($text == '' && $text !== 0)
            $text = $url;
        $rt = '<a href="' . $url . '" ';
        $rt .= \Core\ArrayTools::ArrayToAttributesString($params);
        $rt .= '>' . $text . '</a>';
        return $rt;
    }

    /**
     * Функция для генерации тегов <input> разных типов
     * @param string $name
     * @param string $type
     * @param string $value
     * @param array $params
     * @return string
     */
    public static function CustomInputBox($name, $type, $value, $params = NULL)
    {
        $basicParams = array("type"=>$type, "name"=>$name, "id"=>$name, "value"=>$value);
        $params = \Core\ArrayTools::Merge($basicParams, $params);

        return '<input ' . \Core\ArrayTools::ArrayToAttributesString($params) . ' />';
    }
}

?>