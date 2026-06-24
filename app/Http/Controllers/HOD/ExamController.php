<?php

namespace App\Http\Controllers\HOD;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::with(['subject', 'schoolClass', 'creator'])
            ->withCount(['questions', 'attempts'])
            ->withAvg('attempts', 'percentage')
            ->when($request->filled('search'), fn ($query) => $this->applyExamSearch($query, (string) $request->query('search')))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $routePrefix = 'hod';
        $search = $request->query('search');

        return view('teacher.exams.index', compact('exams', 'routePrefix', 'search'));
    }

    private function applyExamSearch($query, string $search): void
    {
        $search = strtolower(trim($search));

        $query->where(function ($query) use ($search) {
            $query->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                ->orWhereHas('subject', fn ($query) => $query->whereRaw('LOWER(name) like ?', ["%{$search}%"]))
                ->orWhereHas('schoolClass', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(level) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(stream) like ?', ["%{$search}%"]);
                })
                ->orWhereHas('creator', function ($query) use ($search) {
                    $fullNameExpression = config('database.default') === 'sqlite'
                        ? "LOWER(first_name || ' ' || last_name)"
                        : "LOWER(CONCAT(first_name, ' ', last_name))";

                    $query->whereRaw('LOWER(first_name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(last_name) like ?', ["%{$search}%"])
                        ->orWhereRaw($fullNameExpression . ' like ?', ["%{$search}%"]);
                });
        });
    }
}
