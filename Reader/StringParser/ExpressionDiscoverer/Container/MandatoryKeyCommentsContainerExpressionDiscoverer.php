<?php


namespace Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\Container;


/**
 * The MandatoryKeyCommentsContainerExpressionDiscoverer class.
 */
class MandatoryKeyCommentsContainerExpressionDiscoverer extends MandatoryKeyContainerExpressionDiscoverer
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
            $p = explode('}', $string, 2);
            $comment = array_pop($p);
            call_user_func($this->onContainerEndCallback, $comment);
        }
    }
}
