<?php

namespace App\Domain\User\Service;

use App\Data\AuthorisationException;
use App\Data\AuthorisationData;
use App\Data\AuthorisationType;
use App\Domain\Base\Repository\GenericException;
use App\Domain\Base\Service\BaseServiceTrait;
use App\Domain\Session\Type\SessionRoleType;

/**
 * Service.
 */
final class UserUpdater
{
    use BaseServiceTrait;
    use UserServiceTrait;

    /**
     * Define authorised roles for the service.
     */
    protected function setPermission(): void
    {
        $this->authorisationPermissionList = [
            AuthorisationType::USER
        ];
        $this->entityPermissionList = [
            SessionRoleType::MODERATOR,
            SessionRoleType::FACILITATOR
        ];
    }

    /**
     * Functionality of the change password for the logged in user service.
     *
     * @param AuthorisationData $authorisation Authorisation data
     * @param array<string, mixed> $bodyData Form data from the request body
     * @param array<string, mixed> $urlData Url parameter from the request
     *
     * @return array|object|null Service output
     * @throws AuthorisationException|GenericException
     */
    public function service(
        AuthorisationData $authorisation,
        array $bodyData,
        array $urlData
    ): array|object|null {
        $this->checkPermission($authorisation, $urlData);
        $data = array_merge($bodyData, $urlData);

        // Input validation
        $this->validator->validatePasswordUpdate($authorisation->id, $data);

        // Validation was successfully
        $user = (object)$data;
        $user->id = $authorisation->id;

        $this->transaction->begin();
        // Update the user
        $result = $this->repository->updatePassword($user);
        $this->transaction->commit();

        // Logging
        $this->logger->info("The password was successfully updated: $authorisation->id");

        return [
            "state" => "Success",
            "message" => "The password was successfully updated."
        ];
    }
}
