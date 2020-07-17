<?php


namespace Ling\BabyYaml\Reader;


use Ling\BabyYaml\Reader\NodeToArrayConvertor\NodeToArrayNodeInfoConvertor;
use Ling\BabyYaml\Reader\ValueInterpreter\BabyYamlValueNodeInfoInterpreter;

/**
 * The BabyYamlNodeInfoReader class.
 */
class BabyYamlNodeInfoReader extends BabyYamlReader
{


    /**
     * Builds the BabyYamlCommentsReader instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->convertor = new NodeToArrayNodeInfoConvertor();
        $this->builder = new BabyYamlCommentsBuilder();
        $this->interpreter = new BabyYamlValueNodeInfoInterpreter();
    }


    /**
     * Returns the commentsMap.
     * The result will be available only after calling a method that reads a babyYaml source (such as readString or readFile).
     * @return array
     */
    public function getNodeInfoMap(): array
    {
        $ret = [];
        $commentsMap = $this->convertor->getCommentsMap();
        $types = $this->convertor->getTypes();

        foreach ($commentsMap as $key => $comments) {
            $ret[$key]['comments'] = $comments;
        }

        foreach ($types as $key => $types) {
            $ret[$key]['type'] = $types[0];
            $ret[$key]['keyType'] = $types[3];
            $ret[$key]['originalValue'] = $types[2];
            $ret[$key]['value'] = $types[1];
        }


        return $ret;

    }


}