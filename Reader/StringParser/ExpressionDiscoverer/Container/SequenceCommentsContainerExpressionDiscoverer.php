<?php

namespace Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\Container;


/**
 * The SequenceCommentsContainerExpressionDiscoverer class.
 */
class SequenceCommentsContainerExpressionDiscoverer extends SequenceContainerExpressionDiscoverer
{


    /**
     * This property holds the onContainerEndCallback for this instance.
     * @var callable
     */
    protected $onContainerEndCallback;

    /**
     * @overrides
     */
    public function __construct()
    {
        parent::__construct();
        $this->onContainerEndCallback = null;
    }


    /**
     * Sets the onContainerEndCallback.
     *
     * @param mixed $onContainerEndCallback
     */
    public function setOnContainerEndCallback($onContainerEndCallback)
    {
        $this->onContainerEndCallback = $onContainerEndCallback;
    }




    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @overrides
     */
    protected function onContainerEnd(string $string)
    {
        if (false !== strpos($string, '#')) {
            $p = preg_split('!\](\s+#)!', $string, 2, \PREG_SPLIT_DELIM_CAPTURE);
            $comment = $p[1] . $p[2];
            call_user_func($this->onContainerEndCallback, $comment);

        }
    }


}
