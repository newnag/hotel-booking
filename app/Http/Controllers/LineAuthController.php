<?php

namespace App\Http\Controllers;

use App\Services\LineNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineAuthController extends Controller
{
    /**
     * Redirect user to LINE Login page
     */
    public function redirect()
    {
        $channelId = config('services.line.channel_id');
        $redirectUri = config('services.line.redirect_uri');
        $state = bin2hex(random_bytes(16)); // CSRF protection
        $nonce = bin2hex(random_bytes(16)); // For ID token validation
        
        // Store state and nonce in session for verification
        session([
            'line_oauth_state' => $state,
            'line_oauth_nonce' => $nonce,
        ]);
        
        // Log for debugging
        Log::info('LINE Login redirect', [
            'channel_id' => $channelId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
        
        // Build LINE Login URL according to official documentation
        // https://developers.line.biz/en/reference/line-login/#authorize-request
        $params = [
            'response_type' => 'code',
            'client_id' => $channelId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => 'profile openid', // openid is required for getting user ID
            'nonce' => $nonce,
            'bot_prompt' => 'normal', // or 'aggressive' to encourage adding as friend
        ];
        
        $query = http_build_query($params);
        $url = "https://access.line.me/oauth2/v2.1/authorize?{$query}";
        
        Log::info('Redirecting to LINE Login', ['url' => $url]);
        
        return redirect($url);
    }
    
    /**
     * Handle callback from LINE Login
     */
    public function callback(Request $request)
    {
        Log::info('LINE callback received', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
            'has_error' => $request->has('error'),
        ]);
        
        // Check for error from LINE
        if ($request->has('error')) {
            Log::error('LINE Login error', [
                'error' => $request->error,
                'error_description' => $request->error_description,
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'ไม่สามารถเชื่อมต่อกับ LINE ได้: ' . ($request->error_description ?? $request->error));
        }
        
        // Verify state to prevent CSRF attacks
        $sessionState = session('line_oauth_state');
        if ($request->state !== $sessionState) {
            Log::error('LINE state mismatch', [
                'request_state' => $request->state,
                'session_state' => $sessionState,
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'การยืนยันตัวตนล้มเหลว กรุณาลองใหม่อีกครั้ง');
        }
        
        try {
            // Exchange authorization code for access token
            // https://developers.line.biz/en/reference/line-login/#issue-access-token
            $tokenResponse = Http::asForm()->post('https://api.line.me/oauth2/v2.1/token', [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config('services.line.redirect_uri'),
                'client_id' => config('services.line.channel_id'),
                'client_secret' => config('services.line.channel_secret'),
            ]);
            
            Log::info('LINE token response', [
                'status' => $tokenResponse->status(),
                'successful' => $tokenResponse->successful(),
            ]);
            
            if (!$tokenResponse->successful()) {
                Log::error('Failed to get access token', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body(),
                ]);
                throw new \Exception('Failed to get access token: ' . $tokenResponse->body());
            }
            
            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];
            
            // Get LINE user profile
            // https://developers.line.biz/en/reference/line-login/#get-user-profile
            $profileResponse = Http::withHeaders([
                'Authorization' => "Bearer {$accessToken}",
            ])->get('https://api.line.me/v2/profile');
            
            Log::info('LINE profile response', [
                'status' => $profileResponse->status(),
                'successful' => $profileResponse->successful(),
            ]);
            
            if (!$profileResponse->successful()) {
                Log::error('Failed to get user profile', [
                    'status' => $profileResponse->status(),
                    'body' => $profileResponse->body(),
                ]);
                throw new \Exception('Failed to get user profile: ' . $profileResponse->body());
            }
            
            $profile = $profileResponse->json();
            $lineUserId = $profile['userId'];
            
            Log::info('LINE user profile retrieved', [
                'userId' => $lineUserId,
                'displayName' => $profile['displayName'] ?? null,
            ]);
            
            // Update current user with LINE User ID
            $user = auth()->user();
            $user->line_user_id = $lineUserId;
            $user->line_linked_at = now();
            $user->save();
            
            Log::info("LINE account linked successfully", [
                'user_id' => $user->id,
                'line_user_id' => $lineUserId,
            ]);
            
            // Send welcome message via LINE
            try {
                $lineNotificationService = app(LineNotificationService::class);
                $lineNotificationService->sendWelcomeMessage($user);
            } catch (\Exception $e) {
                Log::warning("Failed to send welcome message: " . $e->getMessage());
                // Don't fail the whole process if welcome message fails
            }
            
            return redirect()->route('dashboard')
                ->with('success', 'เชื่อมต่อกับ LINE สำเร็จ! ตอนนี้คุณจะได้รับการแจ้งเตือนผ่าน LINE');
                
        } catch (\Exception $e) {
            Log::error('LINE OAuth error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'เกิดข้อผิดพลาดในการเชื่อมต่อกับ LINE: ' . $e->getMessage());
        }
    }
    
    /**
     * Unlink LINE account from user
     */
    public function unlink()
    {
        $user = auth()->user();
        
        if (!$user->line_user_id) {
            return redirect()->back()
                ->with('error', 'คุณยังไม่ได้เชื่อมต่อกับ LINE');
        }
        
        $user->line_user_id = null;
        $user->line_linked_at = null;
        $user->save();
        
        Log::info("LINE account unlinked for user {$user->id}");
        
        return redirect()->back()
            ->with('success', 'ยกเลิกการเชื่อมต่อกับ LINE เรียบร้อยแล้ว');
    }
}
