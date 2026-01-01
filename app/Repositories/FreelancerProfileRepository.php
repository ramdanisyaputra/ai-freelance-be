<?php

namespace App\Repositories;

use App\Models\FreelancerProfile;
use App\Repositories\Contracts\FreelancerProfileRepositoryInterface;

class FreelancerProfileRepository implements FreelancerProfileRepositoryInterface
{
    /**
     * Create a new freelancer profile.
     *
     * @param int $userId
     * @param array $data
     * @return FreelancerProfile
     */
    public function create(int $userId, array $data): FreelancerProfile
    {
        return FreelancerProfile::create([
            'user_id' => $userId,
            'stack' => $data['stack'],
            'rate_type' => $data['rate_type'],
            'min_price' => $data['min_price'],
            'currency' => $data['currency'] ?? 'IDR',
        ]);
    }

    /**
     * Find a freelancer profile by user ID.
     *
     * @param int $userId
     * @return FreelancerProfile|null
     */
    public function findByUserId(int $userId): ?FreelancerProfile
    {
        return FreelancerProfile::where('user_id', $userId)->first();
    }
}
