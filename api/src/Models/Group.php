<?php

namespace PieLab\GAB\Models;

/**
 * @OA\Schema(description="group category description")
 */
class Group {

    /**
     * The idea id.
     * @var string|null
     * @OA\Property(example="uuid")
     */
    public ?string $id;

    /**
     * Time of idea storage.
     * @var Date|string|null
     * @OA\Property(property="time_stamp")
     */
    public Date|string|null $time_stamp;

    /**
     * Description of the idea.
     * @var string|null
     * @OA\Property()
     */
    public ?string $description;

    /**
     * Short description or keywords that describe the idea.
     * @var string|null
     * @OA\Property()
     */
    public ?string $keywords;

    public function __construct(array $data = null)
    {
        $this->id = $data['id'] ?? null;
        $this->time_stamp = $data['time_stamp'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->keywords = $data['keywords'] ?? null;
    }
}

?>