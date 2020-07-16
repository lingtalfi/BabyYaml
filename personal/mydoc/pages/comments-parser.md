Comments parser
==========
2020-07-14 -> 2020-07-16


Recently I wanted to be able to update a config file programmatically, and preserving its comments.

This requirement revealed the need for a comment parser.


The strategy I could think about was something like this:

- first collect the existing comments
- then update the config file
- then re-inject the existing comments


Now the technique of collect/re-injection I came up with is based on the [bdot](https://github.com/karayabin/universe-snapshot/blob/master/universe/Ling/Bat/doc/bdot-notation.md) keys
of the config file.

The problem with this approach is that if the updated config changes a key, any comment attached to that
key will disappear.

However, that was still the simplest (i.e. the lesser evil) solution I came up with.

So bear that in mind if you plan to use this comment parser.  



Now that the bulk of it is implemented, it looks like this in php code:

```php
<?php


use Ling\BabyYaml\BabyYamlUtil;

require_once "app.init.inc.php";


$file = "/komin/jin_site_demo/config/services/Light_Train.byml";
$file2 = "/komin/jin_site_demo/config/services/Light_Train2.byml";


$config = BabyYamlUtil::readFile($file);
$config['train']['methods']['setOptions']['options']["theLastOption"] = "marijuana"; // updating the config...
$commentsMap = BabyYamlUtil::getCommentsMapByFile($file); // so here we get the comments map
//az("r", $commentsMap);

BabyYamlUtil::writeFile($config, $file2, [
    "commentsMap" => $commentsMap, // and here we inject it in the file to write
]);




```






commentsMap
----------
2020-07-16


A **comments map** is an array of [bdot paths](https://github.com/karayabin/universe-snapshot/blob/master/universe/Ling/Bat/doc/bdot-notation.md) to commentItems (explained in the next section).
The **comment map** is the basic tool I use at the heart of the strategy described above: to collect the comments,
and re-injecting them later.








commentItem
---------------
2020-07-14 -> 2020-07-16


A comment item is an array representing a comment attached to a specific key.

Its structure is the following:

- 0: string, the comment type, on of:
    - inline
    - block
    - inline-value
- 1: string, the comment text
- 2: bool | null, if the comment is attached to a babyYaml multiline text, then indicates 
    whether the comment is attached to the beginning char of the multiline (true), or
    the ending char (false).
    If the comment is not attached on a multiline, the value is null.
    


The differences between comment types is:

- inline: a comment starting just after the key declaration
- block: a comment standing alone on its line 
- inline-value: a comment starting after the value  
