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

    public function today(Request $request)
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
                ->select(['id', 'lang', 'month', 'day', 'type', 'payload', 'dateTime'])
                ->first();
        });

        return response()->json([
            'date' => sprintf('%02d-%02d', $mm, $dd),
            'lang' => $lang,
            'type' => $type,
            ...$data
        ]);
    }

    private function trimPayload(array $payload): array
    {
        $max = config('onthisday.max_items');

        $trimList = function ($arr) use ($max) {
            $arr = is_array($arr) ? $arr : [];
            $arr = array_slice($arr, 0, $max);

            return array_map(function ($x) {
                return [
                    'year' => $x['year'] ?? null,
                    'text' => $x['text'] ?? '',
                    'pages' => array_map(function ($p) {
                        return [
                            'title'   => $p['titles']['display'] ?? null,
                            'pageUrl' => $p['content_urls']['desktop']['page'] ?? null,
                            'thumb'   => $p['thumbnail']['source'] ?? null,
                            'pageid'  => $p['pageid'] ?? null,
                        ];
                    }, $x['pages'] ?? []),
                ];
            }, $arr);
        };

        // 有的 type 返回体里只有单一字段；这里统一都输出，前端好写
        return [
            'selected'  => $trimList($payload['selected'] ?? []),
            'events'    => $trimList($payload['events'] ?? []),
            'births'    => $trimList($payload['births'] ?? []),
            'deaths'    => $trimList($payload['deaths'] ?? []),
            'holidays'  => $trimList($payload['holidays'] ?? []),
        ];
    }

}
