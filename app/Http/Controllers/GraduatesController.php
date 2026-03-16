<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * GraduatesController
 *
 * Handles the Graduates page and related API endpoints.
 * Reads directly from the `graduates` DB table instead of an Excel file.
 *
 * Table columns: student_id, first_name, last_name, gender,
 *                program_name, program_major, college, date_graduated
 */
class GraduatesController extends Controller
{
    // -------------------------------------------------------------------------
    // PAGES
    // -------------------------------------------------------------------------

    /**
     * GET /graduates
     */
    public function index(Request $request): View
    {
        // College dropdown — distinct values, sorted
        $colleges = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates')
            ->select('college')
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->distinct()
            ->orderBy('college')
            ->pluck('college')
            ->all();

        $selectedCollege = $request->query('college', 'All');

        // Totals
        $query = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates');
        if ($selectedCollege !== 'All') {
            $query->where('college', $selectedCollege);
        }

        $totalGraduates = (clone $query)->count();
        $totalMale      = (clone $query)->whereRaw("LOWER(gender) IN ('male','m')")->count();
        $totalFemale    = (clone $query)->whereRaw("LOWER(gender) IN ('female','f')")->count();

        return view('student.graduates', [
            'active_page'     => 'graduates',
            'colleges'        => $colleges,
            'selected_college' => $selectedCollege,
            'total_graduates' => $totalGraduates,
            'total_male'      => $totalMale,
            'total_female'    => $totalFemale,
        ]);
    }

    // -------------------------------------------------------------------------
    // API ENDPOINTS
    // -------------------------------------------------------------------------

    /**
     * GET /api/graduates-summary
     * Returns male/female count for a selected college (or All).
     */
    public function summary(Request $request): JsonResponse
    {
        $selectedCollege = $request->query('college', 'All');

        $query = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates');
        if ($selectedCollege !== 'All') {
            $query->where('college', $selectedCollege);
        }

        $male   = (clone $query)->whereRaw("LOWER(gender) IN ('male','m')")->count();
        $female = (clone $query)->whereRaw("LOWER(gender) IN ('female','f')")->count();

        return response()->json(['male' => $male, 'female' => $female]);
    }

    /**
     * GET /api/graduates-by-college
     * Returns count and percentage per college.
     */
    public function byCollege(Request $request): JsonResponse
    {
        $rows = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates')
            ->select('college', DB::raw('COUNT(*) as total'))
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->groupBy('college')
            ->orderBy('college')   // mirrors pandas sort_index()
            ->get();

        $labels  = $rows->pluck('college')->all();
        $values  = $rows->pluck('total')->map(fn($v) => (int) $v)->all();
        $total   = array_sum($values);
        $percents = array_map(
            fn($v) => $total > 0 ? round(($v / $total) * 100, 1) : 0,
            $values
        );

        return response()->json([
            'labels'   => $labels,
            'values'   => $values,
            'percents' => $percents,
        ]);
    }

    /**
     * GET /api/graduates-gender-by-college
     * Returns male/female breakdown per college.
     */
    public function genderByCollege(Request $request): JsonResponse
    {
        // Pull male counts per college
        $maleRows = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates')
            ->select('college', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->whereRaw("LOWER(gender) IN ('male','m')")
            ->groupBy('college')
            ->orderBy('college')
            ->pluck('cnt', 'college');

        // Pull female counts per college
        $femaleRows = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates')
            ->select('college', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->whereRaw("LOWER(gender) IN ('female','f')")
            ->groupBy('college')
            ->orderBy('college')
            ->pluck('cnt', 'college');

        // Union of all colleges, sorted
        $labels = collect($maleRows->keys()->merge($femaleRows->keys()))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $maleVals   = array_map(fn($c) => (int)($maleRows[$c]   ?? 0), $labels);
        $femaleVals = array_map(fn($c) => (int)($femaleRows[$c] ?? 0), $labels);

        return response()->json([
            'labels' => $labels,
            'male'   => $maleVals,
            'female' => $femaleVals,
        ]);
    }

    /**
     * GET /api/graduates-by-program
     * Returns top-N programs by graduate count, with optional college filter.
     */
    public function byProgram(Request $request): JsonResponse
    {
        $college = $request->query('college', 'All');
        $topN    = max(1, (int) $request->query('top', 8));

        // Major alias map (mirrors Python MAJOR_ALIASES)
        $majorAliases = [
            'system development'    => 'systems development',
            'fashion merchandizing' => 'fashion merchandising',
            'doctor of philosophy in development education'
                => 'doctor of philosophy in development education (dot-uni)',
        ];

        $noMajorSentinels = ['', 'nan', 'none', 'n/a'];

        // ── Pull relevant rows from DB ────────────────────────────────────────
        $query = DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('graduates')
            ->select('program_name', 'program_major')
            ->whereNotNull('program_name')
            ->where('program_name', '!=', '');

        if ($college !== 'All') {
            $query->where('college', $college);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return response()->json(['items' => []]);
        }

        // ── Normalisation helpers ─────────────────────────────────────────────
        $cleanText = function ($x): string {
            if ($x === null) return '';
            return trim(preg_replace('/\s+/', ' ', (string) $x));
        };

        $normalizeKey = function (string $s) use ($cleanText): string {
            $s = strtolower($cleanText($s));
            $s = str_replace(['–', '—'], '-', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        $normalizeMajorKey = function (string $m) use ($normalizeKey, $majorAliases): string {
            $mk = $normalizeKey($m);
            return $majorAliases[$mk] ?? $mk;
        };

        // ── Build composite key counts ────────────────────────────────────────
        $keyCounts = [];
        $keyData   = [];

        foreach ($rows as $row) {
            $programName = $cleanText($row->program_name  ?? '');
            $cleanMajor  = $cleanText($row->program_major ?? '');

            $isMajorEmpty = in_array(strtolower($cleanMajor), $noMajorSentinels, true);
            $majorPart    = $isMajorEmpty ? 'NO_MAJOR' : $normalizeMajorKey($cleanMajor);

            $key = $normalizeKey($programName) . '||' . $majorPart;

            $keyCounts[$key] = ($keyCounts[$key] ?? 0) + 1;

            $keyData[$key]['program_names'][] = $programName;
            if (!$isMajorEmpty) {
                $keyData[$key]['majors'][] = $cleanMajor;
            }
        }

        // ── Sort desc, take top N ─────────────────────────────────────────────
        arsort($keyCounts);
        $topKeys = array_slice(array_keys($keyCounts), 0, $topN, true);

        $items = [];
        foreach ($topKeys as $key) {
            $programNames = $keyData[$key]['program_names'] ?? [];
            $majors       = $keyData[$key]['majors']        ?? [];

            $items[] = [
                'program_name' => $this->mode($programNames),
                'major'        => !empty($majors) ? $this->mode($majors) : null,
                'count'        => $keyCounts[$key],
            ];
        }

        return response()->json(['items' => $items]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS
    // -------------------------------------------------------------------------

    /**
     * Return the most-frequent value in an array (simple mode).
     */
    private function mode(array $values): ?string
    {
        if (empty($values)) return null;

        $counts = array_count_values($values);
        arsort($counts);

        return (string) array_key_first($counts);
    }
}