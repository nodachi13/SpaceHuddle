<?php

namespace PieLab\GAB\Models;

/**
 * @OA\Schema(description="basic voting description")
 */
class Voting {

    /**
     * id of the voting
     * @var string|null
     * @OA\Property(example="uuid")
     */
    public ?string $id;

    /**
     * id of the idea
     * @var string|null
     * @OA\Property(example="uuid")
     */
    public ?string $idea_id;

    /**
     * rating of the idea
     * @var int|null
     * @OA\Property()
     */
    public ?int $rating;

    /**
     * Weighting of the idea.
     * @var float|null
     * @OA\Property()
     */
    public ?float $detail_rating;

    public function __construct(array $data = null)
    {
        $this->id = $data['id'] ?? null;
        $this->idea_id = $data['idea_id'] ?? null;
        $this->rating = (int)$data['rating'] ?? null;
        $this->detail_rating = (float)$data['detail_rating'] ?? null;
    }
}

?>