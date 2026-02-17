<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateProposalRequest;
use App\Services\ProposalService;
use App\Traits\ResponseTrait;
use App\Traits\GetsAuthenticatedUser;
use App\Events\ProposalGenerated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    use ResponseTrait, GetsAuthenticatedUser;

    public function __construct(
        private ProposalService $proposalService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $proposals = $this->proposalService->getUserProposals($this->getAuthUserId());

            return $this->successResponse([
                'proposals' => $proposals,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Proposals Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to retrieve proposals.');
        }
    }

    public function generate(GenerateProposalRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proposal = $this->proposalService->generateProposal(
                $request->validated()
            );

            DB::commit();

            return $this->successResponse([
                'message' => 'Proposal generation started',
                'id' => $proposal->id,
                'status' => $proposal->status,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate Proposal Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to generate proposal. Please try again.');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                return $this->notFoundResponse('Proposal not found.');
            }

            // Check if user owns the proposal
            if ($proposal->user_id !== $this->getAuthUserId()) {
                return $this->forbiddenResponse('You do not have permission to update this proposal.');
            }

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'content' => 'sometimes|string',
                'summary' => 'sometimes|string',
                'scope' => 'sometimes|array',
            ]);

            $updatedProposal = $this->proposalService->updateProposal($id, $validated);

            DB::commit();

            return $this->successResponse([
                'message' => 'Proposal updated successfully',
                'proposal' => $updatedProposal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Proposal Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to update proposal.');
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                return $this->notFoundResponse('Proposal not found.');
            }

            // Check if user owns the proposal
            if ($proposal->user_id !== $this->getAuthUserId()) {
                return $this->forbiddenResponse('You do not have permission to view this proposal.');
            }

            return $this->successResponse([
                'proposal' => $proposal,
            ]);
        } catch (\Exception $e) {
            Log::error('Get Proposal Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to retrieve proposal.');
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                return $this->notFoundResponse('Proposal not found.');
            }

            // Check if user owns the proposal
            if ($proposal->user_id !== $this->getAuthUserId()) {
                return $this->forbiddenResponse('You do not have permission to delete this proposal.');
            }

            $this->proposalService->deleteProposal($id);

            DB::commit();

            return $this->successResponse([
                'message' => 'Proposal deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Proposal Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to delete proposal.');
        }
    }

    public function callback(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $proposal = $this->proposalService->updateProposalContent($id, $request->all());

            broadcast(new ProposalGenerated($proposal))->toOthers();

            DB::commit();

            return $this->successResponse([
                'message' => 'Proposal updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Proposal Callback Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to update proposal.');
        }
    }
}
