<?php

namespace Core\Service\RSSService;

class RSSItem
{
    private $title = '';
    private $link = '';
    private $description = '';
    private $author;
    private $category;
    private $comments;
    private $enclosure;
    private $guid;
    private $pubDate;
    private $source;

    public function __toString()
    {
        $r = '<item>';
        $fields = get_object_vars($this);
        foreach ($fields as $key => $value)
        {
            if ($value != null)
            {
                $r .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
        }

        $r .= '</item>';
        return $r;
    }

    public function AppendData($data)
    {
        foreach ($data as $key => $value)
        {
            $methodName = 'Set' . ucfirst($key);
            if (method_exists($this, $methodName))
            {
                $this->$methodName($value);
            }
        }
    }

    /**
     * Get title
     * @return string
     */
    public function GetTitle()
    {
        return $this->title;
    }
    /**
     * Set title
     * @param string $title
     */
    public function SetTitle($title)
    {
        if ($title != null)
        {
            $this->title = $title;
        }
    }

    /**
     * Get link
     * @return string
     */
    public function GetLink()
    {
        return $this->link;
    }
    /**
     * Set link
     * @param string $link
     */
    public function SetLink($link)
    {
        if ($link != null)
        {
            $this->link = $link;
        }
    }

    /**
     * Get description
     * @return string
     */
    public function GetDescription()
    {
        return $this->description;
    }
    /**
     * Set description
     * @param string $description
     */
    public function SetDescription($description)
    {
        if ($description != null)
        {
            $this->description = $description;
        }
    }

    /**
     * Get publication datetime
     * @return string
     */
    public function GetPubDate()
    {
        return $this->pubDate;
    }
    /**
     * Set publication datetime
     * @param string $pubDate
     */
    public function SetPubDate($pubDate)
    {
        if (is_numeric($pubDate))
            $this->pubDate = date('r', $pubDate);
        elseif (is_object($pubDate))
            $this->pubDate = date('r', $pubDate->getTimestamp());
        else
            $this->pubDate = date('r', strtotime($pubDate));
    }

}

?>
