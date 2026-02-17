<?php

namespace App\Repositories\Contracts;

use App\Models\Proposal;
use Illuminate\Database\Eloquent\Collection;

interface ProposalRepositoryInterface
{
    public function create(array $data): Proposal;
    
    public function update(int $id, array $data): Proposal;
    
    public function findById(int $id): ?Proposal;
    
    public function findByUserId(int $userId): Collection;
    
    public function delete(int $id): bool;
}
