<?php

namespace App\Action\Participant;

use App\Action\Base\AbstractAction;
use App\Domain\Participant\Service\ParticipantConnector;
use App\Responder\Responder;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Action for connecting to a session as a participant.
 *
 * @OA\Post(
 *   path="/api/participant/connect/",
 *   summary="Connect to a session",
 *   tags={"Participant"},
 *   @OA\RequestBody(
 *     @OA\MediaType(
 *       mediaType="application/json",
 *       @OA\Schema(required={"sessionKey", "ipHash"},
 *         @OA\Property(property="sessionKey", type="string"),
 *         @OA\Property(property="ipHash", type="string")
 *       )
 *     )
 *   ),
 *   @OA\Response(response="200", description="Success",
 *     @OA\JsonContent(ref="#/components/schemas/ParticipantData")),
 *   @OA\Response(response="404", description="Not Found")
 * )
 */
final class ParticipantConnectAction extends AbstractAction
{
    /**
     * The constructor.
     *
     * @param Responder $responder The responder
     * @param ParticipantConnector $service The service
     */
    public function __construct(Responder $responder, ParticipantConnector $service)
    {
        parent::__construct($responder, $service);
        $this->successStatusCode = StatusCodeInterface::STATUS_ACCEPTED;
    }
}
