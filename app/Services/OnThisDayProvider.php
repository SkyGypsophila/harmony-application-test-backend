<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class OnThisDayProvider
{
    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function fetch(string $lang, string $type, int $month, int $day, ?string $variant = null)
    {
        $mm = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $dd = str_pad((string) $day, 2, '0', STR_PAD_LEFT);

        $url = "https://api.wikimedia.org/feed/v1/wikipedia/{$lang}/onthisday/{$type}/{$mm}/{$dd}";
        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => config('onthisday.user_agent'),
            'Accept-Language' => 'zh-hans' // We assume that only fetch zh content
        ];

        if ($variant) {
            $headers['Accept-Language'] = $variant;
        }

        $response = Http::retry(2, 250)
            ->timeout(20)
            ->withToken(env('WIKIPEDIA_ACCESS_TOKEN'))
            ->withHeaders($headers)
            ->get($url);

        $response->throw();

        return $response->json();
    }
}
