<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$product = \App\Models\Product::find(9);
$converter = app(\App\Services\UnitConversionService::class);

if ($product) {
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║         PRODUCT #9 - PIECE UNIT VERIFICATION               ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    echo "Base Data:\n";
    echo "  Price (source):           ₹" . $product->price . "\n";
    echo "  Base Price Unit:          " . ($product->base_price_unit ?? 'NULL') . "\n";
    echo "  Allowed Units:            " . json_encode($product->allowed_units ?? []) . "\n";
    echo "  Grams Per Piece:          " . ($product->grams_per_piece ?? 'NULL') . "\n";
    echo "  Grams Per Piece Type:     " . ($product->grams_per_piece_type ?? 'standard') . "\n";
    echo "\n────────────────────────────────────────────────────────────\n\n";

    // Check if piece is in allowed units
    $allowedUnits = $product->allowed_units ?? [];
    if (in_array('piece', $allowedUnits)) {
        echo "✓ Piece IS in allowed_units\n\n";

        if ($product->grams_per_piece) {
            try {
                $pricePerPiece = $converter->calculatePrice(
                    (float) $product->price,
                    $product->base_price_unit ?? 'kg',
                    'piece',
                    (float) $product->grams_per_piece
                );
                echo "Conversion Calculation:\n";
                echo "  Base Price:               ₹" . $product->price . "\n";
                echo "  From Unit:                " . ($product->base_price_unit ?? 'kg') . "\n";
                echo "  To Unit:                  piece\n";
                echo "  Grams Per Piece:          " . $product->grams_per_piece . "g\n";
                echo "  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                echo "  CORRECT Price Per Piece:  ₹" . number_format($pricePerPiece, 2) . "\n\n";

                echo "Verification:\n";
                if (abs($pricePerPiece - 27.50) < 0.01) {
                    echo "  ✅ 27.50 is CORRECT\n";
                } else {
                    echo "  ❌ 27.50 is WRONG\n";
                    echo "  Expected: ₹" . number_format($pricePerPiece, 2) . "\n";
                }
            } catch (Exception $e) {
                echo "❌ Conversion Error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Piece is allowed but grams_per_piece is NULL\n";
            echo "   Cannot convert without grams_per_piece value\n";
        }
    } else {
        echo "❌ Piece is NOT in allowed_units\n";
        echo "   Available units: " . json_encode($allowedUnits) . "\n";
    }
} else {
    echo "❌ Product #9 not found\n";
}

