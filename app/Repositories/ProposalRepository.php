<?php

namespace App\Repositories;

use App\Models\Proposal;
use App\Repositories\Contracts\ProposalRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProposalRepository implements ProposalRepositoryInterface
{
    public function __construct(
        private Proposal $model
    ) {}

    /**
     * Create a new proposal.
     *
     * @param array $data
     * @return Proposal
     */
    public function create(array $data): Proposal
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing proposal.
     *
     * @param int $id
     * @param array $data
     * @return Proposal
     */
    public function update(int $id, array $data): Proposal
    {
        $proposal = $this->findById($id);
        $proposal->update($data);
        return $proposal->fresh();
    }

    /**
     * Find a proposal by ID.
     *
     * @param int $id
     * @return Proposal|null
     */
    public function findById(int $id): ?Proposal
    {
        return $this->model->find($id);
    }

    /**
     * Get all proposals for a user.
     *
     * @param int $userId
     * @return Collection
     */
    public function findByUserId(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Delete a proposal.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $proposal = $this->findById($id);
        return $proposal ? $proposal->delete() : false;
    }
}
