<?php


namespace Ling\BabyYaml\Reader\StringParser;

use Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\Container\MandatoryKeyCommentsContainerExpressionDiscoverer;
use Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\Container\SequenceCommentsContainerExpressionDiscoverer;
use Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\HybridCommentsExpressionDiscoverer;
use Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\Miscellaneous\PolyExpressionDiscoverer;
use Ling\BabyYaml\Reader\StringParser\ExpressionDiscoverer\SimpleQuoteCommentsExpressionDiscoverer;
use Ling\BabyYaml\Reader\StringParser\ExpressionDiscovererModel\ExpressionDiscovererModel;


/**
 * BabyYamlLineCommentExpressionDiscoverer
 *
 * A modified version of the BabyYamlLineExpressionDiscoverer, see more details in there.
 *
 * The goal was to get the inline comments.
 *
 */
class BabyYamlLineCommentExpressionDiscoverer extends PolyExpressionDiscoverer
{

    /**
     * This property holds the hybridDiscoverer for this instance.
     * @var HybridCommentsExpressionDiscoverer
     */
    protected $hybridDiscoverer;

    /**
     * This property holds the simpleQuoteDiscoverer for this instance.
     * @var SimpleQuoteCommentsExpressionDiscoverer
     */
    protected $simpleQuoteDiscoverer;

    /**
     * This property holds the sequenceDiscoverer for this instance.
     * @var SequenceCommentsContainerExpressionDiscoverer
     */
    protected $sequenceDiscoverer;


    /**
     * This property holds the sequenceDiscovererComments for this instance.
     * @var array
     */
    protected $sequenceDiscovererComments;


    /**
     * This property holds the mappingDiscoverer for this instance.
     * @var MandatoryKeyCommentsContainerExpressionDiscoverer
     */
    protected $mappingDiscoverer;


    /**
     * This property holds the mappingDiscovererComments for this instance.
     * @var array
     */
    protected $mappingDiscovererComments;


    /**
     * Builds the BabyYamlLineCommentExpressionDiscoverer instance.
     */
    public function __construct()
    {
        parent::__construct();


        $this->hybridDiscoverer = HybridCommentsExpressionDiscoverer::create();
        $this->simpleQuoteDiscoverer = new SimpleQuoteCommentsExpressionDiscoverer();
        $this->sequenceDiscoverer = new SequenceCommentsContainerExpressionDiscoverer();
        $this->sequenceDiscoverer->setOnContainerEndCallback(function ($comment) {
            $this->sequenceDiscovererComments[] = [
                'inline-value',
                $comment,
            ];
        });

        $this->mappingDiscoverer = new MandatoryKeyCommentsContainerExpressionDiscoverer();
        $this->mappingDiscoverer->setOnContainerEndCallback(function ($comment) {
            $this->mappingDiscovererComments[] = [
                'inline-value',
                $comment,
            ];
        });

        $seq = $this->sequenceDiscoverer;
        $map = $this->mappingDiscoverer;
        $disco = [
            new ExpressionDiscovererModel($map),
            new ExpressionDiscovererModel($seq),
            $this->simpleQuoteDiscoverer,
            $this->hybridDiscoverer,
        ];
        $seq->setDiscoverers($disco);
        $map->setDiscoverers($disco);
        $this
            ->setDiscoverers($disco)
            ->setGreedyDiscoverersSymbols([' #']) // there was a bug, no time for that sorry...
            ->setValidatorSymbols([' #']);

        $this->sequenceDiscovererComments = [];
        $this->mappingDiscovererComments = [];

    }


    /**
     * Returns the array of the @page(commentItems) that this discoverer parsed.
     * @return array
     */
    public function getComments(): array
    {
        $commentsOne = $this->hybridDiscoverer->getComments();
        $commentsTwo = $this->simpleQuoteDiscoverer->getComments();


        return array_merge(
            $commentsOne,
            $commentsTwo,
            $this->sequenceDiscovererComments,
            $this->mappingDiscovererComments
        );

    }


    /**
     * Reset the comments.
     */
    public function resetComments()
    {
        $this->hybridDiscoverer->resetComments();
        $this->simpleQuoteDiscoverer->resetComments();
        $this->sequenceDiscovererComments = [];
        $this->mappingDiscovererComments = [];
    }


}
