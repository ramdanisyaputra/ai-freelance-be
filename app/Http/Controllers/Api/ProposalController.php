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
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function exportPdf(int $id)
    {
        try {
            $proposal = $this->proposalService->getProposalById($id);

            if (!$proposal) {
                return $this->notFoundResponse('Proposal not found.');
            }

            // Check if user owns the proposal
            if ($proposal->user_id !== $this->getAuthUserId()) {
                return $this->forbiddenResponse('You do not have permission to export this proposal.');
            }

            // Increase execution time for PDF generation
            set_time_limit(300);

            // Optimize images: Replace public URLs with local file paths to avoid HTTP requests loop and timeout
            $content = $proposal->content;
            
            // Pattern to match image src attributes
            $pattern = '/<img[^>]+src="([^">]+)"/i';
            
            $content = preg_replace_callback($pattern, function ($matches) {
                $src = $matches[1];
                
                // Check if the URL contains '/storage/' which indicates it's a local file
                if (strpos($src, '/storage/') !== false) {
                    // Extract path starting from /storage/
                    // e.g. http://localhost:8000/storage/images/xyz.png -> /images/xyz.png
                    $parts = explode('/storage/', $src);
                    if (count($parts) > 1) {
                        $relativePath = '/' . $parts[1];
                        
                        // Construct local file path
                        $localPath = storage_path('app/public' . $relativePath);
                        
                        Log::info("PDF Image Path: " . $localPath);

                        if (file_exists($localPath)) {
                            // Convert to base64 to avoid any path/permission issues with dompdf
                            try {
                                $type = pathinfo($localPath, PATHINFO_EXTENSION);
                                $data = file_get_contents($localPath);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                return str_replace($src, $base64, $matches[0]);
                            } catch (\Exception $e) {
                                Log::error("Failed to convert image to base64: " . $e->getMessage());
                                // Fallback to local path if base64 conversion fails
                                return str_replace($src, 'file://' . $localPath, $matches[0]);
                            }
                        } else {
                            Log::warning("PDF Image not found at: " . $localPath);
                        }
                    }
                }
                
                return $matches[0];
            }, $content);

            // Temporarily replace content for PDF generation
            $proposal->content = $content;

            $pdf = Pdf::loadView('pdf.proposal', compact('proposal'));
            
            // Set paper size and orientation if needed (A4 Portrait is default for DomPDF usually)
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

            return $pdf->stream('proposal-' . $id . '.pdf');
        } catch (\Exception $e) {
            Log::error('Export PDF Error: ' . $e->getMessage());
            return $this->internalServerErrorResponse('Failed to generate PDF.');
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
