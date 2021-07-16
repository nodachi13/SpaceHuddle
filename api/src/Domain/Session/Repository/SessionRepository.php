<?php

namespace App\Domain\Session\Repository;

use App\Domain\Base\Repository\GenericException;
use App\Domain\Base\Repository\KeyGeneratorTrait;
use App\Domain\Base\Repository\RepositoryInterface;
use App\Domain\Base\Repository\RepositoryTrait;
use App\Domain\Participant\Repository\ParticipantRepository;
use App\Domain\Task\Data\TaskData;
use App\Domain\Topic\Repository\TopicRepository;
use App\Domain\User\Repository\UserRepository;
use App\Domain\Session\Type\SessionRoleType;
use App\Factory\QueryFactory;
use App\Domain\Session\Data\SessionData;
use DomainException;

/**
 * Repository
 */
class SessionRepository implements RepositoryInterface
{
    use KeyGeneratorTrait;
    use RepositoryTrait {
        RepositoryTrait::insert as private genericInsert;
    }

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->setUp(
            $queryFactory,
            "session",
            SessionData::class,
            "user_id",
            UserRepository::class
        );
    }

    /**
     * Checks the access role via which the logged-in user may access the entry with the specified primary key.
     * @param string|null $id Primary key to be checked.
     * @return string|null Role with which the user is authorised to access the entry.
     * @throws GenericException
     */
    public function getAuthorisationRole(?string $id): ?string
    {
        $authorisation = $this->getAuthorisation();
        $conditions = [
            "session_id" => $id,
            "user_id" => $authorisation->id,
            "user_type" => $authorisation->type
        ];
        $role = $this->getAuthorisationRoleFromCondition(
            $id,
            $conditions,
            "session_permission",
            false
        );

        if ($authorisation->isParticipant() and $role == strtoupper(SessionRoleType::PARTICIPANT)) {
            $result = $this->get([
                "session.id" => $id
            ]);
            if (!is_object($result)) {
                return strtoupper(SessionRoleType::EXPIRED);
            }
        }

        return $role;
    }

    /**
     * Get entity.
     * @param array $conditions The WHERE conditions to add with AND.
     * @return SessionData|array<SessionData>|null The result entity(s).
     * @throws GenericException
     */
    public function get(array $conditions = []): null|SessionData|array
    {
        $authorisation = $this->getAuthorisation();
        $authorisation_conditions = [
            "session_permission.user_id" => $authorisation->id,
            "session_permission.user_type" => $authorisation->type
        ];

        if ($authorisation->isParticipant()) {
            array_push($conditions, "session.expiration_date >= current_timestamp()");
        }

        $query = $this->queryFactory->newSelect($this->getEntityName());
        $query->select(["session.*", "session_permission.role"])
            ->innerJoin("session_permission", "session_permission.session_id = session.id")
            ->andWhere($authorisation_conditions)
            ->andWhere($conditions);

        return $this->fetchAll($query);
    }

    /**
     * Get entity by ID.
     * @param string $id The entity ID.
     * @return SessionData|null The result entity.
     * @throws GenericException
     */
    public function getById(string $id): ?SessionData
    {
        $result = $this->get([
            "session.id" => $id
        ]);
        if (!is_object($result)) {
            throw new DomainException("Entity $this->entityName not found");
        }
        return $result;
    }

    /**
     * Get entity by ID.
     * @param string $parentId The entity parent ID.
     * @return array<SessionData> The result entity list.
     * @throws GenericException
     */
    public function getAll(string $parentId): array
    {
        $result = $this->get([
            #"session_permission.user_state" => "active"
        ]);
        if (is_array($result)) {
            return $result;
        } elseif (isset($result)) {
            return [$result];
        }
        return [];
    }

    /**
     * Insert session row.
     * @param object $data The session data
     * @return object|null The new session
     */
    public function insert(object $data): ?object
    {
        $data->connectionKey = $this->generateNewConnectionKey("connection_key");
        return $this->genericInsert($data);
    }

    /**
     * Include dependent data.
     * @param string $id Primary key of the linked table entry
     * @param array|object|null $parameter Dependent data to be included.
     * @return void
     */
    protected function insertDependencies(string $id, array|object|null $parameter): void
    {
        if (is_object($parameter)) {
            $this->queryFactory->newInsert("session_role", [
                "session_id" => $id,
                "user_id" => $parameter->userId,
                "role" => strtoupper(SessionRoleType::MODERATOR)
            ])->execute();
        }
    }

    /**
     * Delete dependent data.
     * @param string $id Primary key of the linked table entry.
     * @return void
     */
    protected function deleteDependencies(string $id): void
    {
        $query = $this->queryFactory->newSelect("participant");
        $query->select(["id"]);
        $query->andWhere(["session_id" => $id]);

        $result = $query->execute()->fetchAll("assoc");
        if (is_array($result)) {
            $participant = new ParticipantRepository($this->queryFactory);
            foreach ($result as $resultItem) {
                $participantId = $resultItem["id"];
                $participant->deleteById($participantId);
            }
        }

        $query = $this->queryFactory->newSelect("topic");
        $query->select(["id"]);
        $query->andWhere(["session_id" => $id]);

        $result = $query->execute()->fetchAll("assoc");
        if (is_array($result)) {
            $topic = new TopicRepository($this->queryFactory);
            foreach ($result as $resultItem) {
                $topicId = $resultItem["id"];
                $topic->deleteById($topicId);
            }
        }

        $this->queryFactory->newDelete("resource")
            ->andWhere(["session_id" => $id])
            ->execute();

        $this->queryFactory->newDelete("session_role")
            ->andWhere(["session_id" => $id])
            ->execute();
    }

    /**
     * Convert to array.
     * @param object $data The entity data
     * @return array<string, mixed> The array
     */
    protected function formatDatabaseInput(object $data): array
    {
        return [
            "id" => $data->id ?? null,
            "title" => $data->title ?? null,
            "description" => $data->description ?? null,
            "connection_key" => $data->connectionKey ?? null,
            "max_participants" => $data->maxParticipants ?? null,
            "expiration_date" => $data->expirationDate ?? null,
            "public_screen_module_id" => $data->publicScreenModuleId ?? null
        ];
    }

    /**
     * Sets the active session task displayed on the public screen.
     * @param string $sessionId The session id to be updated.
     * @param string|null $taskId The active task id on the public screen.
     * @return object|null The updated session.
     * @throws GenericException
     */
    public function setPublicScreen(string $sessionId, ?string $taskId): object|null
    {
        $moduleId = null;
        if (isset($taskId)) {
            $moduleId = $this->getPossiblePublicScreenModule($sessionId, $taskId);
            if (is_null($moduleId)) {
                return null;
            }
        }
        
        $row = [
            "id" => $sessionId,
            "public_screen_module_id" => $moduleId
        ];
        return $this->update($row);
    }

    /**
     * Evaluates whether there is a valid module for the task to be set.
     * @param string $sessionId The session id to be updated.
     * @param string $taskId The active task id on the public screen.
     * @return string|null Module Id
     */
    public function getPossiblePublicScreenModule(string $sessionId, string $taskId): ?string
    {
        $query = $this->queryFactory->newSelect("module");
        $query->select(["module.id"])
            ->innerJoin("task", "task.id = module.task_id")
            ->innerJoin("topic", "topic.id = task.topic_id")
            ->andWhere([
                "module.task_id" => $taskId,
                "topic.session_id" => $sessionId
            ]);

        $result = $query->execute()->fetch("assoc");
        if (is_array($result)) {
            return $result["id"];
        }
        return null;
    }

    /**
     * Get the active session task displayed on the public screen.
     * @param string $sessionId The session id.
     * @return object|null The result entity.
     */
    public function getPublicScreen(string $sessionId): ?object
    {
        $query = $this->queryFactory->newSelect("task");
        $query->select(["task.*"])
            ->innerJoin("module", "task.id = module.task_id")
            ->innerJoin("session", "module.id = session.public_screen_module_id")
            ->andWhere([
                "session.id" => $sessionId
            ]);

        $result = $this->fetchAll($query, TaskData::class);

        if (is_array($result)) {
            if (sizeof($result) > 0) {
                $result = $result[0];
            } else {
                $result = null;
            }
        }
        return $result;
    }
}