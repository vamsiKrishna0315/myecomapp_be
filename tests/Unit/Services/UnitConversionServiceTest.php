<?php

declare(strict_types=1);

use App\Services\UnitConversionService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('UnitConversionService', function () {
    it('converts kg to gram and vice versa', function () {
        $converter = app(UnitConversionService::class);

        expect($converter->convert(1, 'kg', 'gram'))->toBe(1000.0);
        expect($converter->convert(1000, 'gram', 'kg'))->toBe(1.0);
    });

    it('returns same quantity when units match', function () {
        $converter = app(UnitConversionService::class);

        expect($converter->convert(5, 'kg', 'kg'))->toBe(5.0);
        expect($converter->convert(500, 'gram', 'gram'))->toBe(500.0);
    });

    it('converts piece to kg and vice versa using grams per piece', function () {
        $converter = app(UnitConversionService::class);

        // 1 piece = 100 grams = 0.1 kg
        expect($converter->convert(10, 'piece', 'kg', 100))->toBe(1.0);
        expect($converter->convert(1, 'kg', 'piece', 100))->toBe(10.0);
    });

    it('converts piece to gram and vice versa using grams per piece', function () {
        $converter = app(UnitConversionService::class);

        // 1 piece = 100 grams
        expect($converter->convert(5, 'piece', 'gram', 100))->toBe(500.0);
        expect($converter->convert(500, 'gram', 'piece', 100))->toBe(5.0);
    });

    it('throws exception when piece conversion without grams per piece', function () {
        $converter = app(UnitConversionService::class);

        expect(fn () => $converter->convert(5, 'piece', 'kg'))->toThrow(InvalidArgumentException::class);
    });

    it('calculates price per kg from price per gram', function () {
        $converter = app(UnitConversionService::class);

        // Price per gram = 1, so price per kg = 1000
        expect($converter->calculatePrice(1, 'gram', 'kg'))->toBe(1000.0);
    });

    it('calculates price per gram from price per kg', function () {
        $converter = app(UnitConversionService::class);

        // Price per kg = 100, so price per gram = 0.1
        expect($converter->calculatePrice(100, 'kg', 'gram'))->toBe(0.1);
    });

    it('calculates price per piece from price per kg using grams per piece', function () {
        $converter = app(UnitConversionService::class);

        // Price per kg = 100, 1 piece = 50g = 0.05kg
        // Price per piece = 100 * 0.05 = 5
        expect($converter->calculatePrice(100, 'kg', 'piece', 50))->toBe(5.0);
    });

    it('calculates price per kg from price per piece using grams per piece', function () {
        $converter = app(UnitConversionService::class);

        // Price per piece = 10, 1 piece = 100g = 0.1kg
        // Price per kg = 10 / 0.1 = 100
        expect($converter->calculatePrice(10, 'piece', 'kg', 100))->toBe(100.0);
    });

    it('throws exception when converting piece without grams per piece', function () {
        $converter = app(UnitConversionService::class);

        expect(fn () => $converter->calculatePrice(100, 'kg', 'piece'))->toThrow(InvalidArgumentException::class);
    });

    it('returns same price when units match', function () {
        $converter = app(UnitConversionService::class);

        expect($converter->calculatePrice(50, 'kg', 'kg'))->toBe(50.0);
        expect($converter->calculatePrice(10, 'piece', 'piece', 100))->toBe(10.0);
    });
});
