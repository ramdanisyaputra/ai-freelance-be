<?php

namespace App\Services;

use App\Jobs\GenerateProposalJob;
use App\Models\Proposal;
use App\Models\User;
use App\Repositories\Contracts\ProposalRepositoryInterface;
use App\Traits\GetsAuthenticatedUser;
use Illuminate\Database\Eloquent\Collection;

class ProposalService
{
    use GetsAuthenticatedUser;

    public function __construct(
        private ProposalRepositoryInterface $proposalRepository
    ) {}

    public function generateProposal(array $data): Proposal
    {
        $user = $this->getAuthUser();
        $currency = $user?->freelancerProfile?->currency ?? 'IDR';

        $proposal = $this->proposalRepository->create([
            'user_id' => $user->id,
            'brief' => $data['brief'],
            'user_brief' => $data['user_brief'] ?? null,
            'language' => $data['language'] ?? 'id',
            'summary' => null,
            'scope' => [],
            'duration_days' => 0,
            'price' => 0,
            'currency' => $currency,
            'content' => '',
            'status' => 'processing',
        ]);

        GenerateProposalJob::dispatch($proposal);

        return $proposal;
    }

    public function updateProposal(int $id, array $data): Proposal
    {
        return $this->proposalRepository->update($id, $data);
    }

    public function updateProposalContent(int $id, array $data): Proposal
    {
        $content = $data['content'] ?? $data['proposal_html'] ?? '';
        $status = !empty($content) ? 'completed' : 'failed';

        return $this->proposalRepository->update($id, [
            'content' => $content,
            'scope' => $data['scope'] ?? [],
            'duration_days' => $data['estimation']['duration_days'] ?? 0,
            'price' => $data['estimation']['price'] ?? 0,
            'status' => $status,
        ]);
    }

    public function getProposalById(int $id): ?Proposal
    {
        return $this->proposalRepository->findById($id);
    }

    public function getUserProposals(int $userId): Collection
    {
        return $this->proposalRepository->findByUserId($userId);
    }

    public function deleteProposal(int $id): bool
    {
        return $this->proposalRepository->delete($id);
    }
}
