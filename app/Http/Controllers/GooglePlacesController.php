<?php

namespace App\Http\Controllers;

use App\Services\GooglePlacesService;
use Illuminate\Http\Request;

class GooglePlacesController
{
    protected GooglePlacesService $googlePlacesService;

    public function __construct(GooglePlacesService $googlePlacesService)
    {
        $this->googlePlacesService = $googlePlacesService;
    }

    /**
     * Test Google Places API connection.
     */
    public function test()
    {
        if (!$this->googlePlacesService->isConfigured()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Google Places API key is not configured',
            ], 500);
        }

        $result = $this->googlePlacesService->searchPlaces('Mumbai, India');

        return response()->json([
            'status' => 'success',
            'configured' => true,
            'api_response' => $result,
        ]);
    }

    /**
     * Search places via AJAX.
     */
    public function search(Request $request)
    {
        $query = $request->get('query');
        
        if (empty($query)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Query parameter is required',
            ], 400);
        }

        $result = $this->googlePlacesService->searchPlaces($query);

        return response()->json($result);
    }

    /**
     * Get place details by place_id.
     */
    public function details(Request $request)
    {
        $placeId = $request->get('place_id');
        
        if (empty($placeId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'place_id parameter is required',
            ], 400);
        }

        $result = $this->googlePlacesService->getPlaceDetails($placeId);

        return response()->json($result);
    }
}