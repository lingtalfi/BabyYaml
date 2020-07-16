<?php

namespace Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer;


use Ling\BabyYaml\Exception\BabyYamlException;

/**
 * SimpleQuoteCommentsExpressionDiscoverer
 */
class SimpleQuoteCommentsExpressionDiscoverer extends SimpleQuoteExpressionDiscoverer
{


    /**
     * This property holds the @page(commentItems) for this instance.
     * @var array
     */
    protected $comments = [];

    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->comments = [];
    }


    /**
     * Returns the comments of this instance, and empties them.
     *
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * Resets the comments
     */
    public function resetComments()
    {
        $this->comments = [];
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @overrides
     */
    protected function onValueDiscovered(string $string, int $lastPos)
    {
        $rest = substr($string, $lastPos);
        $p = explode('#', $rest, 2);
        if (2 === count($p)) {
            $comment = substr($rest, 1);
            $this->comments[] = [
                'inline-value',
                $comment,
            ];
        } else {
            throw new BabyYamlException("You shouldn't see this message..., good luck");
        }
    }


}
