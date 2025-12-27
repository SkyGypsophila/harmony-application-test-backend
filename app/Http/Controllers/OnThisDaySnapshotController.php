<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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

    public function index(Request $request)
    {
        $lang  = $request->query('lang', 'zh');
        $type  = $request->query('type', 'selected');
        $limit = (int) $request->query('limit', 3);

        // 校验 type
        $allowedTypes = config('onthisday.types');
        abort_unless(in_array($type, $allowedTypes, true), 422, 'Invalid type');

        // 校验 limit
        $limit = max(1, min($limit, 10));

        $now = Carbon::now();
        $mm  = (int) $now->format('m');
        $dd  = (int) $now->format('d');

        $query = OnThisDaySnapshot::query()
            ->where('lang', $this->lang)
            ->where('type', $this->type)
            ->where('month', $mm)
            ->where('day', $dd);

        // 年份 null 的放后面，年份大的靠前
        $events = $query
            ->orderByRaw('year IS NULL ASC')
            ->orderByDesc('year')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $events,
        ]);
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
                ->select(['id', 'lang', 'year', 'month', 'day', 'type', 'text', 'event_datetime'])
                ->first();
        });

        return response()->json([
            'data' => $data
        ]);
    }
}
