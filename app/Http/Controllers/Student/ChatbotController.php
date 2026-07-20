<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        try {
            $userMessage = $request->input('message');
            
            // System prompt that defines what the chatbot can answer
            $systemPrompt = $this->buildSystemPrompt();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $userMessage
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if ($response->successful()) {
                $answer = $response->json('choices.0.message.content');
                return response()->json([
                    'success' => true,
                    'answer' => $answer
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Unable to get response from AI service'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildSystemPrompt()
    {
        return <<<'EOT'
You are a helpful student assistant for the SSC (Supreme Student Council) Transparency and Budget Allocation System. 

Your role is to answer student questions about:
1. Budget information and allocations
2. Announcements and important dates
3. How to vote and candidacy procedures
4. General information about SSC events and activities
5. How to provide feedback to SSC officers
6. Viewing officer information and contact details
7. System navigation and feature explanations

Guidelines:
- Be helpful, friendly, and professional
- Only answer questions related to the SSC system and student activities
- If a question is outside your scope, politely explain that you cannot help with that
- Provide clear, concise answers in 1-2 sentences when possible
- Suggest checking specific pages in the system if more information is needed
- If you don't know the answer, recommend the student contact the SSC or administrators
- Never share sensitive information or bypass security
- Focus on helping students understand how to use the system and its features

Example appropriate questions:
- "How do I submit a proposal?"
- "What is the current budget allocation?"
- "How do I vote in the election?"
- "How can I give feedback?"

Example inappropriate questions (politely decline):
- Personal data requests
- System administration tasks
- Questions about other students' information
EOT;
    }
}
