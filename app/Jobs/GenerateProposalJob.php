<?php

namespace App\Jobs;

use App\Models\Proposal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateProposalJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300; // 5 minutes for AI generation

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Proposal $proposal
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting proposal generation for ID: {$this->proposal->id}");

        try {
            $aiServiceUrl = config('services.ai.url');
            $callbackUrl = route('ai.callback.proposal', ['id' => $this->proposal->id]);

            // Load user with freelancer profile
            $user = $this->proposal->user()->with('freelancerProfile')->first();
            $profile = $user->freelancerProfile;

            $payload = [
                'proposal_id' => $this->proposal->id,
                'brief' => $this->proposal->brief,
                'user_brief' => $this->proposal->user_brief,
                'language' => $this->proposal->language ?? 'id',
                'freelancer_profile' => [
                    'role' => $profile?->role ?? 'Freelancer',
                    'stack' => $profile?->stack ?? ['Web Development'],
                    'rate_type' => $profile?->rate_type ?? 'project',
                    'min_price' => (int) ($profile?->min_price ?? 5000000),
                    'currency' => $profile?->currency ?? 'IDR',
                ],
                'callback_url' => $callbackUrl,
            ];

            $response = Http::timeout(30)
                ->post("{$aiServiceUrl}/api/generate-proposal", $payload);

            if ($response->successful()) {
                Log::info("AI service accepted proposal {$this->proposal->id}");
            } else {
                Log::error("AI service rejected proposal {$this->proposal->id}: " . $response->body());
                $this->proposal->update(['status' => 'failed']);
            }

        } catch (\Exception $e) {
            Log::error("Failed to send proposal {$this->proposal->id} to AI service: " . $e->getMessage());
            $this->proposal->update(['status' => 'failed']);
            throw $e;
        }
    }
}
