<?php

namespace App\Services\OpenAi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAiClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.openai.key'));
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.openai.enabled', true) && $this->isConfigured();
    }

    /**
     * Call OpenAI Responses API. Optionally attaches file_search with the configured vector store.
     *
     * @param  array<int, array<string, mixed>>  $input
     * @return array{text:string, raw:array<string, mixed>, id:?string}
     */
    public function createResponse(array $input, ?string $instructions = null): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('OpenAI API key is not configured.');
        }

        $payload = [
            'model' => config('services.openai.model', 'gpt-4.1-mini'),
            'input' => $input,
        ];

        if ($instructions !== null && $instructions !== '') {
            $payload['instructions'] = $instructions;
        }

        $vectorStoreId = config('services.openai.vector_store_id');
        if (filled($vectorStoreId)) {
            $payload['tools'] = [[
                'type' => 'file_search',
                'vector_store_ids' => [$vectorStoreId],
            ]];
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('services.openai.base_url'), '/'))
                ->withToken((string) config('services.openai.key'))
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('services.openai.timeout', 60))
                ->post('/responses', $payload)
                ->throw();
        } catch (ConnectionException $e) {
            Log::warning('OpenAI connection failed', ['error' => $e->getMessage()]);
            throw new RuntimeException('Unable to reach the AI service. Please try again shortly.', 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response?->json();
            Log::warning('OpenAI request failed', [
                'status' => $status,
                'body' => $body,
            ]);
            throw new RuntimeException(
                'The AI service returned an error. Please try again or contact support.',
                0,
                $e
            );
        }

        /** @var array<string, mixed> $data */
        $data = $response->json() ?? [];
        $text = $this->extractOutputText($data);

        if ($text === '') {
            Log::warning('OpenAI response missing text', ['response_id' => $data['id'] ?? null]);
            throw new RuntimeException('The AI returned an empty response. Please try again.');
        }

        return [
            'text' => $text,
            'raw' => $data,
            'id' => isset($data['id']) ? (string) $data['id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function extractOutputText(array $data): string
    {
        if (!empty($data['output_text']) && is_string($data['output_text'])) {
            return trim($data['output_text']);
        }

        $chunks = [];
        $output = $data['output'] ?? null;
        if (!is_array($output)) {
            return '';
        }

        foreach ($output as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['type'] ?? null) !== 'message') {
                continue;
            }
            $content = $item['content'] ?? [];
            if (!is_array($content)) {
                continue;
            }
            foreach ($content as $part) {
                if (!is_array($part)) {
                    continue;
                }
                $type = $part['type'] ?? '';
                if (in_array($type, ['output_text', 'text'], true) && !empty($part['text']) && is_string($part['text'])) {
                    $chunks[] = $part['text'];
                }
            }
        }

        return trim(implode("\n", $chunks));
    }
}
