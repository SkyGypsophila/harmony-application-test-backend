<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnThisDaySnapshot;
use App\Services\OnThisDayProvider;
use Illuminate\Support\Facades\Cache;

class OnThisDaySnapshotController extends Controller
{
    protected OnThisDayProvider $provider;

    protected string $lang = 'zh';

    protected string $type = 'selected';

    public function __construct(OnThisDayProvider $provider)
    {
        $this->provider = $provider;
    }

    public function historicalToday(Request $request)
    {
        $lang = $request->query('lang', 'zh');
        $type = $request->query('type', 'selected');
        [$mm, $dd] = [(int) now()->format('m'), (int) now()->format('d')];

        return $this->byDateInternal($lang, $type, $mm, $dd);
    }

    protected function byDateInternal(string $lang, string $type, int $mm, int $dd)
    {
        $cacheKey = "onthisday:{$this->lang}:{$this->type}:{$mm}-{$dd}";
        $ttl = config('onthisday.cache_ttl_seconds');

        $data = Cache::remember($cacheKey, $ttl, function () use ($lang, $type, $mm, $dd) {
            return OnThisDaySnapshot::where('lang', $this->lang)
                ->where('type', $this->type)
                ->where('month', $mm)
                ->where('day', $dd)
                ->select(['id', 'lang', 'month', 'day', 'type', 'text', 'payload', 'event_datetime'])
                ->first();
        });

        return response()->json([
            'data' => $data
        ]);
    }
}
