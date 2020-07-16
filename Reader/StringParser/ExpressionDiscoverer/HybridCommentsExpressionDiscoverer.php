<?php

namespace Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer;


use Ling\BabyYaml\Exception\BabyYamlException;

/**
 * HybridCommentsExpressionDiscoverer
 *
 */
class HybridCommentsExpressionDiscoverer extends HybridExpressionDiscoverer implements GreedyExpressionDiscovererInterface
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
     * @param string $string
     * @overrideMe
     */
    protected function onSymbolDetected(string $string)
    {
        /**
         * We know that those values can't contain the hash symbol (because they can't have quotes around them),
         * so we can use preg_match here to get the comment with the relevant indentation prefix.
         */

        if (preg_match('!\s+#.*!', $string, $match)) {
            $comment = $match[0];
            $this->comments[] = [
                'inline-value',
                $comment,
            ];
        } else {
            throw new BabyYamlException("Why was the comment symbol not detected? That must be weird.");
        }

    }


}
