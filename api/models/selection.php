<?php
/**
 * @OA\Schema(description="description of the selection group naming")
 */
class Selection {

    /**
     * The selection id.
     * @var string|null
     * @OA\Property(example="uuid")
     */
    public ?string $id;

    /**
     * The selection name.
     * @var string|null
     * @OA\Property()
     */
    public ?string $name;

    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
    }
}

?>
