<?php

namespace App\Domain\User\Service;

use App\Domain\User\Repository\UserRepository;
use App\Domain\Base\AbstractValidator;
use App\Factory\ValidationFactory;
use Cake\Validation\Validator;
use Selective\Validation\Exception\ValidationException;
use Selective\Validation\ValidationResult;

/**
 * Service.
 */
final class UserValidator extends AbstractValidator
{
    /**
     * The constructor.
     *
     * @param UserRepository $repository The repository
     * @param ValidationFactory $validationFactory The validation
     */
    public function __construct(UserRepository $repository, ValidationFactory $validationFactory)
    {
        parent::__construct($repository, $validationFactory);
    }

    protected function getRepository() : UserRepository {
        if ($this->repository instanceof UserRepository) {
            return $this->repository;
        }
    }

    /**
     * Validate create.
     *
     * @param int $userId The user id
     * @param array<mixed> $data The data
     *
     * @return void
     */
    public function validateLogin(array $data): void
    {
        $this->validateEntity($data,
            $this->validationFactory->createValidator()
                ->notEmptyString("username")
                ->requirePresence("username")
                ->notEmptyString("password")
                ->requirePresence("password")
        );

        $username = $data["username"];
        $password = $data["password"];
        if (!$this->getRepository()->checkPasswordForUsername($username, $password)) {
            $result = new ValidationResult();
            $result->addError("username or password", "Username or password wrong.");
            throw new ValidationException("Please check your input", $result);
        }
    }

    /**
     * Validate create.
     *
     * @param array<mixed> $data The data
     *
     * @return void
     */
    public function validateCreate(array $data): void
    {
        $this->validateEntity($data);

        $username = $data["username"];
        if ($this->getRepository()->existsUsername($username)) {
            $result = new ValidationResult();
            $result->addError("username", "User $username already exists.");
            throw new ValidationException("Please check your input", $result);
        }
    }

    /**
     * Validate if the entity exists.
     *
     * @param string $id The entity id
     *
     * @return void
     */
    public function validateExists(string $id, ?string $errorMessage = null): void
    {
        parent::validateExists($id, "The logged-in user no longer exists.");
    }

    /**
     * Validate password update.
     *
     * @param string $userId The user id
     * @param array<mixed> $data The data
     *
     * @return void
     */
    public function validatePasswordUpdate(string $userId, array $data): void
    {
        $this->validateUpdate($userId, $data);

        $oldPassword = $data["oldPassword"];
        if (!$this->repository->checkEncryptTextForId($userId, $oldPassword)) {
            $result = new ValidationResult();
            $result->addError("password", "The old password is wrong.");
            throw new ValidationException("Please check your input", $result);
        }
    }
    /**
     * Create validator.
     *
     * @return Validator The validator
     */
    protected function createValidator(): Validator
    {
        $validator = $this->validationFactory->createValidator();

        return $validator
            ->notEmptyString("username")
            ->requirePresence("username", "create")
            ->notEmptyString("password")
            ->requirePresence("password")
            ->notEmptyString("passwordConfirmation")
            ->requirePresence("passwordConfirmation")
            ->minLength("password", 8, "Too short")
            ->maxLength("password", 40, "Too long")
            ->equalToField("passwordConfirmation", "password", "Password and confirmation do not match.");
    }
}
