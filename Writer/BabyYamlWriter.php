<?php


namespace Ling\BabyYaml\Writer;


use Ling\BabyYaml\Exception\BabyYamlException;
use Ling\Bat\FileSystemTool;

/**
 * BabyYamlWriter.
 * @author Lingtalfi
 *
 *
 */
class BabyYamlWriter
{

    private $valueAdaptor;
    private $eol = PHP_EOL;
    private $tab = "    ";
    private $formatCode = true;


    public function __construct()
    {
        $this->valueAdaptor = new BabyYamlWriterValueAdaptor();
    }


    /**
     * If file is null, will return the babyYaml dump.
     * If file is given, will write the babyYaml dump to the given file.
     *
     * Available options are:
     * - commentsMap: a [commentsMap](https://github.com/lingtalfi/BabyYaml/blob/master/personal/mydoc/pages/node-info-parser.md) can be passed.
     *      If so, it's re-injected in the given file.
     *
     *
     *
     * @return bool|string,
     *                  bool is returned only if file is given.
     *                  It indicates whether or not the writing to the file has been successful.
     *
     *                  string is returned only if file is null.
     *
     */
    public function export(array $data, $file = null, array $options = [])
    {
        $content = $this->getBabyYamlFromArray($data, $options);
        if (null === $file) {
            return $content;
        }
        return (false !== FileSystemTool::mkfile($file, $content));
    }


    //------------------------------------------------------------------------------/
    //
    //------------------------------------------------------------------------------/
    /**
     *
     * Returns the babyYaml string from the given array.
     * Available options are the same as the export method above.
     *
     *
     * @param array $array
     * @param array $options
     * @return string
     */
    private function getBabyYamlFromArray(array $array, array $options = []): string
    {
        $s = rtrim($this->getNodeContent($array, 0, 0, [], $options), PHP_EOL);
        return $s;
    }


    /**
     * Returns the BabyYaml string for the given node, recursively.
     *
     * Available options are the same as the export method above.
     *
     *
     * @param array $config
     * @param int $level
     * @param int $n
     * @param array $breadcrumbs
     * @param array $options
     * @return string
     */
    private function getNodeContent(array $config, $level = 0, $n = 0, array $breadcrumbs = [], array $options = []): string
    {


        $nodeInfoMap = $options['nodeInfoMap'] ?? null;


        $s = '';
        foreach ($config as $k => $v) {

            $breadcrumbs[] = str_replace('.', '\\.', $k);
            $currentPath = implode('.', $breadcrumbs);

            $nodeInfo = [];
            $valueType = null;
            $valueAlreadyProcessed = false;
            $literalOptions = [];


            $commentItems = [];
            if (null !== $nodeInfoMap) {
                if (array_key_exists($currentPath, $nodeInfoMap)) {
                    $nodeInfo = $nodeInfoMap[$currentPath];
                    if (array_key_exists("comments", $nodeInfo)) {
                        $commentItems = $nodeInfo['comments'];
                    }
                    if (array_key_exists("type", $nodeInfo)) {
                        $valueType = $nodeInfo['type'];
                    }
                }
            }


            if ('multi' === $valueType) {
                $literalOptions['forceMulti'] = true;
                foreach ($commentItems as $commentItem) {
                    if ('multi-top' === $commentItem[0]) {
                        $literalOptions['multiTopComment'] = $commentItem[1];
                    } elseif ('multi-bottom' === $commentItem[0]) {
                        $literalOptions['multiBottomComment'] = $commentItem[1];
                    }
                }
            } elseif (in_array($valueType, [
                'hybrid',
                'quote',
                'mapping',
                'sequence',
            ])) {
                /**
                 * Note that the original value includes the inline comments...
                 */
                if (array_key_exists("originalValue", $nodeInfo)) {
                    $v = $nodeInfo['originalValue'];
                } else {
                    throw new BabyYamlException("As for now, you're expected to provide the originalValue along with the type, see the node-info-parser.md documentation for more info.");
                }
                $valueAlreadyProcessed = true;
            }


            $this->appendComments($s, $commentItems, 'block');


            if (is_numeric($k)) {

                $prefix = $k . ': ';
                if ((int)$k === (int)$n) {
                    $prefix = "- ";
                } else {
                    $n = $k;
                }


                if (is_array($v)) {
                    $s .= $this->tab($level) . $prefix;
                    $this->appendComments($s, $commentItems, 'inline');


                    if ($v) {
                        $p = 0;
                        $s .= $this->eol();
                        foreach ($v as $k2 => $v2) {
                            $s .= $this->getNodeContent(array($k2 => $v2), ($level + 1), $p, $breadcrumbs, $options);
                            $p++;
                        }
                        $s .= $this->tab($level);
                    } else {
                        $s .= $this->toLiteral($v, $level, $valueAlreadyProcessed, $literalOptions);

                    }

                    $s .= $this->eol();
                } else {
                    $s .= $this->tab($level) . $prefix . $this->toLiteral($v, $level, $valueAlreadyProcessed, $literalOptions);
                    // comments already covered by originalValue
                    $s .= $this->eol();
                }
                $n++;
            } else {

                if (is_array($v)) {
                    $s .= $this->tab($level) . $k . ': ';
                    $this->appendComments($s, $commentItems, 'inline');


                    if ($v) {
                        $p = 0;
                        $s .= $this->eol();
                        foreach ($v as $k2 => $v2) {
                            $s .= $this->getNodeContent(array($k2 => $v2), ($level + 1), $p, $breadcrumbs, $options);
                            $p++;
                        }
                        $s .= $this->tab($level);
                    } else {
                        $s .= $this->toLiteral($v, $level, $valueAlreadyProcessed, $literalOptions);
                    }
                    $s .= $this->eol();
                } else {
                    if (false !== strpos($k, ':')) {
                        $k = '"' . str_replace('"', '\"', $k) . '"';
                    }
                    $s .= $this->tab($level) . $k . ': ' . $this->toLiteral($v, $level, $valueAlreadyProcessed, $literalOptions);
                    $s .= $this->eol();
                }
            }

            array_pop($breadcrumbs);

        }
        return $s;
    }


