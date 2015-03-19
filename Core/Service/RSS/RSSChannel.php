<?php

namespace Core\Service\RSSService;

class RSSChannel
{
    private $title = '';
    private $link = '';
    private $description = '';
    private $language;
    private $copyright;
    private $managingEditor;
    private $webMaster;
    private $pubDate;
    private $lastBuildDate;
    private $category;
    private $generator;
    private $docs;
    private $cloud;
    private $ttl;
    private $image;
    private $rating;
    private $textInput;
    private $skipHours;
    private $skipDays;
    private $items = array();

    public function __toString()
    {
        $r = '<?xml version="1.0" encoding="UTF-8"?>';
        $r .= '<rss version="2.0">';
        $r .= '<channel>';

        $fields = get_object_vars($this);
        foreach ($fields as $key => $value)
        {
            if ($key == 'items')
                continue;

            if ($value != null)
            {
                $r .= '<' . $key . '>' . $value . '</' . $key . '>';
            }
        }

        if (count($this->items) > 0)
        {
            foreach ($this->items as $item)
            {
                $r .= $item;
            }
        }

        $r .= '</channel>';
        $r .= '</rss>';
        return $r;
    }

    public function AddItem($item)
    {
        $this->items[] = $item;
    }

    public function AddItemText($title, $description, $link, $timestamp)
    {
        $item = new \RSSItem();
        $item->SetTitle($title);
        $item->SetDescription($description);
        $item->SetLink($link);
        $item->SetPubDate($timestamp);
        $this->AddItem($item);
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
        $this->title = $title;
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
        $this->link = $link;
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
        $this->description = $description;
    }
}

?>
