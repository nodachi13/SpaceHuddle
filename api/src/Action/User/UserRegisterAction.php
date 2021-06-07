<?php

namespace App\Action\User;

use App\Action\Base\AbstractAction;
use App\Domain\User\Service\UserCreator;
use App\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Action for registering a new user.
 * @package App\Action\User
 * @OA\Post(
 *   path="/api/user/register/",
 *   summary="Register a new user",
 *   tags={"User"},
 *   @OA\RequestBody(
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(required={"username", "password", "passwordConfirmation"},
 *         @OA\Property(property="username", type="string", example="john.doe"),
 *         @OA\Property(property="password", type="string", example="secret123"),
 *         @OA\Property(property="passwordConfirmation", type="string", example="secret123")
 *       )
 *     )
 *   ),
 *   @OA\Response(response="201", ref="#/components/responses/201"),
 *   @OA\Response(response="400", ref="#/components/responses/400"),
 *   @OA\Response(response="403", ref="#/components/responses/403"),
 *   @OA\Response(response="422", ref="#/components/responses/422"),
 *   @OA\Response(response="500", ref="#/components/responses/500")
 * )
 */
final class UserRegisterAction extends AbstractAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param UserCreator $service The service
     */
    public function __construct(Responder $responder, UserCreator $service)
    {
        parent::__construct($responder, $service);
        $this->successStatusCode = StatusCodeInterface::STATUS_CREATED;
    }
}
