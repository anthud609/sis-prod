<?php
// App/Modules/Auth/Contracts/UserRepositoryInterface.php

namespace App\Modules\Auth\Contracts;

interface UserRepositoryInterface
{
    /**
     * Find a user by their email address.
     *
     * @param string $email
     * @return array|null  Returns user data as an associative array or null if not found.
     */
    public function findByEmail(string $email): ?array;

    /**
     * Find a user by their ID.
     *
     * @param int $id
     * @return array|null  Returns user data as an associative array or null if not found.
     */
    public function findById(int $id): ?array;
}
