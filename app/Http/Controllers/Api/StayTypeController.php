<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StayTypeResource;
use App\Http\Resources\StayTypeCollection;
use App\Models\StayType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StayTypeController extends Controller
{
    /**
     * List all active stay types with optional hotel filter and rate plan pricing hints.
     *
     * GET /api/stay-types
     *
     * @param Request $request
     * @return StayTypeCollection
     */
    public function index(Request $request): StayTypeCollection
    {
        $query = StayType::with(['hotel', 'rateRules.ratePlan'])->active();

        if ($request->has('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $stayTypes = $query->get();

        return new StayTypeCollection($stayTypes);
    }

    /**
     * Get a single stay type with details including related hotel and age policy.
     *
     * GET /api/stay-types/{id}
     *
     * @param $id
     * @return StayTypeResource|JsonResponse
     */
    public function show($id): StayTypeResource|JsonResponse
    {
        try {
            $stayType = StayType::with(['hotel.agePolicies', 'rateRules.ratePlan'])
                ->where('id', $id)
                ->firstOrFail();

            return new StayTypeResource($stayType);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse($id);
        }
    }

    /**
     * Handle model not found exception.
     *
     * @param $id
     * @return JsonResponse
     */
    protected function notFoundResponse($id): JsonResponse
    {
        return response()->json([
            'message' => 'Stay type not found',
        ], 404);
    }

    /**
     * Create a new stay type.
     *
     * POST /api/stay-types
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => 'required|string|max:50|unique:stay_types,code',
            'nights' => 'required|integer|min:1',
            'included_board_type' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $stayType = StayType::create($validated);

        return response()->json([
            'message' => 'Stay type created successfully',
            'data' => new StayTypeResource($stayType->load(['hotel', 'rateRules.ratePlan'])),
        ], 201);
    }

    /**
     * Update an existing stay type.
     *
     * PUT /api/stay-types/{id}
     *
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $stayType = StayType::where('id', $id)->firstOrFail();

            $validated = $request->validate([
                'hotel_id' => 'sometimes|exists:hotels,id',
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'code' => 'sometimes|string|max:50|unique:stay_types,code,' . $stayType->id,
                'nights' => 'sometimes|integer|min:1',
                'included_board_type' => 'nullable|string|max:100',
                'is_active' => 'nullable|boolean',
            ]);

            $stayType->update($validated);

            return response()->json([
                'message' => 'Stay type updated successfully',
                'data' => new StayTypeResource($stayType->load(['hotel', 'rateRules.ratePlan'])),
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse($id);
        }
    }

    /**
     * Soft delete a stay type.
     *
     * DELETE /api/stay-types/{id}
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $stayType = StayType::where('id', $id)->firstOrFail();
            $stayType->delete();

            return response()->json([
                'message' => 'Stay type deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse($id);
        }
    }
}
