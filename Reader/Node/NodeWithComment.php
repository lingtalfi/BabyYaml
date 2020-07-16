<?php


namespace Ling\BabyYaml\Reader\Node;


/**
 * The NodeWithComment class.
 */
class NodeWithComment extends Node
{


    /**
     * This property holds the @page(commentItems) for this instance.
     *
     * @var array
     */
    protected $comments;


    /**
     * Builds the NodeWithComment instance.
     *
     * @param string $value
     * @param null $key
     */
    function __construct($value = '', $key = null)
    {
        parent::__construct($value, $key);
        $this->comments = [];
    }


    /**
     * Adds a comment.
     *
     * @param string $comment
     * @param string $type
     */
    public function addComment(string $comment, string $type, $extra = null)
    {
        $this->comments[] = [$type, $comment, $extra];
    }


    /**
     * Returns whether this instance contains comments.
     * @return bool
     */
    public function hasComments(): bool
    {
        return (count($this->comments) > 0);
    }

    /**
     * Returns the comments of this instance.
     *
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }


}
