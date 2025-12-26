<?php

namespace App\Services;

use App\Models\OnThisDaySnapshot;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class OnThisDayExecuter
{
    public function __construct(private readonly OnThisDayProvider $provider) {}

    private array $allowedLangs = [];
    private array $allowedTypes = [];
    private string $variant;
    private int $month;
    private int $day;

    private array $raw = [];
    private array $trimmed = [];

    /**
     * @throws ValidationException
     */
    public function validateOptions(array $langs, array $types, ?string $variant = null): static
    {
        $this->allowedLangs = config('onthisday.langs');
        $this->allowedTypes = config('onthisday.types');

        foreach ($langs as $l) {
            if(! in_array($l, $this->allowedLangs)) {
                throw ValidationException::withMessages(['lang' => "Invalid language: {$l}"]);
            }
        }

        foreach ($types as $t) {
            if(! in_array($t, $this->allowedTypes)) {
                throw ValidationException::withMessages(['type' => "Invalid type: {$t}"]);
            }
        }

        if ($variant) {
            $this->variant = $variant;
        }

        return $this;
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function fetch(): static
    {
        $this->prepareParams();
        $this->raw = [];

        foreach($this->allowedLangs as $lang) {
            foreach($this->allowedTypes as $type) {
                $payload = $this->provider->fetch($lang, $type, $this->month, $this->day);
                $this->raw[$lang][$type] = $payload[$type];
            }
        }

        return $this;
    }

    public function trimPayload(): static
    {
        $maximum = config('onthisday.max_items');

        foreach ($this->raw as $languageKey => $contents)
        {
            foreach ($contents as $eventKey => $eventData)
            {
                $eventData = is_array($eventData) ? $eventData : [];
                $eventData = array_slice($eventData, 0, $maximum);

                $this->trimmed[$languageKey][$eventKey] = array_map(function ($source) {
                    return [
                        'text' => $source['text'] ?? '',
                        'year' => $source['year'] ?? null,
                        'pages' => array_map(function ($page) {
                            return [
                                'title'   => $page['titles']['display'] ?? null,
                                'pageUrl' => $page['content_urls']['desktop']['page'] ?? null,
                                'thumb'   => $page['thumbnail']['source'] ?? null,
                                'pageid'  => $page['pageid'] ?? null,
                            ];
                        }, array_slice($source['pages'], 0, 2))
                    ];
                }, $eventData);
            }
        }

        return $this;
    }

    public function synchronize(): static
    {
        foreach ($this->trimmed as $language => $typesPayload)
        {
            foreach ($typesPayload as $type => $dataArray)
            {
                foreach ($dataArray as $data)
                {
                    $year = $data['year'] ?? null;
                    $text = $data['text'] ?? '';

                    $eventDateTime = null;
                    if ($year != null && $year >= 1 && $year <= 9999) {
                        $eventDateTime = Carbon::create($year, $this->month, $this->day, 0, 0, 0, config('app.timezone'));
                    }

                    OnThisDaySnapshot::create([
                        'lang' => $language,
                        'type' => $type,
                        'year' => $year,
                        'month' => $this->month,
                        'day' => $this->day,
                        'text' => $text,
                        'payload' => $data,
                        'event_datetime' => $eventDateTime,
                    ]);

                }
            }
        }

        return $this;
    }

    private function prepareParams(): void
    {
        $this->month = (int) now()->format('m');
        $this->day = (int) now()->format('d');
    }
}
