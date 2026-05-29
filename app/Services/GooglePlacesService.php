<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = config('services.google.places.api_key');
    }

    /**
     * Search places by text query.
     */
    public function searchPlaces(string $query, array $options = []): array
    {
        $params = array_merge([
            'query' => $query,
            'key' => $this->apiKey,
        ], $options);

        try {
            $response = Http::get("{$this->baseUrl}/place/textsearch/json", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Places API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'ERROR', 'results' => []];
        } catch (\Exception $e) {
            Log::error('Google Places API exception', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);

            return ['status' => 'ERROR', 'results' => []];
        }
    }

    /**
     * Get place details by place_id.
     */
    public function getPlaceDetails(string $placeId, array $fields = []): array
    {
        $defaultFields = [
            'place_id',
            'name',
            'formatted_address',
            'geometry',
            'address_components',
            'types',
        ];

        $fields = empty($fields) ? $defaultFields : $fields;

        $params = [
            'place_id' => $placeId,
            'fields' => implode(',', $fields),
            'key' => $this->apiKey,
        ];

        try {
            $response = Http::get("{$this->baseUrl}/place/details/json", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Places Details API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'ERROR', 'result' => null];
        } catch (\Exception $e) {
            Log::error('Google Places Details API exception', [
                'message' => $e->getMessage(),
                'place_id' => $placeId,
            ]);

            return ['status' => 'ERROR', 'result' => null];
        }
    }

    /**
     * Geocode an address to get coordinates.
     */
    public function geocode(string $address): array
    {
        $params = [
            'address' => $address,
            'key' => $this->apiKey,
        ];

        try {
            $response = Http::get("{$this->baseUrl}/geocode/json", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Geocoding API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'ERROR', 'results' => []];
        } catch (\Exception $e) {
            Log::error('Google Geocoding API exception', [
                'message' => $e->getMessage(),
                'address' => $address,
            ]);

            return ['status' => 'ERROR', 'results' => []];
        }
    }

    /**
     * Reverse geocode coordinates to get address.
     */
    public function reverseGeocode(float $lat, float $lng): array
    {
        $params = [
            'latlng' => "{$lat},{$lng}",
            'key' => $this->apiKey,
        ];

        try {
            $response = Http::get("{$this->baseUrl}/geocode/json", $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google Reverse Geocoding API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['status' => 'ERROR', 'results' => []];
        } catch (\Exception $e) {
            Log::error('Google Reverse Geocoding API exception', [
                'message' => $e->getMessage(),
                'coordinates' => "{$lat},{$lng}",
            ]);

            return ['status' => 'ERROR', 'results' => []];
        }
    }

    /**
     * Check if API key is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && $this->apiKey !== 'your_google_maps_api_key_here';
    }
}