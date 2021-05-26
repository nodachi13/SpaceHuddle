<?php
require_once('idea_abstract.php');
/**
 * @OA\Schema(description="description of the aggregated voting result")
 */
class VotingResult {

    /**
     * evaluated idea
     * @var IdeaAbstract|null
     * @OA\Property(ref="#/components/schemas/IdeaAbstract")
     */
    public ?IdeaAbstract $idea;

    /**
     * Sum of the idea rating
     * @var int|null
     * @OA\Property()
     */
    public ?int $rating_sum;

    /**
     * Sum of the idea weighting
     * @var float|null
     * @OA\Property()
     */
    public ?float $detail_rating_sum;

    public function __construct(array $data = null)
    {
        $this->idea = new IdeaAbstract($data);
        $this->rating_sum = isset($data['rating']) ? (int)$data['rating'] : null;
        $this->detail_rating_sum = isset($data['detail_rating']) ? (float)$data['detail_rating'] : null;
    }
}

?>
