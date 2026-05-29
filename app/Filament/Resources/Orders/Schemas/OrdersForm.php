<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\CouponType;
use App\Enums\OrderItemStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\OrderStatuses;
use App\Models\Product;
use App\Models\ProductCut;
use App\Models\Store;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Utilities\Get;
use Filament\Forms\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Log;

final class OrdersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('order_number')
                                    ->disabled()
                                    ->dehydrated()
                                    ->label('Order Number')
                                    ->helperText('Auto-generated and unique. Cannot be entered manually.')
                                    ->columnSpan(1),

                                Select::make('customer_id')
                                    ->relationship('customer', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                                    ->searchable(['first_name', 'last_name', 'email', 'mobile'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->label('Customer')
                                    ->columnSpan(2),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('delivery_address_id')
                                    ->label('Delivery Address')
                                    ->options(function ($get) {
                                        $customerId = $get('customer_id');
                                        if (! $customerId) {
                                            return [];
                                        }

                                        return Address::where('customer_id', $customerId)
                                            ->where('status', 1)
                                            ->get()
                                            ->pluck('full_address', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->suffixAction(
                                        Action::make('addDeliveryAddress')
                                            ->icon('heroicon-m-plus')
                                            ->color('success')
                                            ->tooltip('Add New Delivery Address')
                                            ->form([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('address_line1')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('Address Line 1')
                                                            ->columnSpan(2),

                                                        TextInput::make('address_line2')
                                                            ->maxLength(255)
                                                            ->label('Address Line 2')
                                                            ->placeholder('Apartment, suite, etc. (optional)')
                                                            ->columnSpan(2),

                                                        TextInput::make('city')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('City')
                                                            ->columnSpan(1),

                                                        TextInput::make('state')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('State')
                                                            ->columnSpan(1),

                                                        TextInput::make('zip_code')
                                                            ->required()
                                                            ->maxLength(10)
                                                            ->label('ZIP Code')
                                                            ->columnSpan(1),

                                                        TextInput::make('country')
                                                            ->required()
                                                            ->default('India')
                                                            ->maxLength(255)
                                                            ->label('Country')
                                                            ->columnSpan(1),

                                                        Select::make('address_type')
                                                            ->options([
                                                                1 => 'Home',
                                                                2 => 'Work',
                                                                3 => 'Other',
                                                            ])
                                                            ->default(1)
                                                            ->required()
                                                            ->label('Address Type')
                                                            ->columnSpan(1),

                                                        Toggle::make('is_default')
                                                            ->label('Set as Default Address')
                                                            ->default(false)
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->action(function (array $data, $set, $get) {
                                                $customerId = $get('customer_id');
                                                if (! $customerId) {
                                                    return;
                                                }

                                                $address = Address::create([
                                                    'customer_id' => $customerId,
                                                    'address_line1' => $data['address_line1'],
                                                    'address_line2' => $data['address_line2'],
                                                    'city' => $data['city'],
                                                    'state' => $data['state'],
                                                    'zip_code' => $data['zip_code'],
                                                    'country' => $data['country'],
                                                    'address_type' => $data['address_type'],
                                                    'is_default' => $data['is_default'],
                                                    'status' => 1,
                                                ]);

                                                // Set the newly created address as selected
                                                $set('delivery_address_id', $address->id);

                                                // Refresh the options by triggering a state update
                                                $set('customer_id', $customerId);
                                            })
                                            ->modalHeading('Add New Delivery Address')
                                            ->modalSubmitActionLabel('Add Address')
                                            ->visible(fn ($get): bool => ! empty($get('customer_id')))
                                    )
                                    ->columnSpan(1)
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        // If billing is set to same as delivery, sync billing id
                                        if ($get('billing_same_as_delivery')) {
                                            $set('billing_address_id', $state);
                                        }
                                    }),

                                Toggle::make('billing_same_as_delivery')
                                    ->label('Billing same as Delivery')
                                    ->default(false)
                                    ->reactive()
                                    ->dehydrated(false)
                                    ->columnSpan(1)
                                    ->helperText('When checked, billing address will use the selected delivery address')
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        if ($state) {
                                            // If enabling, copy current delivery address to billing
                                            $set('billing_address_id', $get('delivery_address_id'));
                                        } else {
                                            // If disabling, clear billing so user can select another
                                            $set('billing_address_id', null);
                                        }
                                    }),

                                Select::make('billing_address_id')
                                    ->label('Billing Address')
                                    ->options(function ($get) {
                                        $customerId = $get('customer_id');
                                        if (! $customerId) {
                                            return [];
                                        }

                                        return Address::where('customer_id', $customerId)
                                            ->where('status', 1)
                                            ->get()
                                            ->pluck('full_address', 'id');
                                    })
                                    ->searchable()
                                    ->live()
                                    ->visible(fn ($get) => ! $get('billing_same_as_delivery'))
                                    ->suffixAction(
                                        Action::make('addBillingAddress')
                                            ->icon('heroicon-m-plus')
                                            ->color('success')
                                            ->tooltip('Add New Billing Address')
                                            ->form([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('address_line1')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('Address Line 1')
                                                            ->columnSpan(2),

                                                        TextInput::make('address_line2')
                                                            ->maxLength(255)
                                                            ->label('Address Line 2')
                                                            ->placeholder('Apartment, suite, etc. (optional)')
                                                            ->columnSpan(2),

                                                        TextInput::make('city')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('City')
                                                            ->columnSpan(1),

                                                        TextInput::make('state')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->label('State')
                                                            ->columnSpan(1),

                                                        TextInput::make('zip_code')
                                                            ->required()
                                                            ->maxLength(10)
                                                            ->label('ZIP Code')
                                                            ->columnSpan(1),

                                                        TextInput::make('country')
                                                            ->required()
                                                            ->default('India')
                                                            ->maxLength(255)
                                                            ->label('Country')
                                                            ->columnSpan(1),

                                                        Select::make('address_type')
                                                            ->options([
                                                                1 => 'Home',
                                                                2 => 'Work',
                                                                3 => 'Other',
                                                            ])
                                                            ->default(1)
                                                            ->required()
                                                            ->label('Address Type')
                                                            ->columnSpan(1),

                                                        Toggle::make('is_default')
                                                            ->label('Set as Default Address')
                                                            ->default(false)
                                                            ->columnSpan(1),
                                                    ]),
                                            ])
                                            ->action(function (array $data, $set, $get) {
                                                $customerId = $get('customer_id');
                                                if (! $customerId) {
                                                    return;
                                                }

                                                $address = Address::create([
                                                    'customer_id' => $customerId,
                                                    'address_line1' => $data['address_line1'],
                                                    'address_line2' => $data['address_line2'],
                                                    'city' => $data['city'],
                                                    'state' => $data['state'],
                                                    'zip_code' => $data['zip_code'],
                                                    'country' => $data['country'],
                                                    'address_type' => $data['address_type'],
                                                    'is_default' => $data['is_default'],
                                                    'status' => 1,
                                                ]);

                                                // Set the newly created address as selected
                                                $set('billing_address_id', $address->id);

                                                // Refresh the options by triggering a state update
                                                $set('customer_id', $customerId);
                                            })
                                            ->modalHeading('Add New Billing Address')
                                            ->modalSubmitActionLabel('Add Address')
                                            ->visible(fn ($get): bool => ! empty($get('customer_id')))
                                    )
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Store Vendor Assignment')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('store_id')
                                    ->label('Store')
                                    ->options(function () {
                                        return Store::where('status', 1)
                                            ->get()
                                            ->pluck('name', 'id');
                                    })
                                    ->default(function () {
                                        // Auto-select first store
                                        return Store::where('status', 1)->first()?->id;
                                    })
                                    ->searchable()
                                    ->live()
                                    ->dehydrated(false)
                                    ->helperText('Store is auto-selected')
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        // Reset store vendor when store changes
                                        $set('store_vendor_id', null);
                                        $set('is_eligible', null);
                                    })
                                    ->columnSpan(1),

                                Select::make('store_vendor_id')
                                    ->label('Store Vendor')
                                    ->options(function ($get) {
                                        $storeId = $get('store_id');
                                        if (! $storeId) {
                                            return [];
                                        }

                                        return \App\Models\User::where('user_role', 'store_vendor')
                                            ->where('store_id', $storeId)
                                            ->where('status', 1)
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                return [$user->id => "{$user->name} ({$user->email})"];
                                            });
                                    })
                                    ->searchable()
                                    ->placeholder('Click "Find Nearest Vendor" to auto-assign')
                                    ->helperText('Shows vendor name, saves vendor ID')
                                    ->live()
                                    ->dehydrated(false)
                                    ->suffixAction(
                                        Action::make('findNearestVendor')
                                            ->label('Find Nearest Vendor')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->color('success')
                                            ->action(function ($set, $get) {
                                                $deliveryAddressId = $get('delivery_address_id');
                                                $storeId = $get('store_id');

                                                if (! $deliveryAddressId) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->warning()
                                                        ->title('Delivery Address Required')
                                                        ->body('Please select a delivery address first.')
                                                        ->send();

                                                    return;
                                                }

                                                if (! $storeId) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->warning()
                                                        ->title('Store Required')
                                                        ->body('Please select a store first.')
                                                        ->send();

                                                    return;
                                                }

                                                // Call helper to find nearest vendor
                                                $nearestVendor = static::findNearestVendor($deliveryAddressId, $storeId);

                                                if ($nearestVendor) {
                                                    $set('store_vendor_id', $nearestVendor['user_id']);
                                                    $set('is_eligible', $nearestVendor['is_eligible']);

                                                    // Persist pending assignment to session immediately so afterCreate can use it
                                                    session([
                                                        'pending_store_vendor_assignment' => [
                                                            'store_id' => $storeId,
                                                            'store_vendor_id' => $nearestVendor['user_id'],
                                                            'is_eligible' => $nearestVendor['is_eligible'],
                                                        ],
                                                    ]);

                                                    Log::info('Pending store vendor assignment saved to session (find action)', [
                                                        'store_id' => $storeId,
                                                        'vendor_id' => $nearestVendor['user_id'],
                                                        'is_eligible' => $nearestVendor['is_eligible'],
                                                    ]);

                                                    \Filament\Notifications\Notification::make()
                                                        ->success()
                                                        ->title('Vendor Found!')
                                                        ->body("Nearest vendor assigned: {$nearestVendor['vendor_name']}")
                                                        ->send();
                                                } else {
                                                    \Filament\Notifications\Notification::make()
                                                        ->danger()
                                                        ->title('No Vendor Found')
                                                        ->body('No eligible store vendor found within delivery radius.')
                                                        ->send();
                                                }
                                            })
                                    )
                                    ->columnSpan(1),

                                Placeholder::make('is_eligible_display')
                                    ->label('Eligibility Status')
                                    ->content(function ($get): string {
                                        $isEligible = $get('is_eligible');
                                        $vendorId = $get('store_vendor_id');

                                        if (! $vendorId) {
                                            return '⏳ Click "Find Nearest Vendor" to check eligibility';
                                        }

                                        if ($isEligible === null) {
                                            return '⏳ Checking eligibility...';
                                        }
                                        if ($isEligible) {
                                            return '✅ Eligible - Within 10 minute delivery radius';
                                        }

                                        return '❌ Not Eligible - Outside 10 minute delivery radius';
                                    })
                                    ->extraAttributes(function ($get) {
                                        $isEligible = $get('is_eligible');
                                        if ($isEligible === true) {
                                            return ['class' => 'text-green-600 font-bold'];
                                        }
                                        if ($isEligible === false) {
                                            return ['class' => 'text-red-600 font-bold'];
                                        }

                                        return ['class' => 'text-gray-500'];
                                    })
                                    ->columnSpan(1),
                            ]),

                        // Hidden field to store is_eligible value
                        Toggle::make('is_eligible')
                            ->hidden()
                            ->default(null),
                    ])
                    ->description('Store is auto-selected. Click "Find Nearest Vendor" to auto-assign the nearest available store vendor based on delivery location.')
                    ->columnSpanFull(),

                Section::make('Order Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Section::make('')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Select::make('product_id')
                                                    ->relationship('product', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        // Always ensure all required fields have values
                                                        if ($state) {
                                                            $product = Product::find($state);
                                                            if ($product) {
                                                                // Set all product-related fields from database
                                                                $set('product_name', $product->name ?? 'Product');
                                                                $set('sku', $product->sku ?? '');
                                                                $set('category_id', $product->category_id ?? 0);

                                                                // Set product fields that moved from ProductCut
                                                                $set('price_per_kg', $product->price_per_kg ?? 0);
                                                                $set('price_per_piece', $product->price_per_piece ?? 0);
                                                                $set('weight_unit', $product->weight_unit ?? 'kg');
                                                                $set('preparation_style', $product->preparation_style ?? '');
                                                                $set('is_cleaned', $product->is_cleaned ?? false);
                                                                $set('is_skinless', $product->is_skinless ?? false);

                                                                Log::info('Product selected', [
                                                                    'product_id' => $state,
                                                                    'product_name' => $product->name,
                                                                    'sku' => $product->sku,
                                                                    'price_per_kg' => $product->price_per_kg,
                                                                    'category_id' => $product->category_id,
                                                                ]);
                                                            }
                                                        } else {
                                                            // Set safe defaults when no product selected
                                                            $set('product_name', 'Product');
                                                            $set('sku', '');
                                                            $set('category_id', 0);
                                                            $set('price_per_kg', 0);
                                                            $set('price_per_piece', 0);
                                                            $set('weight_unit', 'kg');
                                                            $set('preparation_style', '');
                                                            $set('is_cleaned', false);
                                                            $set('is_skinless', false);
                                                        }

                                                        // Reset cut selection when product changes and ensure defaults
                                                        $set('cut_id', null);
                                                        $set('cut_name', 'Cut');
                                                        $set('special_instructions', '');
                                                        $set('order_item_status', 0);
                                                        $set('status', 1);

                                                        static::calculateItemTotal($set, $get);
                                                    })
                                                    ->label('Product')
                                                    ->columnSpan(1),

                                                Select::make('cut_id')
                                                    ->label('Cut Type')
                                                    ->options(function ($get) {
                                                        $productId = $get('product_id');
                                                        if (! $productId) {
                                                            return [];
                                                        }

                                                        // Get cut types from cuttype_product pivot table
                                                        $product = Product::find($productId);
                                                        if (! $product) {
                                                            return [];
                                                        }

                                                        // Load cut types via pivot relationship
                                                        return $product->cuttypes()
                                                            ->where('cut_types.status', 1)
                                                            ->pluck('cut_types.name', 'cut_types.id');
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        if ($state) {
                                                            // Get the cut type name
                                                            $cutType = \App\Models\CutType::find($state);

                                                            if ($cutType) {
                                                                $set('cut_name', $cutType->name);

                                                                Log::info('Cut type selected', [
                                                                    'cut_id' => $state,
                                                                    'cut_name' => $cutType->name,
                                                                ]);
                                                            }
                                                        } else {
                                                            $set('cut_name', 'Cut');
                                                        }

                                                        // Ensure other required fields have defaults
                                                        if (! $get('special_instructions')) {
                                                            $set('special_instructions', '');
                                                        }
                                                        if (! $get('order_item_status')) {
                                                            $set('order_item_status', 0);
                                                        }
                                                        if (! $get('status')) {
                                                            $set('status', 1);
                                                        }

                                                        static::calculateItemTotal($set, $get);
                                                    })
                                                    ->placeholder('Select product first')
                                                    ->columnSpan(1),

                                                TextInput::make('ordered_weight')
                                                    ->numeric()
                                                    ->step(0.1)
                                                    ->minValue(0)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        static::calculateItemTotal($set, $get);
                                                    })
                                                    ->label('Ordered Weight')
                                                    ->suffix('kg')
                                                    ->columnSpan(1),

                                                TextInput::make('actual_weight')
                                                    ->numeric()
                                                    ->step(0.1)
                                                    ->minValue(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($set, $get, $state) {
                                                        static::calculateItemTotal($set, $get);
                                                    })
                                                    ->label('Actual Weight')
                                                    ->suffix('kg')
                                                    ->placeholder('Auto-filled from ordered')
                                                    ->columnSpan(1),
                                            ]),

                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('price_per_kg')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->prefix('₹')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Price per Kg')
                                                    ->columnSpan(1),

                                                TextInput::make('line_subtotal')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Line Subtotal')
                                                    ->extraAttributes(['class' => 'font-bold'])
                                                    ->columnSpan(1),

                                                TextInput::make('line_tax')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Line Tax')
                                                    ->helperText('Auto-calculated from order tax %')
                                                    ->columnSpan(1),

                                                TextInput::make('line_total')
                                                    ->numeric()
                                                    ->prefix('₹')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->label('Line Total')
                                                    ->extraAttributes(['class' => 'font-bold text-green-600'])
                                                    ->columnSpan(1),

                                                Toggle::make('is_cleaned')
                                                    ->label('Cleaned')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->columnSpan(1),

                                                Toggle::make('is_skinless')
                                                    ->label('Skinless')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->columnSpan(1),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Select::make('order_item_status')
                                                    ->options(function () {
                                                        $options = [];
                                                        foreach (OrderItemStatus::cases() as $status) {
                                                            $options[$status->toInt()] = $status->getLabel();
                                                        }

                                                        return $options;
                                                    })
                                                    ->default(OrderItemStatus::Pending->toInt())
                                                    ->required()
                                                    ->label('Item Status')
                                                    ->columnSpan(1),

                                                Textarea::make('special_instructions')
                                                    ->maxLength(500)
                                                    ->label('Special Instructions')
                                                    ->placeholder('Any special preparation instructions...')
                                                    ->rows(2)
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->compact(),

                                // Hidden fields for data storage
                                TextInput::make('product_name')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default('Product')
                                    ->beforeStateDehydrated(function ($state, $get) {
                                        $value = $state ?: 'Product';
                                        Log::info('Dehydrating product_name', ['value' => $value]);

                                        return $value;
                                    }),
                                TextInput::make('sku')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default('')
                                    ->beforeStateDehydrated(function ($state, $get) {
                                        $value = $state ?: '';
                                        Log::info('Dehydrating sku', ['value' => $value]);

                                        return $value;
                                    }),
                                TextInput::make('cut_name')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default('Cut')
                                    ->beforeStateDehydrated(function ($state, $get) {
                                        $value = $state ?: 'Cut';
                                        Log::info('Dehydrating cut_name', ['value' => $value]);

                                        return $value;
                                    }),

                                // Additional hidden fields for data storage
                                TextInput::make('category_id')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('price_per_piece')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('weight_unit')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default('kg'),
                                TextInput::make('preparation_style')
                                    ->hidden()
                                    ->dehydrated()
                                    ->default(''),
                                TextInput::make('line_discount')
                                    ->hidden()
                                    ->default(0)
                                    ->dehydrated(),
                                // NOTE: `line_total` is defined above as a visible, disabled, dehydrated
                                // field (so it will be included in the repeater payload). Removed the
                                // duplicate hidden definition to avoid dehydration conflicts.
                                Select::make('status')
                                    ->options([1 => 'Active', 0 => 'Inactive'])
                                    ->default(1)
                                    ->hidden()
                                    ->dehydrated(),
                            ])
                            ->addActionLabel('Add Product Item')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['product_name'] ?? 'New Item').
                                (isset($state['cut_name']) ? " - {$state['cut_name']}" : '').
                                (isset($state['ordered_weight']) ? " ({$state['ordered_weight']}kg)" : '').
                                (isset($state['line_subtotal']) && $state['line_subtotal'] > 0 ? " - ₹{$state['line_subtotal']}" : '')
                            )
                            ->columns(1)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($set, $get, $state) {
                                // Calculate order totals from items
                                $items = $state ?? [];
                                $orderSubtotal = 0;

                                foreach ($items as $item) {
                                    $lineSubtotal = (float) ($item['line_subtotal'] ?? 0);
                                    $orderSubtotal += $lineSubtotal;
                                }

                                $set('subtotal', number_format($orderSubtotal, 2, '.', ''));

                                // Recalculate tax amount based on current tax percentage
                                $taxPercentage = (float) ($get('tax_percentage') ?? 18);
                                $taxAmount = ($orderSubtotal * $taxPercentage) / 100;
                                $set('tax_amount', number_format($taxAmount, 2, '.', ''));

                                // Recalculate total
                                $deliveryCharge = (float) ($get('delivery_charge') ?? 0);
                                $discountAmount = (float) ($get('discount_amount') ?? 0);
                                $total = $orderSubtotal + $taxAmount + $deliveryCharge - $discountAmount;
                                $total = max(0, $total);
                                $set('total_amount', number_format($total, 2, '.', ''));
                            })
                            ->defaultItems(0)
                            ->minItems(0),
                    ])
                    ->description('Add products with their cuts, weights, and pricing details')
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Delivery Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('delivery_date')
                                    ->required()
                                    ->label('Delivery Date')
                                    ->default(now()->addDay()->toDateString())
                                    ->minDate(now()->toDateString())
                                    ->columnSpan(1),

                                TimePicker::make('delivery_time_slot')
                                    ->required()
                                    ->label('Delivery Time Slot')
                                    ->placeholder('e.g., 09:00-12:00 or custom')
                                    ->helperText('Enter delivery time slot manually, e.g., 09:00-12:00')
                                    ->columnSpan(1),

                                Select::make('driver_id')
                                    ->relationship('driver', 'name')
                                    ->searchable(['name', 'mobile'])
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        if ($state) {
                                            // Find the OrderStatuses record with code 'assigned_to_driver'
                                            $status = OrderStatuses::where('code', 'assigned_to_driver')->first();
                                            if ($status) {
                                                $set('current_status_id', $status->id);
                                                $set('current_status_code', $status->code);
                                            } else {
                                                $set('current_status_code', 'assigned_to_driver');
                                            }
                                        }
                                    })
                                    ->label('Assigned Driver')
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('special_instructions')
                            ->maxLength(500)
                            ->label('Special Instructions')
                            ->placeholder('Any special delivery instructions...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Pricing Information')
                    ->schema([
                        Section::make('Order Totals')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('subtotal')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->disabled()
                                            ->dehydrated()
                                            ->live()
                                            ->label('Subtotal')
                                            ->helperText('Automatically calculated from items')
                                            ->columnSpan(1),

                                        TextInput::make('tax_percentage')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(99)
                                            ->default(18)
                                            ->label('Tax %')
                                            ->helperText('Enter tax percentage (0-99). Only numbers allowed.')
                                            ->live()
                                            ->afterStateHydrated(function ($set, $get, $state) {
                                                // Ensure a sensible default is shown when editing existing records
                                                if ($state === null) {
                                                    $set('tax_percentage', 18);
                                                } else {
                                                    // Coerce stored values (e.g. "18.00") to an integer
                                                    if (is_numeric($state)) {
                                                        $set('tax_percentage', (int) round((float) $state));
                                                    }
                                                }
                                            })
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                // Only allow numbers up to 99
                                                $taxPercentage = (int) ($state ?? 0);
                                                if ($taxPercentage < 0) {
                                                    $taxPercentage = 0;
                                                }
                                                if ($taxPercentage > 99) {
                                                    $taxPercentage = 99;
                                                }
                                                $set('tax_percentage', $taxPercentage);
                                                // Calculate order-level tax amount
                                                $subtotal = (float) ($get('subtotal') ?? 0);
                                                if ($subtotal > 0) {
                                                    $taxAmount = ($subtotal * $taxPercentage) / 100;
                                                    $set('tax_amount', number_format($taxAmount, 2, '.', ''));
                                                }
                                                // Recalculate all line items with new tax percentage
                                                $items = $get('items') ?? [];
                                                foreach ($items as $index => $item) {
                                                    $lineSubtotal = (float) ($item['line_subtotal'] ?? 0);
                                                    if ($lineSubtotal > 0) {
                                                        $lineTax = ($lineSubtotal * $taxPercentage) / 100;
                                                        $lineDiscount = (float) ($item['line_discount'] ?? 0);
                                                        $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;
                                                        $set("items.{$index}.line_tax", number_format($lineTax, 2, '.', ''));
                                                        $set("items.{$index}.line_total", number_format($lineTotal, 2, '.', ''));
                                                    }
                                                }
                                                static::calculateTotal($set, $get);
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('tax_amount')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->default(0)
                                            ->label('Tax Amount')
                                            ->disabled()
                                            ->dehydrated()
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                static::calculateTotal($set, $get);
                                            })
                                            ->columnSpan(1),

                                        TextInput::make('delivery_charge')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->default(0)
                                            ->label('Delivery Charge')
                                            ->dehydrated()
                                            ->live()
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                static::calculateTotal($set, $get);
                                            })
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->compact(),

                        Section::make('Coupon & Discount')
                            ->schema([
                                Placeholder::make('available_coupons')
                                    ->label('Available Coupons')
                                    ->content(function ($get): string {
                                        $subtotal = (float) ($get('subtotal') ?? 0);
                                        if ($subtotal <= 0) {
                                            return '💡 Add items to see available coupons';
                                        }

                                        $coupons = static::getAvailableCoupons($get);
                                        if (empty($coupons)) {
                                            return '❌ No coupons available for this order amount';
                                        }

                                        $couponList = [];
                                        foreach ($coupons as $code => $description) {
                                            $couponList[] = "🎟️ <strong>{$code}</strong> - {$description}";
                                        }

                                        return implode('<br>', $couponList);
                                    })
                                    ->extraAttributes(['class' => 'text-sm'])
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('coupon_code')
                                            ->maxLength(50)
                                            ->label('Coupon Code')
                                            ->placeholder('e.g., SAVE20')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($set, $get, $state) {
                                                static::validateAndApplyCoupon($set, $get, $state);
                                            })
                                            ->helperText('Enter coupon code and it will be validated automatically')
                                            ->columnSpan(1),

                                        TextInput::make('discount_amount')
                                            ->numeric()
                                            ->prefix('₹')
                                            ->default(0)
                                            ->label('Discount Amount')
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Automatically calculated from coupon')
                                            ->columnSpan(1),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('discount_type')
                                            ->options([
                                                CouponType::Percentage->value => 'Percentage',
                                                CouponType::FixedAmount->value => 'Fixed Amount',
                                            ])
                                            ->default(CouponType::FixedAmount->value)
                                            ->label('Discount Type')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),

                                        TextInput::make('coupon_status')
                                            ->label('Coupon Status')
                                            ->disabled()
                                            ->placeholder('Enter coupon code to validate')
                                            ->helperText('Shows coupon validation status')
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->compact(),

                        Section::make('Final Total')
                            ->schema([
                                TextInput::make('total_amount')
                                    ->numeric()
                                    ->prefix('₹')
                                    ->required()
                                    ->label('Total Amount')
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600'])
                                    ->helperText('Final amount including all charges and discounts')
                                    ->columnSpanFull(),
                            ])
                            ->compact(),
                    ])
                    ->description('Order pricing, taxes, delivery charges, and coupon discounts')
                    ->columnSpanFull(),

                Section::make('Payment & Status')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('billing_type_id')
                                    ->relationship('billingType', 'name')
                                    ->preload()
                                    ->required()
                                    ->label('Payment Method')
                                    ->columnSpan(1),

                                Select::make('payment_status')
                                    ->options([
                                        PaymentStatus::Pending->value => 'Pending',
                                        PaymentStatus::Paid->value => 'Paid',
                                        PaymentStatus::Failed->value => 'Failed',
                                        PaymentStatus::Refunded->value => 'Refunded',
                                        PaymentStatus::PartialRefund->value => 'Partial Refund',
                                    ])
                                    ->default(PaymentStatus::Pending->value)
                                    ->required()
                                    ->label('Payment Status')
                                    ->columnSpan(1),

                                Select::make('current_status_id')
                                    ->relationship('currentStatus', 'name', fn ($query) => $query->orderBy('id', 'asc'))
                                    ->preload()
                                    ->required()
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        if ($state) {
                                            $status = OrderStatuses::find($state);
                                            if ($status) {
                                                Log::info('Setting current status code 866', ['code' => $status->code]);
                                                $set('current_status_code', $status->code);
                                            }
                                        } else {
                                            // Fallback to default status
                                            $defaultStatus = OrderStatuses::find(1);
                                            Log::info('Setting default status code 872', ['code' => $defaultStatus ? $defaultStatus->code : 'pending']);
                                            $set('current_status_code', $defaultStatus ? $defaultStatus->code : 'pending');
                                        }
                                    })
                                    ->afterStateHydrated(function ($set, $get, $state) {
                                        // Ensure status code is set when form loads
                                        if ($state) {
                                            $status = OrderStatuses::find($state);
                                            if ($status) {
                                                $set('current_status_code', $status->code);
                                            }
                                        } else {
                                            // Set default status code for new records
                                            $defaultStatus = OrderStatuses::find(1);
                                            $set('current_status_code', $defaultStatus ? $defaultStatus->code : 'PENDING');
                                        }
                                    })
                                    ->label('Order Status')
                                    ->columnSpan(1),
                            ]),

                        TextInput::make('current_status_code')
                            ->maxLength(20)
                            ->label('Status Code')
                            ->placeholder('Auto-generated from order status')
                            ->disabled()
                            ->dehydrated()
                            ->default('PENDING')
                            ->required()
                            ->helperText('Automatically set based on selected order status')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->label('Order Status'),
                    ])
                    ->columnSpanFull(),

                Section::make('Order Tracking')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_cancelled')
                                    ->label('Is Cancelled')
                                    ->live()
                                    ->columnSpan(1),

                                DateTimePicker::make('cancelled_at')
                                    ->label('Cancelled At')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->format('Y-m-d H:i:s')
                                    ->timezone('Asia/Kolkata')
                                    ->closeOnDateSelection(false)
                                    ->visible(fn ($get): bool => $get('is_cancelled'))
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('confirmed_at')
                                    ->label('Confirmed At')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->format('Y-m-d H:i:s')
                                    ->timezone('Asia/Kolkata')
                                    ->closeOnDateSelection(false)
                                    ->columnSpan(1),

                                DateTimePicker::make('delivered_at')
                                    ->label('Delivered At')
                                    ->native(false)
                                    ->seconds(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->format('Y-m-d H:i:s')
                                    ->timezone('Asia/Kolkata')
                                    ->closeOnDateSelection(false)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    /**
     * Calculate total amount based on subtotal, tax, delivery charge, and discount.
     */
    private static function calculateTotal($set, $get): void
    {
        $subtotal = (float) ($get('subtotal') ?? 0);
        $taxAmount = (float) ($get('tax_amount') ?? 0);
        $deliveryCharge = (float) ($get('delivery_charge') ?? 0);
        $discountAmount = (float) ($get('discount_amount') ?? 0);

        $total = $subtotal + $taxAmount + $deliveryCharge - $discountAmount;
        $total = max(0, $total); // Ensure total is not negative

        $set('total_amount', number_format($total, 2, '.', ''));
    }

    /**
     * Calculate individual item total based on weight and price.
     */
    private static function calculateItemTotal($set, $get): void
    {
        $orderedWeight = (float) ($get('ordered_weight') ?? 0);
        $actualWeight = (float) ($get('actual_weight') ?? 0);
        $pricePerKg = (float) ($get('price_per_kg') ?? 0);

        // Use actual weight if provided, otherwise use ordered weight
        $weightToUse = $actualWeight > 0 ? $actualWeight : $orderedWeight;

        $lineSubtotal = $weightToUse * $pricePerKg;
        $set('line_subtotal', number_format($lineSubtotal, 2, '.', ''));

        // Get tax percentage from the main order (not line level)
        $taxPercentage = (float) ($get('../../tax_percentage') ?? 18); // Get from parent form
        $lineTax = ($lineSubtotal * $taxPercentage) / 100;
        $set('line_tax', number_format($lineTax, 2, '.', ''));

        $lineDiscount = (float) ($get('line_discount') ?? 0);

        // CORRECT FORMULA: line_total = line_subtotal - line_discount + line_tax
        $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;
        $set('line_total', number_format($lineTotal, 2, '.', ''));

        // Ensure ALL required fields have valid values - NOT NULL
        $set('product_name', $get('product_name') ?: 'Product');
        $set('cut_name', $get('cut_name') ?: 'Cut');
        $set('sku', $get('sku') ?: '');
        $set('category_id', $get('category_id') ?: 0);
        $set('price_per_piece', $get('price_per_piece') ?: 0);
        $set('weight_unit', $get('weight_unit') ?: 'kg');
        $set('preparation_style', $get('preparation_style') ?: '');
        $set('line_discount', $get('line_discount') ?: 0);
        $set('special_instructions', $get('special_instructions') ?: '');
        $set('order_item_status', $get('order_item_status') ?: 0);
        $set('status', $get('status') ?: 1);
        $set('is_cleaned', $get('is_cleaned') ?? false);
        $set('is_skinless', $get('is_skinless') ?? false);

        Log::info('Line item calculation using order tax %', [
            'line_subtotal' => $lineSubtotal,
            'order_tax_percentage' => $taxPercentage,
            'line_tax' => $lineTax,
            'line_total' => $lineTotal,
        ]);
    }

    /**
     * Delayed order total calculation to ensure item calculations are complete
     */
    private static function updateOrderTotalsFromItemsDelayed($set, $get): void
    {
        // This ensures the calculation happens after the current field update is complete
        self::updateOrderTotalsFromItems($set, $get);
    }

    /**
     * Update order totals based on all items.
     */
    private static function updateOrderTotalsFromItems($set, $get): void
    {
        $items = $get('items') ?? [];
        $orderSubtotal = 0;

        // Only proceed if we have items
        if (empty($items)) {
            $set('subtotal', '0.00');
            // Also reset tax amount when no items
            $set('tax_amount', '0.00');
            self::calculateTotal($set, $get);

            return;
        }

        foreach ($items as $item) {
            $lineSubtotal = (float) ($item['line_subtotal'] ?? 0);
            $orderSubtotal += $lineSubtotal;
        }

        $set('subtotal', number_format($orderSubtotal, 2, '.', ''));

        // Recalculate tax amount based on current tax percentage
        $taxPercentage = (float) ($get('tax_percentage') ?? 18);
        $taxAmount = ($orderSubtotal * $taxPercentage) / 100;
        $set('tax_amount', number_format($taxAmount, 2, '.', ''));

        // Recalculate total with new subtotal and tax
        self::calculateTotal($set, $get);

        // Re-validate coupon if one is applied
        $couponCode = $get('coupon_code');
        if ($couponCode) {
            self::validateAndApplyCoupon($set, $get, $couponCode);
        }
    }

    /**
     * Validate and apply coupon code.
     */
    private static function validateAndApplyCoupon($set, $get, ?string $couponCode): void
    {
        if (empty($couponCode)) {
            $set('discount_amount', 0);
            $set('discount_type', CouponType::FixedAmount->value);
            $set('coupon_status', 'Enter coupon code');
            self::calculateTotal($set, $get);

            return;
        }

        // Find the coupon
        $coupon = Coupon::where('code', mb_strtoupper($couponCode))
            ->where('status', 1)
            ->first();

        if (! $coupon) {
            $set('discount_amount', 0);
            $set('discount_type', CouponType::FixedAmount->value);
            $set('coupon_status', '❌ Invalid coupon code');
            self::calculateTotal($set, $get);

            return;
        }

        // Check if coupon is valid (date range)
        $now = now();
        if ($coupon->valid_from > $now || $coupon->valid_until < $now) {
            $set('discount_amount', 0);
            $set('discount_type', CouponType::FixedAmount->value);
            $set('coupon_status', '❌ Coupon expired or not yet valid');
            self::calculateTotal($set, $get);

            return;
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            $set('discount_amount', 0);
            $set('discount_type', CouponType::FixedAmount->value);
            $set('coupon_status', '❌ Coupon usage limit reached');
            self::calculateTotal($set, $get);

            return;
        }

        // Check usage per customer
        $customerId = $get('customer_id');
        if ($coupon->usage_per_customer && $customerId) {
            $customerUsage = \App\Models\Orders::where('customer_id', $customerId)
                ->where('coupon_code', mb_strtoupper($couponCode))
                ->count();
            if ($customerUsage >= $coupon->usage_per_customer) {
                $set('discount_amount', 0);
                $set('discount_type', CouponType::FixedAmount->value);
                $set('coupon_status', '❌ Coupon usage per customer limit reached');
                self::calculateTotal($set, $get);

                return;
            }
        }

        $subtotal = (float) ($get('subtotal') ?? 0);

        // Check minimum order amount
        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            $set('discount_amount', 0);
            $set('discount_type', CouponType::FixedAmount->value);
            $set('coupon_status', "❌ Minimum order amount: ₹{$coupon->min_order_amount}");
            self::calculateTotal($set, $get);

            return;
        }

        // Calculate discount
        $discountAmount = 0;
        if ($coupon->type === CouponType::Percentage->value) {
            $discountAmount = ($subtotal * $coupon->value) / 100;

            // Apply maximum discount limit for percentage coupons
            if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
                $discountAmount = $coupon->max_discount_amount;
            }
        } else {
            $discountAmount = $coupon->value;
        }

        // Ensure discount doesn't exceed subtotal
        $discountAmount = min($discountAmount, $subtotal);

        // Apply the coupon
        $set('discount_amount', number_format($discountAmount, 2, '.', ''));
        $set('discount_type', $coupon->type);

        // Set success status with discount details
        $discountText = $coupon->type === CouponType::Percentage->value
            ? "{$coupon->value}%"
            : "₹{$coupon->value}";
        $set('coupon_status', "✅ Applied: {$discountText} discount (₹".number_format($discountAmount, 2).')');

        // Update coupon usage count
        $coupon->used_count = $coupon->used_count + 1;
        $coupon->save();

        // Recalculate total
        self::calculateTotal($set, $get);
    }

    /**
     * Get available coupons for the customer.
     */
    private static function getAvailableCoupons($get): array
    {
        $subtotal = (float) ($get('subtotal') ?? 0);
        $customerId = $get('customer_id');

        $query = Coupon::where('status', 1)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('min_order_amount')
                    ->orWhere('min_order_amount', '<=', $subtotal);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            });

        return $query->get()
            ->mapWithKeys(function ($coupon) {
                $discountText = $coupon->type === CouponType::Percentage->value
                    ? "{$coupon->value}%"
                    : "₹{$coupon->value}";

                $label = $discountText;
                if ($coupon->description) {
                    $label .= " - {$coupon->description}";
                }

                return [$coupon->code => $label];
            })
            ->toArray();
    }

    /**
     * Check if store vendor is eligible for delivery based on distance.
     *
     * @param  int  $deliveryAddressId
     * @param  int  $storeVendorId
     * @param  int  $storeId
     */
    private static function checkVendorEligibility($deliveryAddressId, $storeVendorId, $storeId): bool
    {
        // TODO: Replace with dynamic calculation using Haversine formula
        // For now, using static data for testing

        try {
            // Get delivery address coordinates
            $deliveryAddress = Address::find($deliveryAddressId);
            if (! $deliveryAddress) {
                return false;
            }

            // Get store coordinates
            $store = Store::find($storeId);
            if (! $store) {
                return false;
            }

            // STATIC DATA FOR NOW - Replace with actual lat/lng from database
            // Assuming delivery address has lat/lng fields (to be added later)
            $deliveryLat = $deliveryAddress->latitude ?? 13.0827; // Static: Bangalore example
            $deliveryLng = $deliveryAddress->longitude ?? 80.2707; // Static: Chennai example

            // Assuming store has lat/lng fields (to be added later)
            $storeLat = $store->latitude ?? 13.0850; // Static: Close to delivery
            $storeLng = $store->longitude ?? 80.2750; // Static: Close to delivery

            // Calculate distance using Haversine formula
            $distance = self::calculateDistance($deliveryLat, $deliveryLng, $storeLat, $storeLng);

            // STATIC: 10 minutes delivery radius = approximately 5km (can be adjusted)
            $maxDistanceKm = 5.0;

            Log::info('Vendor Eligibility Check', [
                'delivery_address_id' => $deliveryAddressId,
                'store_vendor_id' => $storeVendorId,
                'store_id' => $storeId,
                'distance_km' => $distance,
                'max_distance_km' => $maxDistanceKm,
                'is_eligible' => $distance <= $maxDistanceKm,
            ]);

            return $distance <= $maxDistanceKm;

        } catch (Exception $e) {
            Log::error('Error checking vendor eligibility: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @param  float  $lat1
     * @param  float  $lng1
     * @param  float  $lat2
     * @param  float  $lng2
     * @return float Distance in kilometers
     */
    private static function calculateDistance($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($lat1);
        $lngFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lngTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Find the nearest eligible store vendor for delivery.
     *
     * @param  int  $deliveryAddressId
     * @param  int  $storeId
     * @return array|null Returns ['user_id', 'vendor_name', 'distance_km', 'is_eligible'] or null if none found
     */
    private static function findNearestVendor($deliveryAddressId, $storeId): ?array
    {
        try {
            $deliveryAddress = Address::find($deliveryAddressId);
            if (! $deliveryAddress) {
                return null;
            }

            $store = Store::find($storeId);
            if (! $store) {
                return null;
            }

            // STATIC DATA FOR NOW - Replace with actual lat/lng from database
            $deliveryLat = $deliveryAddress->latitude ?? 13.0827; // Static: Bangalore example
            $deliveryLng = $deliveryAddress->longitude ?? 80.2707; // Static: Chennai example

            $storeLat = $store->latitude ?? 13.0850; // Static: Close to delivery
            $storeLng = $store->longitude ?? 80.2750; // Static: Close to delivery

            // Get all active store vendors for this store
            $storeVendors = \App\Models\User::where('user_role', 'store_vendor')
                ->where('store_id', $storeId)
                ->where('status', 1)
                ->get();

            if ($storeVendors->isEmpty()) {
                Log::warning('No store vendors found for store', ['store_id' => $storeId]);

                return null;
            }

            // Calculate distance for each vendor (using store location as vendor location for now)
            $nearestVendor = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($storeVendors as $vendor) {
                // Using store lat/lng for now - can be replaced with vendor-specific location if needed
                $distance = self::calculateDistance($deliveryLat, $deliveryLng, $storeLat, $storeLng);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearestVendor = $vendor;
                }
            }

            if (! $nearestVendor) {
                return null;
            }

            // STATIC: 10 minutes delivery radius = approximately 5km (can be adjusted)
            $maxDistanceKm = 5.0;
            $isEligible = $minDistance <= $maxDistanceKm;

            Log::info('Nearest Vendor Found', [
                'delivery_address_id' => $deliveryAddressId,
                'store_id' => $storeId,
                'vendor_id' => $nearestVendor->id,
                'vendor_name' => $nearestVendor->name,
                'distance_km' => $minDistance,
                'max_distance_km' => $maxDistanceKm,
                'is_eligible' => $isEligible,
            ]);

            return [
                'user_id' => $nearestVendor->id,
                'vendor_name' => $nearestVendor->name,
                'distance_km' => round($minDistance, 2),
                'is_eligible' => $isEligible,
            ];

        } catch (Exception $e) {
            Log::error('Error finding nearest vendor: '.$e->getMessage());

            return null;
        }
    }
}
