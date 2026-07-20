# SSC Student Chatbot - Setup Guide

## Overview
This guide explains how to set up and configure the OpenAI chatbot for the SSC Student Overview page.

## What Was Added

1. **Student Dashboard/Overview Page** (`/student`)
   - Displays welcome message with student name
   - Shows latest announcements
   - Displays active proposals
   - Shows candidacy status
   - Integrated AI chatbot sidebar

2. **AI Chatbot Component**
   - Real-time chat interface
   - Answers questions about SSC system
   - Limited to appropriate student questions
   - Powered by OpenAI GPT-3.5

3. **Routes Added**
   - `GET /student` - Student Overview (Dashboard)
   - `POST /student/chatbot/chat` - Chatbot API endpoint

## Setup Instructions

### Step 1: Get OpenAI API Key

1. Go to https://platform.openai.com/
2. Sign up for an account (if you don't have one)
3. Navigate to **API Keys** section
4. Click **Create new secret key**
5. Copy the key (you'll only see it once)

### Step 2: Configure Environment

1. Open your `.env` file in the project root
2. Add the following line:
   ```
   OPENAI_API_KEY=sk-your-api-key-here
   ```
3. Replace `sk-your-api-key-here` with your actual API key
4. Save the file

**Important:** Never commit the `.env` file to version control. Add it to `.gitignore` if not already there.

### Step 3: Install/Update Dependencies

Make sure `guzzlehttp/guzzle` is installed (used for HTTP requests):

```bash
composer require guzzlehttp/guzzle
```

### Step 4: Clear Cache

Run the following to clear application cache:

```bash
php artisan config:cache
php artisan cache:clear
```

### Step 5: Test the Chatbot

1. Navigate to `/student` in your browser (after logging in as a student)
2. Try asking the chatbot questions like:
   - "How do I submit a proposal?"
   - "When is the next election?"
   - "How do I vote?"
   - "What can I do on this system?"

## Features

### What the Chatbot Can Answer
- Budget: allocations, current amounts, spending information
- Voting: election information, voting procedures
- Candidacy: application process, status checks
- Announcements: latest updates, important dates
- Officers: finding contact information
- Feedback: how to submit feedback
- General system navigation and features

### What the Chatbot Won't Answer
- Personal student data
- System administration tasks
- Other students' information
- Sensitive security matters
- Topics outside the SSC system

## Cost Considerations

**OpenAI API Pricing:**
- GPT-3.5-turbo: approximately $0.001 per 1,000 tokens
- Average response: ~100-200 tokens
- Estimated cost: $0.01 per chat interaction

**Cost-Saving Tips:**
- Set rate limiting on the chatbot endpoint
- Implement caching for common questions
- Monitor API usage regularly
- Set monthly spending limits in OpenAI dashboard

## Monitoring and Troubleshooting

### Check API Usage
1. Go to https://platform.openai.com/usage
2. View current month's usage and costs

### Common Issues

**"Unable to get response from AI service"**
- Verify API key is correct
- Check OpenAI API status at https://status.openai.com
- Ensure rate limits aren't exceeded

**Chatbot not responding**
- Check browser console for errors (F12)
- Verify CSRF token in HTML
- Check Laravel logs: `storage/logs/laravel.log`

**API Key errors**
- Verify key starts with `sk-`
- Ensure no extra spaces in `.env`
- Clear cache with: `php artisan config:cache`

## Customization

### Change Model
Edit `/app/Http/Controllers/Student/ChatbotController.php`:
```php
'model' => 'gpt-3.5-turbo',  // Change to: gpt-4, gpt-4-turbo, etc.
```

### Adjust Response Length
Modify `max_tokens`:
```php
'max_tokens' => 500,  // Increase for longer responses
```

### Modify System Prompt
Edit the `buildSystemPrompt()` method in ChatbotController to customize what the chatbot can answer.

### Change Appearance
Styles are in `resources/views/student/overview.blade.php` - modify the CSS section to match your design.

## API Endpoint Details

**Endpoint:** `POST /student/chatbot/chat`

**Request:**
```json
{
  "message": "How do I submit a proposal?"
}
```

**Response (Success):**
```json
{
  "success": true,
  "answer": "To submit a proposal, navigate to the Proposals page..."
}
```

**Response (Error):**
```json
{
  "success": false,
  "error": "Unable to get response from AI service"
}
```

## Security Notes

1. API key is stored in `.env` - never commit this file
2. The chatbot validates all input (max 2000 characters)
3. Only authenticated students can access the chatbot
4. All requests are CSRF-protected
5. Never log or store sensitive student information

## Performance Tips

1. Consider implementing message history with database storage
2. Add caching for common questions using Redis
3. Implement rate limiting per student
4. Monitor response times and optimize prompts if needed

## Support Resources

- OpenAI Documentation: https://platform.openai.com/docs
- Laravel Documentation: https://laravel.com/docs
- Guzzle HTTP Client: https://docs.guzzlephp.org

---

**Last Updated:** 2026-07-20
