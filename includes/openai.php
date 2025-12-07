<?php
// OpenAI API Integration | Today's Delulu Fortune

require_once __DIR__ . '/config.php';

class OpenAI {
    private $apiKey;
    private $model;
    
    public function __construct() {
        $this->apiKey = OPENAI_API_KEY;
        $this->model = OPENAI_MODEL;
        
        if (empty($this->apiKey)) {
            throw new Exception("OpenAI API key not configured in includes/config.php");
        }
    }
    
    // Generate multiple fortunes at once (more efficient)
    public function generateFortunes($count = 5) {
        $prompt = $this->buildBatchPrompt($count);
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a mystical fortune teller with modern, chaotic humor. You give fortunes relatable to millennials and Gen-Z, mixing cosmic wisdom with everyday struggles like UPI payments, texting drama, and food delivery. Your fortunes are slightly hopeful but absurdly specific. Keep each fortune to 1-2 sentences max.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.95,
        ];
        
        $response = $this->makeRequest($data);
        
        if (isset($response['choices'][0]['message']['content'])) {
            return $this->parseFortunes($response['choices'][0]['message']['content'], $count);
        }
        
        throw new Exception("Failed to generate fortunes from OpenAI");
    }
    
    private function buildBatchPrompt($count) {
        $themes = [
            "someone thinking about you",
            "unexpected money or UPI refund",
            "that text you're waiting for",
            "your ex seeing your story",
            "food delivery luck",
            "work from home vibes",
            "online shopping surprises",
            "wifi working perfectly",
            "finding the perfect parking spot",
            "meeting your crush randomly",
            "your meme going viral",
            "getting a job callback",
            "someone paying back money they owe",
            "finding cash in old jeans",
            "getting the window seat",
            "your food order being accurate",
            "no one asking 'when marriage?'",
            "your screen time going down",
            "zero mosquitoes tonight",
            "instant biryani delivery",
            "autorickshaw going by meter",
            "train arriving on time",
            "coffee hitting just right",
            "no traffic today"
        ];
        
        shuffle($themes);
        $selectedThemes = array_slice($themes, 0, $count);
        $dayOfWeek = date('l');
        
        return "Generate exactly {$count} different delulu fortunes for today ({$dayOfWeek}). 

Theme hints (one per fortune):
" . implode("\n", array_map(fn($i, $t) => ($i+1) . ". {$t}", array_keys($selectedThemes), $selectedThemes)) . "

Style examples:
- 'Someone will think about you… probably.'
- 'Money might come… from a UPI refund.'
- 'That text you're waiting for? It's coming. In 2-5 business days.'
- 'Your ex will see your story. They won't react, but they'll see it.'

Output format - exactly {$count} lines, one fortune per line, numbered 1-{$count}. Be creative and funny. Use ellipses (…) for dramatic effect.";
    }
    
    private function parseFortunes($content, $expectedCount) {
        $lines = explode("\n", trim($content));
        $fortunes = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Remove numbering like "1." or "1:"
            $line = preg_replace('/^[\d]+[\.\:\)]\s*/', '', $line);
            // Remove quotes if present
            $line = trim($line, '"\'');
            
            if (!empty($line) && strlen($line) > 10) {
                $fortunes[] = $line;
            }
        }
        
        // Ensure we have enough fortunes
        while (count($fortunes) < $expectedCount) {
            $fortunes[] = "The universe is buffering… try again later.";
        }
        
        return array_slice($fortunes, 0, $expectedCount);
    }
    
    private function makeRequest($data) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown API error';
            throw new Exception("OpenAI API Error ({$httpCode}): " . $errorMessage);
        }
        
        return json_decode($response, true);
    }
}
