<?php

namespace App\Filament\Resources;
use App\Enums\OrderStatusEnum;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Shops';
    protected static ?int $navigationSort =3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Order Details')->schema([
                        TextInput::make('number')->default('OR-' . random_int(10000,999999))->disabled()->dehydrated()->required(),
                        Select::make('customer_id')->relationship('customer', 'name')->searchable()->required()->preload(),
                        Select::make('type')->options([
                            'pending'=> OrderStatusEnum::PENDING->value,
                            'processing'=> OrderStatusEnum::PROCESSING->value,
                            'completed'=> OrderStatusEnum::COMPLETED->value,
                            'declined'=> OrderStatusEnum::DECLINED->value,
                        ])->columnSpanFull(),
                        MarkdownEditor::make('notes')->columnSpanFull()
                    ])->columns(2),
                    Step::make('OrderItems')->schema([
                        Repeater::make('items')->relationship()
                        ->schema([
                            Select::make('product_id')->label('product')
                            ->options(Product::query()->pluck('name','id')),
                            TextInput::make('qunatity')->numeric()->default(1)->required(),
                            TextInput::make('unit_price')->disabled()->dehydrated()->numeric()->required(),
                        ]),
                    ])->columns(3),

                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('customer.name')->sortable()->searchable()->toggleable(),
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('total_price')->sortable()->searchable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()->money(),
                ]),
                TextColumn::make('created_at')->sortable()->searchable()->date()->label('order date')


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),

                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
