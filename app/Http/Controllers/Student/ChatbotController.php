<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $userMessage = $request->input('message');
        $apiKey = trim(env('OPENAI_API_KEY', ''));
        
        $isPlaceholder = empty($apiKey) || 
                         str_contains(strtolower($apiKey), 'your-api-key') || 
                         str_contains(strtolower($apiKey), 'placeholder') ||
                         str_contains(strtolower($apiKey), 'sk-your');

        // 1. Fallback to Local Rules-Based Responder if API Key is empty or placeholder
        if ($isPlaceholder) {
            return response()->json([
                'success' => true,
                'answer'  => $this->getFallbackResponse($userMessage)
            ]);
        }

        // 2. Attempt OpenAI Completion
        try {
            $systemPrompt = $this->buildSystemPrompt();
            
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
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
                    'answer'  => $answer
                ]);
            }

            Log::warning('OpenAI Chatbot API failed, utilizing fallback responder.', [
                'status' => $response->status(),
                'error'  => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI Chatbot exception caught, utilizing fallback responder.', [
                'message' => $e->getMessage()
            ]);
        }

        // Graceful fallback to prevent 500 errors and keep the user experience seamless
        return response()->json([
            'success' => true,
            'answer'  => $this->getFallbackResponse($userMessage)
        ]);
    }

    private function getFallbackResponse(string $input): string
    {
        $normalized = strtolower(trim($input));

        if (str_contains($normalized, 'budget') || str_contains($normalized, 'fund') || str_contains($normalized, 'transparency')) {
            return "Want to track where your student fees go? 📊\n\nWe maintain full transparency of our budget:\n• Check the Summary Dashboard to see real-time charts of allocated versus spent funds.\n• Check the Proposals Portal to review specific project budgets, liquidation logs, and uploaded receipts for completed projects.";
        }

        if (str_contains($normalized, 'proposal') || str_contains($normalized, 'project') || str_contains($normalized, 'submit')) {
            return "Want to submit a project proposal? 📝\n\nStudent organizations and department representatives can request Supreme Student Council (SSC) funding easily:\n1. Navigate to the Proposals Portal on your sidebar.\n2. Click the Submit Proposal button and fill in the project title, expected timeline, and estimated budget.\n3. Once submitted, it will appear on the discussions list for student feedback and voting.";
        }

        if (str_contains($normalized, 'feedback') || str_contains($normalized, 'concern') || str_contains($normalized, 'suggestion')) {
            return "Your voice is essential to build a better campus! 💬\n\nTo share feedback, suggestions, or concerns with the council:\n1. Open the Student Feedback Wall.\n2. Write your message and choose the type (Suggestion, Inquiry, or Concern).\n3. Check Submit Anonymously to keep your identity private if preferred.\n4. All submissions are read and addressed directly by the SSC Executive Committee.";
        }

        if (str_contains($normalized, 'contact') || str_contains($normalized, 'officer') || str_contains($normalized, 'reach') || str_contains($normalized, 'email')) {
            return "Let's stay connected! 📞\n\nYou can reach the SSC officers through our official channels:\n• Email: ssc.official@mcclawis.edu.ph\n• Facebook: SSC Official Facebook Page\n• Office: Student Center, 2nd Floor, MCC Campus\n• Office Hours: Mon-Fri | 8:00 AM – 5:00 PM";
        }

        if (str_contains($normalized, 'vote') || str_contains($normalized, 'voting') || str_contains($normalized, 'election')) {
            return "Interested in participating in the elections? 🗳️\n\nWhen voting is active, you can cast your secure ballot in 3 simple steps:\n1. Open the Voting Portal in the app menu.\n2. Review candidate platform and position details.\n3. Select your preferred candidates and tap the Cast Ballot button to safely record your vote.";
        }

        if (str_contains($normalized, 'candidacy') || str_contains($normalized, 'run') || str_contains($normalized, 'candidate')) {
            return "Are you running for office? 🚀\n\nStudents can file for official candidacy through our platform:\n1. Visit the Candidacy Portal.\n2. Select your desired role and enter your campaign platform details.\n3. Note that eligibility is limited by department restrictions and active election timelines set by the administration.";
        }

        if (str_contains($normalized, 'hello') || str_contains($normalized, 'hi') || str_contains($normalized, 'hey')) {
            return "Hi there! 👋 I'm your SSC assistant. I can help you with student concerns, proposals, anonymous feedback, and budget tracking. What can I do for you today?";
        }

        if (str_contains($normalized, 'thanks') || str_contains($normalized, 'thank')) {
            return "You're very welcome! Let me know if there's anything else I can do to help you navigate the system. 🚀";
        }

        return "I'm sorry, I don't have a specific answer for that.\n\nTry asking about:\n• proposals\n• anonymous feedback\n• track budgets\n• contact ssc\n• voting\n• candidacy";
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