    //------------------------------------------------------------------------------/
    //
    //------------------------------------------------------------------------------/
    /**
     *
     * Available options are:
     * - forceMulti: bool=false, whether to force the writing of the value as a multi
     * - multiTopComment: string=null, the multi top comment
     * - multiBottomComment: string=null, the multi bottom comment
     *
     *
     *
     * @param $scalar
     * @param $level
     * @param bool $valueAlreadyProcessed
     * @param array $options
     * @return int|string
     */
    private function toLiteral($scalar, $level, bool $valueAlreadyProcessed = false, array $options = [])
    {
        if (true === $valueAlreadyProcessed) {
            return $scalar;
        }

        $forceMulti = $options["forceMulti"] ?? false;
        if (is_string($scalar) &&
            (
                true === $forceMulti ||
                false !== strpos($scalar, $this->eol())
            )
        ) {

            $multiTopComment = $options['multiTopComment'] ?? '';
            $multiBottomComment = $options['multiBottomComment'] ?? '';


            // adding 4 extra spaces (compared to the parent key's beginning) at the beginning of each line
            $nbSpaces = ($level * 4) + 4;
            $s = '<' . $multiTopComment;

            $s .= $this->eol();
            $p = explode($this->eol(), $scalar);
            foreach ($p as $v) {
                $t = trim($v);
                if (strlen($t) > 0) {
                    $v = str_repeat($this->tab, $nbSpaces / 4) . $v;
                }
                $s .= $v . $this->eol();
            }
            $s .= str_repeat(' ', $level * 4) . '>' . $multiBottomComment;
            $v = $s;
        } else {
            $v = $this->valueAdaptor->getValue($scalar);
        }
        return $v;
    }


    private function appendComments(string &$s, array $commentItems, string $kind)
    {
        foreach ($commentItems as $commentItem) {
            list($type, $comment, $isBegin) = $commentItem;

            switch ($kind) {
                case "block":
                    if ('block' === $type) {
                        $s .= $comment . PHP_EOL;
                    }
                    break;
                case "inline":
                    if ('inline' === $type) {
                        $s .= $comment;
                    }
                    break;
                default:
                    throw new BabyYamlException("Unknown kind $kind.");
                    break;
            }
        }
    }


    //------------------------------------------------------------------------------/
    //
    //------------------------------------------------------------------------------/
    private function tab($level)
    {
        if (true === $this->formatCode) {
            return str_repeat($this->tab, $level);
        }
    }

    private function eol()
    {
        if (true === $this->formatCode) {
            return $this->eol;
        }
    }
}
