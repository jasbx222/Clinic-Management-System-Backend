<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VisitController extends Controller
{
    public function __construct(private VisitService $visitService) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Visit::class);

        $query = Visit::with(['patient.user', 'doctor']);

        if ($request->user()->role === 'doctor') {
            $query->where('doctor_id', $request->user()->id);
        } elseif ($request->user()->role === 'patient') {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        }

        return VisitResource::collection($query->paginate(15));
    }

    public function store(StoreVisitRequest $request)
    {
        Gate::authorize('create', Visit::class);

        $validated = $request->validated();

        $visit = $this->visitService->createVisit($validated, $request->user()->id);

        return new VisitResource($visit);
    }

    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        Gate::authorize('update', $visit);

        if ($visit->status === 'completed') {
            return response()->json(['message' => 'Cannot modify a completed visit.'], 400);
        }

        $validated = $request->validated();

        $visit->update($validated);

        return new VisitResource($visit);
    }

    public function show(Visit $visit)
    {
        Gate::authorize('view', $visit);

        return new VisitResource($visit->load(['patient.user', 'doctor', 'prescription', 'invoice']));
    }

    public function endVisit(Visit $visit)
    {
        Gate::authorize('update', $visit);

        $visit = $this->visitService->endVisit($visit);

        return new VisitResource($visit);
    }
}
