<?php

namespace App\Http\Controllers;

use App\Support\{Activity, Img};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryController extends Controller
{
    private function project($id)
    {
        abort_unless(auth()->user()->hasPerm('content'), 403);
        $p = DB::table('projects')->where('id', $id)->first();
        abort_unless($p, 404);
        return $p;
    }
    private function item($mid)
    {
        abort_unless(auth()->user()->hasPerm('content'), 403);
        $m = DB::table('project_media')->where('id', $mid)->first();
        abort_unless($m, 404);
        return $m;
    }
    // YouTube and Vimeo links become privacy-friendly embed links
    public static function embed(string $url): ?string
    {
        $url = trim($url);
        if (
            preg_match('#^https://(?:www\.|m\.)?youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})#', $url, $m)
            || preg_match('#^https://youtu\.be/([A-Za-z0-9_-]{11})#', $url, $m)
            || preg_match('#^https://(?:www\.)?youtube\.com/(?:embed|shorts)/([A-Za-z0-9_-]{11})#', $url, $m)
        ) return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        if (preg_match('#^https://(?:www\.)?vimeo\.com/(?:video/)?(\d{6,12})#', $url, $m)) return 'https://player.vimeo.com/video/' . $m[1];
        return null;
    }
    public function add(Request $r, $id)
    {
        $p = $this->project($id);
        $r->validate(['images' => 'nullable|array|max:20', 'images.*' => 'image|max:8192', 'video' => 'nullable|url|max:300', 'library_pick' => 'nullable|string|max:200']);
        $count = DB::table('project_media')->where('project_id', $id)->count();
        $sort = (int) DB::table('project_media')->where('project_id', $id)->max('sort');
        $paths = [];
        foreach ((array) $r->file('images') as $f) $paths[] = Img::store($f, 1600);
        $pick = (string) $r->input('library_pick');
        if ($pick !== '' && preg_match('#^storage/uploads/[A-Za-z0-9]{8,64}\.webp$#', $pick) && is_file(public_path($pick))) $paths[] = $pick;
        $added = 0;
        foreach ($paths as $path) {
            if ($count + $added >= 30) break;
            DB::table('project_media')->insert(['project_id' => $id, 'type' => 'image', 'path' => $path, 'url' => null, 'caption' => null, 'sort' => ++$sort]);
            $added++;
        }
        if ($v = trim((string) $r->input('video'))) {
            $e = self::embed($v);
            if (!$e) return back()->withErrors(['video' => 'Only YouTube and Vimeo links are supported.'])->withInput();
            if ($count + $added < 30) {
                DB::table('project_media')->insert(['project_id' => $id, 'type' => 'video', 'path' => null, 'url' => $e, 'caption' => null, 'sort' => ++$sort]);
                $added++;
            }
        }
        if ($count + $added >= 30 && $added === 0) return back()->with('err', 'A project can have up to 30 gallery items.');
        Activity::note('updated', "Added $added gallery item(s) to project: {$p->title}", 'Projects', $id);
        return back()->with('ok', $added ? "$added item(s) added to the gallery." : 'Nothing was added.');
    }
    public function order(Request $r, $id)
    {
        $this->project($id);
        foreach (array_map('intval', (array) $r->input('ids', [])) as $i => $mid) {
            DB::table('project_media')->where('id', $mid)->where('project_id', $id)->update(['sort' => $i + 1]);
        }
        return response()->json(['ok' => true]);
    }
    public function caption(Request $r, $mid)
    {
        $this->item($mid);
        $r->validate(['caption' => 'nullable|max:150']);
        DB::table('project_media')->where('id', $mid)->update(['caption' => $r->input('caption') ?: null]);
        return back()->with('ok', 'Caption saved.');
    }
    public function destroy($mid)
    {
        $m = $this->item($mid);
        DB::table('project_media')->where('id', $mid)->delete();
        return back()->with('ok', 'Removed from the gallery. The image stays in the Media library.');
    }
}
