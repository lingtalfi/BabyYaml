<?php


namespace Ling\BabyYaml\Parser;

use Ling\BabyYaml\Reader\BabyYamlCommentsReader;

/**
 * The BabyYamlCommentsParser class.
 */
class BabyYamlCommentsParser
{


    /**
     * Returns a [commentsMap](https://github.com/lingtalfi/BabyYaml/blob/master/personal/mydoc/pages/comments-parser.md) from the given string.
     *
     * @param string $string
     * @return array
     */
    public function parseString(string $string): array
    {
        $reader = new BabyYamlCommentsReader();
        $reader->readString($string);
        return $reader->getCommentsMap();
    }


    /**
     * Returns a [commentsMap](https://github.com/lingtalfi/BabyYaml/blob/master/personal/mydoc/pages/comments-parser.md) from the given file.
     *
     * @param string $file
     * @return array
     */
    public function parseFile(string $file): array
    {
        $reader = new BabyYamlCommentsReader();
        $reader->readFile($file);
        return $reader->getCommentsMap();
    }
}