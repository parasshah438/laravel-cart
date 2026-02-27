<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use App\Models\ChatSession;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
        //$this->middleware('auth')->except(['shoppingAssistant']);
    }

    /**
     * Handle intelligent chat conversations
     */
    public function intelligentChat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'session_id' => 'nullable|string',
                'context' => 'nullable|array'
            ]);

            $userId = Auth::id();
            $message = $request->input('message');
            $sessionId = $request->input('session_id');
            $context = $request->input('context', []);

            // Get or create chat session
            $session = $this->chatbotService->getOrCreateSession($userId, $sessionId, 'intelligent_chat');

            // Process the message and get AI response
            $response = $this->chatbotService->processIntelligentChat($message, $session, $context);

            // Store the conversation
            $this->chatbotService->storeConversation($session, $message, $response['message'], $context);

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'message' => $response['message'],
                'suggestions' => $response['suggestions'] ?? [],
                'actions' => $response['actions'] ?? [],
                'metadata' => $response['metadata'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Intelligent chat error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'message' => $request->input('message')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Handle product consultation requests
     */
    public function productConsultation(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|max:1000',
                'product_id' => 'nullable|integer|exists:products,id',
                'category' => 'nullable|string',
                'budget_range' => 'nullable|string',
                'preferences' => 'nullable|array',
                'session_id' => 'nullable|string'
            ]);

            $userId = Auth::id();
            $query = $request->input('query');
            $productId = $request->input('product_id');
            $category = $request->input('category');
            $budgetRange = $request->input('budget_range');
            $preferences = $request->input('preferences', []);
            $sessionId = $request->input('session_id');

            // Get or create consultation session
            $session = $this->chatbotService->getOrCreateSession($userId, $sessionId, 'product_consultation');

            // Process product consultation
            $consultation = $this->chatbotService->processProductConsultation([
                'query' => $query,
                'product_id' => $productId,
                'category' => $category,
                'budget_range' => $budgetRange,
                'preferences' => $preferences,
                'user_id' => $userId
            ], $session);

            // Store consultation record
            $this->chatbotService->storeConversation(
                $session, 
                $query, 
                $consultation['response'], 
                ['type' => 'consultation', 'products' => $consultation['products'] ?? []]
            );

            return response()->json([
                'success' => true,
                'session_id' => $session->id,
                'response' => $consultation['response'],
                'recommendations' => $consultation['recommendations'] ?? [],
                'products' => $consultation['products'] ?? [],
                'follow_up_questions' => $consultation['follow_up_questions'] ?? [],
                'comparison_data' => $consultation['comparison_data'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('Product consultation error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'query' => $request->input('query')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sorry, I couldn\'t process your consultation request. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show shopping assistant interface
     */
    public function shoppingAssistant(Request $request)
    {
        try {
            $userId = Auth::id();
            $categories = \App\Models\Category::active()->get();
            $recentProducts = [];
            $recommendations = [];
            
            if ($userId) {
                // Get user's recent products and recommendations
                $recentProducts = Product::whereHas('views', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->latest()->take(10)->get();

                $recommendations = $this->chatbotService->getPersonalizedRecommendations($userId, 6);
            } else {
                // Get popular products for guests
                $recommendations = Product::take(6)->get();
                //dd($recommendations);
            }

            // Get active chat sessions
            $activeSessions = $userId 
                ? ChatSession::where('user_id', $userId)
                    ->where('is_active', true)
                    ->latest()
                    ->take(5)
                    ->get()
                : collect();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'categories' => $categories,
                        'recent_products' => $recentProducts,
                        'recommendations' => $recommendations,
                        'active_sessions' => $activeSessions,
                        'user_authenticated' => (bool) $userId
                    ]
                ]);
            }

            //dd($categories)
            return view('chatbot.shopping-assistant', compact(
                'categories', 
                'recentProducts', 
                'recommendations', 
                'activeSessions'
            ));

        } catch (\Exception $e) {
            //dd($e->getMessage());
            Log::error('Shopping assistant error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to load shopping assistant.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }

            return view('chatbot.shopping-assistant')->with('error', 'Unable to load shopping assistant data.');
        }
    }

    /**
     * Get chat history for a session
     */
    public function getChatHistory(Request $request, $sessionId)
    {
        try {
            $userId = Auth::id();
            
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $userId)
                ->with('messages')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'session' => $session,
                'messages' => $session->messages()->latest()->paginate(50)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chat session not found.'
            ], 404);
        }
    }

    /**
     * End a chat session
     */
    public function endSession(Request $request, $sessionId)
    {
        try {
            $userId = Auth::id();
            
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $userId)
                ->firstOrFail();

            $session->update(['is_active' => false, 'ended_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Chat session ended successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to end chat session.'
            ], 500);
        }
    }
}