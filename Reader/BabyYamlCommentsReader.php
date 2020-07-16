<?php


namespace Ling\BabyYaml\Reader;


use Ling\BabyYaml\Exception\BabyYamlException;
use Ling\BabyYaml\Reader\Node\NodeWithComment;
use Ling\BabyYaml\Reader\NodeToArrayConvertor\NodeToArrayCommentsConvertor;
use Ling\BabyYaml\Reader\ValueInterpreter\BabyYamlValueCommentInterpreter;

/**
 * The BabyYamlCommentsReader class.
 */
class BabyYamlCommentsReader extends BabyYamlReader
{


    /**
     * This property holds the commentsMap for this instance.
     *
     * An array of path => comments.
     *
     * With:
     *
     * - path: string, the @page(bdot path) representing a key of the config
     * - comments: array of @page(commentItems) attached to that particular config key
     *
     *
     *
     * @var array
     */
    protected $commentsMap;


    /**
     * Builds the BabyYamlCommentsReader instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->convertor = new NodeToArrayCommentsConvertor();
        $this->builder = new BabyYamlCommentsBuilder();
        $this->interpreter = new BabyYamlValueCommentInterpreter();
        $this->commentsMap = [];
    }


    /**
     * Returns the commentsMap.
     * The result will be available only after calling a method that reads a babyYaml source (such as readString or readFile).
     * @return array
     */
    public function getCommentsMap(): array
    {
        /**
         * Here we combine the comments map for the inline & block comments with the comments map for the inline-value comments.
         */
        $firstMap = $this->commentsMap;
        $secondMap = $this->convertor->getCommentsMap();
        $ret = array_merge_recursive($firstMap, $secondMap);


        /**
         * Fix multiple inline-values, that might happen.
         * Note that comments on sequences/mappings are interpreted by two different tools of this suite,
         * but fortunately the first to be called is the sequence/mapping discoverer (i.e. not the regular inline value
         * fetcher, which is not accurate for sequences/mappings).
         * So we basically just need to drop any inline-value but the first to be good.
         *
         */
        foreach ($ret as $k => $commentItems) {
            $firstInlineValueFound = false;
            foreach ($commentItems as $k2 => $commentItem) {
                if ('inline-value' === $commentItem[0]) {
                    if (false === $firstInlineValueFound) {
                        $firstInlineValueFound = true;
                    } else { // subsequent inline-values, all wrong or duplicates
                        unset($ret[$k][$k2]);
                    }
                }
            }
        }


        


        ksort($ret);
        return $ret;

    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @overrides
     */
    protected function handleReadResult($root)
    {
        if ($root instanceof NodeWithComment) {


            //--------------------------------------------
            // COLLECTING THE COMMENTS MAP
            //--------------------------------------------
            $children = $root->getChildren();
            foreach ($children as $k => $child) {
                $breadcrumbs = null;
                $this->collectMap($child, $k, $breadcrumbs);
            }
            //--------------------------------------------
            // REGULAR RETURN
            //--------------------------------------------
            return $this->convertor->convert($root, $this->interpreter);

        } else {
            $type = gettype($root);
            throw new BabyYamlException("This class only works with NodeWithComment instances, $type passed.");
        }
    }


    protected function collectMap(NodeWithComment $node, $key = null, array &$breadcrumbs = null)
    {
        $currentKey = $node->getKey();
        if (null === $currentKey) {
            $currentKey = $key; // resolving array auto-indexing
        }
        $currentKey = str_replace('.', '\.', $currentKey); // bdot escape


        if (null === $breadcrumbs) { // just the root node case
            $breadcrumbs = [$currentKey];
        } else {
            $breadcrumbs[] = $currentKey;
        }


        $currentPath = implode('.', $breadcrumbs);

        if ($node->hasComments()) {
            $comments = $node->getComments();
            if (false === array_key_exists($currentPath, $this->commentsMap)) {
                $this->commentsMap[$currentPath] = [];
            }
            $this->commentsMap[$currentPath] = array_merge($this->commentsMap[$currentPath], $comments);
        }

        $children = $node->getChildren();
        foreach ($children as $k => $child) {
            $this->collectMap($child, $k, $breadcrumbs);
            array_pop($breadcrumbs);
        }
    }

}