<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Email Verification",
    description: "API Endpoints for email verification"
)]
class VerificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/email/verify/{id}/{hash}",
     *     summary="Verify user email with URL from email",
     *     tags={"Email Verification"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification link"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);
        
        // Check if URL is valid
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'message' => 'Invalid verification link'
            ], 400);
        }

        // Check if user is already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 200);
        }

        // Mark email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/verification-notification",
     *     summary="Resend verification email",
     *     tags={"Email Verification"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification link sent"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many attempts"
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent'
        ]);
    }
}