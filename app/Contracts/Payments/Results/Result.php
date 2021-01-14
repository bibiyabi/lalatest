<?php

namespace App\Contracts\Payments\Results;

class Result
{
    private $type;

    private $content;

    public function __construct(string $type, string $content) {
        $this->type = $type;
        $this->content = $content;
    }

    public function toArray()
    {
        return [
            'type' => $this->type,
            'content' => $this->content,
        ];
    }

    public function getType()
    {
        return $this->type;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the value of content
     *
     * @return  self
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}
