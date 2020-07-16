<?php

namespace Ling\BabyYaml\Reader\NodeToArrayConvertor;

use Ling\BabyYaml\Exception\BabyYamlException;
use Ling\BabyYaml\Reader\Node\NodeInterface;
use Ling\BabyYaml\Reader\ValueInterpreter\BabyYamlValueCommentInterpreter;
use Ling\BabyYaml\Reader\ValueInterpreter\ValueInterpreterInterface;


/**
 * NodeToArrayCommentsConvertor
 *
 */
class NodeToArrayCommentsConvertor implements NodeToArrayConvertorInterface
{


    /**
     * The comment map.
     * An array of bdotPath => @page(commentItems) for this instance.
     * See the @page(BabyYamlCommentsReader->commentsMap property) for more details.
     *
     * @var array
     */
    protected $comments;

    /**
     * This property holds the _previousCurrentPath for this instance.
     * @var string
     */
    private $_previousCurrentPath;


    /**
     * Builds the NodeToArrayCommentsConvertor instance.
     */
    public function __construct()
    {
        $this->comments = [];
        $this->_previousCurrentPath = '';
    }


    /**
     * @implementation
     */
    public function convert(NodeInterface $node, ValueInterpreterInterface $interpreter)
    {
        if ($interpreter instanceof BabyYamlValueCommentInterpreter) {

            $breadcrumbs = [];
            $res = $this->resolveChildren($node->getChildren(), $interpreter, $breadcrumbs);


            $comments = $interpreter->getComments();


            if ($comments) { // don't forget the last comment if any
                // adding isBegin=null for all inline comments, as it's only used for multiline comments...
                foreach ($comments as $k => $comment) {
                    $comments[$k][] = null;
                }
                $this->comments[$this->_previousCurrentPath] = $comments;

            }
            return $res;
        } else {
            $class = get_class($interpreter);
            throw new BabyYamlException("I only work with BabyYamlValueCommentInterpreter instances, sorry ($class given).");
        }
    }


    /**
     * Returns the commentsMap collected by this instance.
     * @return array
     */
    public function getCommentsMap(): array
    {
        return $this->comments;
    }



    //------------------------------------------------------------------------------/
    // 
    //------------------------------------------------------------------------------/
    private function resolveChildren(array $children, BabyYamlValueCommentInterpreter $interpreter, array $breadcrumbs = [])
    {
        $ret = [];
        foreach ($children as $_k => $node) {
            $k = $node->getKey();
            $commentK = $k;
            if (null === $commentK) {
                $commentK = $_k;
            }
            $commentK = str_replace('.', '\.', $commentK);

            $breadcrumbs[] = $commentK;


            $comments = $interpreter->getComments();
            $currentPath = implode('.', $breadcrumbs);
            if ($comments) {
                // adding isBegin=null for all inline comments, as it's only used for multiline comments...
                foreach ($comments as $k => $comment) {
                    $comments[$k][] = null;
                }
                $this->comments[$this->_previousCurrentPath] = $comments;
                $interpreter->resetComments();
            }
            $this->_previousCurrentPath = $currentPath;


            if (false === $node->isMultiline()) {
                $v = $interpreter->getValue($node->getValue());
            } else {
                /**
                 * We don't want to interpret a multiline.
                 */
                $v = $node->getValue();
            }

            $children2 = $node->getChildren();
            if ($children2) {
                if (null === $k) {
                    $ret[] = $this->resolveChildren($children2, $interpreter, $breadcrumbs);
                } else {
                    $ret[$k] = $this->resolveChildren($children2, $interpreter, $breadcrumbs);
                }

            } else {
                if (null === $k) {
                    $ret[] = $v;
                } else {
                    $ret[$k] = $v;
                }
            }


            array_pop($breadcrumbs);

        }
        return $ret;
    }
}
