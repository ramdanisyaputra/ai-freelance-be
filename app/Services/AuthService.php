<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\FreelancerProfileRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private FreelancerProfileRepositoryInterface $profileRepository
    ) {}

    /**
     * Register a new user with freelancer profile.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        // Create user
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // Create freelancer profile
        $profile = $this->profileRepository->create($user->id, [
            'stack' => $data['stack'],
            'rate_type' => $data['rate_type'],
            'min_price' => $data['min_price'],
            'currency' => $data['currency'] ?? 'IDR',
        ]);

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('freelancerProfile'),
            'token' => $token,
        ];
    }

    /**
     * Login a user.
     *
     * @param string $email
     * @param string $password
     * @return array
     * @throws ValidationException
     */
    public function login(string $email, string $password): array
    {
        // Find user
        $user = $this->userRepository->findByEmail($email);

        // Validate credentials
        if (!$user || !Auth::attempt(['email' => $email, 'password' => $password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('freelancerProfile'),
            'token' => $token,
        ];
    }

    public function logout($user)
    {
        $accessToken = $user->currentAccessToken();
        
        if ($accessToken instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $accessToken->delete();
        }

        return 'Logged out';
    }
}
