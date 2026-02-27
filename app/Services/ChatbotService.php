<?php

namespace App\Services;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Get or create a chat session
     */
    public function getOrCreateSession($userId, $sessionId = null, $type = 'general')
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if ($session) {
                return $session;
            }
        }

        // Create new session
        return ChatSession::create([
            'user_id' => $userId,
            'session_type' => $type,
            'is_active' => true,
            'started_at' => now(),
            'metadata' => []
        ]);
    }

    /**
     * Process intelligent chat message
     */
    public function processIntelligentChat($message, $session, $context = [])
    {
        $response = [
            'message' => '',
            'suggestions' => [],
            'actions' => [],
            'metadata' => []
        ];

        // Analyze message intent
        $intent = $this->analyzeIntent($message);
        
        switch ($intent) {
            case 'product_search':
                $response = $this->handleProductSearch($message, $context);
                break;
                
            case 'order_status':
                $response = $this->handleOrderInquiry($message, $session->user_id);
                break;
                
            case 'recommendation':
                $response = $this->handleRecommendationRequest($message, $session->user_id);
                break;
                
            case 'support':
                $response = $this->handleSupportRequest($message);
                break;
                
            case 'greeting':
                $response = $this->handleGreeting($session->user_id);
                break;
                
            default:
                $response = $this->handleGeneralQuery($message, $context);
        }

        return $response;
    }

    /**
     * Process product consultation
     */
    public function processProductConsultation($data, $session)
    {
        $query = $data['query'];
        $productId = $data['product_id'] ?? null;
        $category = $data['category'] ?? null;
        $budgetRange = $data['budget_range'] ?? null;
        $preferences = $data['preferences'] ?? [];
        $userId = $data['user_id'] ?? null;

        $consultation = [
            'response' => '',
            'recommendations' => [],
            'products' => [],
            'follow_up_questions' => [],
            'comparison_data' => null
        ];

        if ($productId) {
            // Specific product consultation
            $consultation = $this->consultSpecificProduct($productId, $query, $preferences);
        } elseif ($category) {
            // Category-based consultation
            $consultation = $this->consultProductCategory($category, $query, $budgetRange, $preferences, $userId);
        } else {
            // General product consultation
            $consultation = $this->consultGeneralProducts($query, $budgetRange, $preferences, $userId);
        }

        return $consultation;
    }

    /**
     * Get personalized recommendations
     */
    public function getPersonalizedRecommendations($userId, $limit = 10)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return Product::popular()->take($limit)->get();
            }

            // Get user's order history and preferences
            $userOrders = $user->orders()->with('items.product')->get();
            $userCategories = collect();
            $userPriceRange = ['min' => 0, 'max' => 0];

            foreach ($userOrders as $order) {
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $userCategories->push($item->product->category_id);
                        $userPriceRange['max'] = max($userPriceRange['max'], $item->product->price);
                        if ($userPriceRange['min'] == 0) {
                            $userPriceRange['min'] = $item->product->price;
                        } else {
                            $userPriceRange['min'] = min($userPriceRange['min'], $item->product->price);
                        }
                    }
                }
            }

            // Get recommendations based on user behavior
            $recommendations = Product::whereIn('category_id', $userCategories->unique())
                ->whereBetween('price', [$userPriceRange['min'] * 0.7, $userPriceRange['max'] * 1.3])
                ->where('is_active', true)
                ->inRandomOrder()
                ->take($limit)
                ->get();

            if ($recommendations->count() < $limit) {
                $additionalProducts = Product::popular()
                    ->whereNotIn('id', $recommendations->pluck('id'))
                    ->take($limit - $recommendations->count())
                    ->get();
                
                $recommendations = $recommendations->merge($additionalProducts);
            }

            return $recommendations;

        } catch (\Exception $e) {
            Log::error('Error getting personalized recommendations', ['error' => $e->getMessage(), 'user_id' => $userId]);
            return Product::popular()->take($limit)->get();
        }
    }

    /**
     * Store conversation message
     */
    public function storeConversation($session, $userMessage, $botResponse, $metadata = [])
    {
        // Store user message
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'message' => $userMessage,
            'sender_type' => 'user',
            'metadata' => $metadata
        ]);

        // Store bot response
        ChatMessage::create([
            'chat_session_id' => $session->id,
            'message' => $botResponse,
            'sender_type' => 'bot',
            'metadata' => $metadata
        ]);
    }

    /**
     * Analyze message intent using simple keyword matching
     */
    private function analyzeIntent($message)
    {
        $message = strtolower($message);

        if (preg_match('/\b(hello|hi|hey|good morning|good afternoon|good evening)\b/', $message)) {
            return 'greeting';
        }

        if (preg_match('/\b(search|find|looking for|need|want|show me)\b/', $message)) {
            return 'product_search';
        }

        if (preg_match('/\b(order|delivery|track|status|shipped|delivered)\b/', $message)) {
            return 'order_status';
        }

        if (preg_match('/\b(recommend|suggest|advice|best|popular|trending)\b/', $message)) {
            return 'recommendation';
        }

        if (preg_match('/\b(help|support|problem|issue|complaint|refund|return)\b/', $message)) {
            return 'support';
        }

        return 'general';
    }

    /**
     * Handle product search queries
     */
    private function handleProductSearch($message, $context = [])
    {
        $keywords = $this->extractKeywords($message);
        
        $products = Product::where('is_active', true)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhere('name', 'like', "%{$keyword}%")
                          ->orWhere('description', 'like', "%{$keyword}%");
                }
            })
            ->take(5)
            ->get();

        if ($products->count() > 0) {
            $response = "I found " . $products->count() . " products that match your search:";
            $suggestions = $products->map(function ($product) {
                return "View " . $product->name;
            })->toArray();
        } else {
            $response = "I couldn't find any products matching your search. Would you like me to show you our popular products instead?";
            $suggestions = ['Show popular products', 'Browse categories', 'Get recommendations'];
        }

        return [
            'message' => $response,
            'suggestions' => $suggestions,
            'actions' => $products->count() > 0 ? ['show_products'] : ['show_alternatives'],
            'metadata' => ['products' => $products->take(3)->toArray()]
        ];
    }

    /**
     * Handle order inquiry
     */
    private function handleOrderInquiry($message, $userId)
    {
        $user = User::find($userId);
        $recentOrders = $user ? $user->orders()->latest()->take(3)->get() : collect();

        if ($recentOrders->count() > 0) {
            $response = "Here are your recent orders:\n";
            foreach ($recentOrders as $order) {
                $response .= "Order #{$order->id} - Status: {$order->status} - ₹{$order->total}\n";
            }
        } else {
            $response = "You don't have any recent orders. Would you like to browse our products?";
        }

        return [
            'message' => $response,
            'suggestions' => $recentOrders->count() > 0 ? ['Track order', 'View all orders'] : ['Browse products', 'View categories'],
            'actions' => ['show_orders'],
            'metadata' => ['orders' => $recentOrders->toArray()]
        ];
    }

    /**
     * Handle recommendation requests
     */
    private function handleRecommendationRequest($message, $userId)
    {
        $recommendations = $this->getPersonalizedRecommendations($userId, 5);

        return [
            'message' => "Based on your preferences, here are my top recommendations:",
            'suggestions' => $recommendations->map(function ($product) {
                return "View " . $product->name;
            })->toArray(),
            'actions' => ['show_recommendations'],
            'metadata' => ['recommendations' => $recommendations->toArray()]
        ];
    }

    /**
     * Handle support requests
     */
    private function handleSupportRequest($message)
    {
        return [
            'message' => "I'm here to help! What specific issue can I assist you with?",
            'suggestions' => [
                'Order issues',
                'Product questions',
                'Account problems',
                'Shipping information',
                'Returns & refunds'
            ],
            'actions' => ['show_support_options'],
            'metadata' => ['support_context' => true]
        ];
    }

    /**
     * Handle greeting messages
     */
    private function handleGreeting($userId)
    {
        $user = User::find($userId);
        $name = $user ? $user->name : 'there';

        return [
            'message' => "Hello {$name}! 👋 Welcome to our store. How can I help you today?",
            'suggestions' => [
                'Show me trending products',
                'Help me find something specific',
                'Check my orders',
                'Get recommendations'
            ],
            'actions' => ['show_main_options'],
            'metadata' => ['greeting' => true]
        ];
    }

    /**
     * Handle general queries
     */
    private function handleGeneralQuery($message, $context = [])
    {
        return [
            'message' => "I understand you're asking about: \"" . substr($message, 0, 100) . "\". Could you be more specific so I can better assist you?",
            'suggestions' => [
                'Search for products',
                'Get recommendations',
                'Check order status',
                'Contact support'
            ],
            'actions' => ['clarify_intent'],
            'metadata' => ['original_query' => $message]
        ];
    }

    /**
     * Consult specific product
     */
    private function consultSpecificProduct($productId, $query, $preferences)
    {
        $product = Product::with(['category', 'reviews'])->find($productId);
        
        if (!$product) {
            return [
                'response' => 'Sorry, I couldn\'t find the product you\'re asking about.',
                'recommendations' => [],
                'products' => [],
                'follow_up_questions' => ['Would you like me to find similar products?']
            ];
        }

        $response = "Let me tell you about the {$product->name}:\n\n";
        $response .= "Price: ₹{$product->price}\n";
        $response .= "Category: {$product->category->name}\n";
        $response .= "Rating: {$product->average_rating}/5 based on {$product->reviews_count} reviews\n\n";
        $response .= substr($product->description, 0, 200) . "...";

        // Get similar products
        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        return [
            'response' => $response,
            'recommendations' => $similarProducts,
            'products' => [$product],
            'follow_up_questions' => [
                'Would you like to see similar products?',
                'Do you need help with sizing or specifications?',
                'Would you like to read customer reviews?'
            ]
        ];
    }

    /**
     * Consult product category
     */
    private function consultProductCategory($categorySlug, $query, $budgetRange, $preferences, $userId)
    {
        $category = Category::where('slug', $categorySlug)->first();
        
        if (!$category) {
            return [
                'response' => 'Sorry, I couldn\'t find the category you\'re looking for.',
                'recommendations' => [],
                'products' => [],
                'follow_up_questions' => ['Would you like to browse all categories?']
            ];
        }

        $productsQuery = Product::where('category_id', $category->id)
            ->where('is_active', true);

        // Apply budget filter if provided
        if ($budgetRange) {
            $range = $this->parseBudgetRange($budgetRange);
            if ($range) {
                $productsQuery->whereBetween('price', [$range['min'], $range['max']]);
            }
        }

        $products = $productsQuery->take(6)->get();

        $response = "I found {$products->count()} great products in the {$category->name} category";
        if ($budgetRange) {
            $response .= " within your budget range of {$budgetRange}";
        }
        $response .= ". Here are my top recommendations:";

        return [
            'response' => $response,
            'recommendations' => $products,
            'products' => $products,
            'follow_up_questions' => [
                'Would you like to filter by specific features?',
                'Do you need help choosing between options?',
                'Would you like to see customer favorites?'
            ]
        ];
    }

    /**
     * General product consultation
     */
    private function consultGeneralProducts($query, $budgetRange, $preferences, $userId)
    {
        $keywords = $this->extractKeywords($query);
        
        $productsQuery = Product::where('is_active', true);

        // Search by keywords
        if (!empty($keywords)) {
            $productsQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        }

        // Apply budget filter
        if ($budgetRange) {
            $range = $this->parseBudgetRange($budgetRange);
            if ($range) {
                $productsQuery->whereBetween('price', [$range['min'], $range['max']]);
            }
        }

        $products = $productsQuery->take(6)->get();

        if ($products->count() > 0) {
            $response = "Based on your requirements, I found {$products->count()} products that might interest you:";
        } else {
            $response = "I couldn't find products matching your exact requirements. Let me show you some popular alternatives:";
            $products = Product::popular()->take(6)->get();
        }

        return [
            'response' => $response,
            'recommendations' => $products,
            'products' => $products,
            'follow_up_questions' => [
                'Would you like to refine your search?',
                'Do you need more details about any product?',
                'Would you like to see products in a specific category?'
            ]
        ];
    }

    /**
     * Extract keywords from message
     */
    private function extractKeywords($message)
    {
        $stopWords = ['i', 'me', 'my', 'myself', 'we', 'our', 'ours', 'ourselves', 'you', 'your', 'yours', 'yourself', 'yourselves', 'he', 'him', 'his', 'himself', 'she', 'her', 'hers', 'herself', 'it', 'its', 'itself', 'they', 'them', 'their', 'theirs', 'themselves', 'what', 'which', 'who', 'whom', 'this', 'that', 'these', 'those', 'am', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'having', 'do', 'does', 'did', 'doing', 'a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'until', 'while', 'of', 'at', 'by', 'for', 'with', 'through', 'during', 'before', 'after', 'above', 'below', 'up', 'down', 'in', 'out', 'on', 'off', 'over', 'under', 'again', 'further', 'then', 'once'];
        
        $words = str_word_count(strtolower($message), 1);
        return array_diff($words, $stopWords);
    }

    /**
     * Parse budget range string
     */
    private function parseBudgetRange($budgetRange)
    {
        if (preg_match('/(\d+)\s*-\s*(\d+)/', $budgetRange, $matches)) {
            return ['min' => (int)$matches[1], 'max' => (int)$matches[2]];
        }
        
        if (preg_match('/under\s*(\d+)/', $budgetRange, $matches)) {
            return ['min' => 0, 'max' => (int)$matches[1]];
        }
        
        if (preg_match('/over\s*(\d+)/', $budgetRange, $matches)) {
            return ['min' => (int)$matches[1], 'max' => 999999];
        }
        
        return null;
    }
}