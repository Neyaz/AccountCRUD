<?php

namespace Core\Service;

require_once "RSS/RSSChannel.php";
require_once "RSS/RSSItem.php";

/**
 * Создание RSS
 */
class RSS
{
    private $defaultConfig = array(
        'title' => 'RSS',
        'description' => 'RSS',
        'link' => 'http://66bit.ru',
        'count' => 20
    );

    private $rssChannel;
    private $conversion = null;

    function __construct()
    {
        $config = \Core\Config::Instance();
        $this->configData = $config->GetData("rss", $this->defaultConfig);
        $this->rssChannel = new \Core\Service\RSSService\RSSChannel();
        foreach ($this->configData as $key => $value)
        {
            $methodName = 'Set' . ucfirst($key);
            if (method_exists($this->rssChannel, $methodName))
            {
                $this->rssChannel->$methodName($value);
            }
        }
    }

    public function AddItem($title, $description, $link, $timestamp)
    {
        $this->rssChannel->AddItemText($title, $description, $link, $timestamp);
    }

    public function GetRSS()
    {
        return (string)$this->rssChannel;
    }

    public function AddFromTable($table)
    {
        //\AccountTable::FindBy($array)->ex
        //$table:: GetAll()->limit($this->configData);
    }

    public function AddItems($items)
    {
        foreach ($items as $item)
        {
            $this->rssChannel->AddItem($item);
        }
    }

    public function SetSource($table, $orderField = 'id', $count = 20)
    {
        //$result = $table::FindBy(array(), array($orderField . ' DESC'), $count);
        //$result = $table::GetAll();
    }

    public function SetSourceFunction($func)
    {
        //$result = $table::FindBy(array(), array($orderField . ' DESC'), $count);
        //$result = $table::GetAll();
    }

    public function SetConversion($conversion)
    {
        $this->conversion = $conversion;
    }

    public function SetConversionBasic($title, $description, $link, $timestamp)
    {
        $this->SetConversion(array('title' => $title, 'description' => $description, 'link' => $link, 'pubDate' => $timestamp));
    }

    private function Convert($data)
    {
        if (is_array($this->conversion))
        {
            if (is_array($data))
            {
                foreach ($this->conversion as $fieldName => $dataName)
                {
                    $methodName = 'Set' . ucfirst($fieldName);
                    if (method_exists($this->rssChannel, $methodName))
                    {
                        $this->rssChannel->$methodName($data[$dataName]);
                    }
                }
            }
            elseif (is_object($data))
            {

            }
            else
            {
                throw new Exception('Failed convert RSS data');
            }
        }
        elseif (is_callable($this->conversion))
        {
            return $this->conversion($data);
        }
    }


}
?>
