<?php

namespace App\Repositories\Contracts;

use App\Models\FreelancerProfile;

interface FreelancerProfileRepositoryInterface
{
    /**
     * Create a new freelancer profile.
     *
     * @param int $userId
     * @param array $data
     * @return FreelancerProfile
     */
    public function create(int $userId, array $data): FreelancerProfile;

    /**
     * Find a freelancer profile by user ID.
     *
     * @param int $userId
     * @return FreelancerProfile|null
     */
    public function findByUserId(int $userId): ?FreelancerProfile;
}
