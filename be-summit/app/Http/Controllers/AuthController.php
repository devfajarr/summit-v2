<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct(
        protected OtpService $otpService
    ) {}

    #[OA\Post(
        path: '/api/register',
        summary: 'Register a new user (Pendaki)',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Jhon Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jhon@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully, verification OTP sent',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Registration successful. Please check your email for the OTP verification code.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['role'] = 'pendaki';
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Generate and send OTP via OtpService
        $this->otpService->generateAndSend($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful. Please check your email for the OTP verification code.',
            'data' => new UserResource($user),
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Login user',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jhon@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
                        new OA\Property(property: 'token', type: 'string', example: '2|xyz...')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 403, description: 'Email address is not verified')
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (!Auth::attempt($validated)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = User::where('email', $validated['email'])->firstOrFail();

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    #[OA\Post(
        path: '/api/verify-otp',
        summary: 'Verify email registration using OTP code',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'otp'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jhon@example.com'),
                    new OA\Property(property: 'otp', type: 'string', example: '123456')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email verified successfully, access token returned',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Email verified successfully.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/UserResource'),
                        new OA\Property(property: 'token', type: 'string', example: '3|abc...')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or expired OTP code'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->firstOrFail();

        $verified = $this->otpService->verifyOtp($user, $validated['otp']);

        if (!$verified) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired OTP code.',
            ], 400);
        }

        // Issue Sanctum token after successful verification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.',
            'data' => new UserResource($user),
            'token' => $token,
        ]);
    }

    #[OA\Post(
        path: '/api/resend-otp',
        summary: 'Resend OTP verification code to user email',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jhon@example.com')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP code resent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'A new OTP verification code has been sent to your email.')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Email is already verified'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email is already verified.',
            ], 400);
        }

        $this->otpService->generateAndSend($user);

        return response()->json([
            'status' => 'success',
            'message' => 'A new OTP verification code has been sent to your email.',
        ]);
    }
}
