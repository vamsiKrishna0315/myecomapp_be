<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCut;

final class UnitConversionService
{
    /**
     * Convert quantity from one unit to another.
     *
     * @param  float  $quantity  The quantity to convert
     * @param  string  $fromUnit  The unit to convert from (kg, gram, piece)
     * @param  string  $toUnit  The unit to convert to (kg, gram, piece)
     * @param  float|null  $gramsPerPiece  Grams per piece (required for piece conversions)
     * @return float The converted quantity
     */
    public function convert(float $quantity, string $fromUnit, string $toUnit, ?float $gramsPerPiece = null): float
    {
        if ($fromUnit === $toUnit) {
            return $quantity;
        }

        // Convert to grams as intermediate unit
        $grams = $this->toGrams($quantity, $fromUnit, $gramsPerPiece);

        // Convert from grams to target unit
        return $this->fromGrams($grams, $toUnit, $gramsPerPiece);
    }

    /**
     * Calculate unit price based on base price unit and requested unit.
     *
     * @param  float  $basePrice  Price in base unit
     * @param  string  $baseUnit  The base unit (kg, gram, piece)
     * @param  string  $requestedUnit  The unit customer requested
     * @param  float|null  $gramsPerPiece  Grams per piece (required for piece conversions)
     * @return float The price per requested unit
     */
    public function calculatePrice(float $basePrice, string $baseUnit, string $requestedUnit, ?float $gramsPerPiece = null): float
    {
        if ($baseUnit === $requestedUnit) {
            return $basePrice;
        }

        // If base is kg and requested is gram: price per gram = price per kg / 1000
        if ($baseUnit === 'kg' && $requestedUnit === 'gram') {
            return $basePrice / 1000;
        }

        // If base is gram and requested is kg: price per kg = price per gram * 1000
        if ($baseUnit === 'gram' && $requestedUnit === 'kg') {
            return $basePrice * 1000;
        }

        // If base is kg and requested is piece: use grams per piece
        if ($baseUnit === 'kg' && $requestedUnit === 'piece') {
            if ($gramsPerPiece === null) {
                throw new \InvalidArgumentException('grams_per_piece is required for piece conversion');
            }

            return ($basePrice / 1000) * $gramsPerPiece;
        }

        // If base is gram and requested is piece: use grams per piece
        if ($baseUnit === 'gram' && $requestedUnit === 'piece') {
            if ($gramsPerPiece === null) {
                throw new \InvalidArgumentException('grams_per_piece is required for piece conversion');
            }

            return $basePrice * $gramsPerPiece;
        }

        // If base is piece and requested is kg: convert via grams
        if ($baseUnit === 'piece' && $requestedUnit === 'kg') {
            if ($gramsPerPiece === null) {
                throw new \InvalidArgumentException('grams_per_piece is required for piece conversion');
            }

            return ($basePrice / $gramsPerPiece) * 1000;
        }

        // If base is piece and requested is gram: convert via grams
        if ($baseUnit === 'piece' && $requestedUnit === 'gram') {
            if ($gramsPerPiece === null) {
                throw new \InvalidArgumentException('grams_per_piece is required for piece conversion');
            }

            return $basePrice / $gramsPerPiece;
        }

        throw new \InvalidArgumentException("Unsupported unit conversion: {$baseUnit} to {$requestedUnit}");
    }

    /**
     * Convert any unit to grams.
     */
    private function toGrams(float $quantity, string $unit, ?float $gramsPerPiece = null): float
    {
        return match ($unit) {
            'kg' => $quantity * 1000,
            'gram' => $quantity,
            'piece' => $gramsPerPiece !== null ? $quantity * $gramsPerPiece : throw new \InvalidArgumentException('grams_per_piece required for piece unit'),
            default => throw new \InvalidArgumentException("Unsupported unit: {$unit}"),
        };
    }

    /**
     * Convert grams to any unit.
     */
    private function fromGrams(float $grams, string $unit, ?float $gramsPerPiece = null): float
    {
        return match ($unit) {
            'kg' => $grams / 1000,
            'gram' => $grams,
            'piece' => $gramsPerPiece !== null ? $grams / $gramsPerPiece : throw new \InvalidArgumentException('grams_per_piece required for piece unit'),
            default => throw new \InvalidArgumentException("Unsupported unit: {$unit}"),
        };
    }
}
