<?php

namespace App\Model\Repositories;

use App\Model\Authorizator\AuthenticatedRole;
use App\Model\Entities\Role;
use App\Model\Entities\User;

/**
 * Class UserRepository - repozitář pro uživatele
 * @package App\Model\Repositories
 */
class UserRepository extends BaseRepository{

    public function findUsersWithoutUser(string $userId): array {
        return $this->createEntities($this->connection->select('*')
            ->from($this->getTable())
            ->where('user_id != %i', $userId)
            ->orderBy('name')
            ->fetchAll());
    }

    public function toggleAdmin(User $user): void {
        $isAdmin = $user->role->roleId === AuthenticatedRole::ADMIN_ROLE;
        $this->connection->query('UPDATE user SET role_id = %s WHERE user_id = %i',
            $isAdmin ? null : AuthenticatedRole::ADMIN_ROLE,
            $user->userId
        );
    }

}