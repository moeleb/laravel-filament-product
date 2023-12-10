<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str ;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shops';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
                        TextInput::make('name')->required()->unique()
                        ->afterStateUpdated(function(string $operation , $state, Forms\Set $set){
                            if($operation!=='create') {
                                return ;
                            }
                            $set('slug', Str::slug($state)) ;
                        }),
                        TextInput::make('slug')->dehydrated()->unique(Brand::class , 'slug', ignoreRecord:true)
                        ->required(),
                        TextInput::make('url')->columnSpan('full') , 
                        MarkdownEditor::make('description')->columnSpan('full')

                    ])->columns(2),

                ]),

                Group::make()->schema([
                    Section::make('status')->schema([
                        Toggle::make('is_visible')->default(true),
                        Toggle::make('is_featured'),
                        DatePicker::make('published_at')->default(now()),
                    ]),    
                    Group::make()->schema([
                        Section::make('Color')->schema([
                            ColorPicker::make('primary_hex')->label('Primary Color'),
                        ]),   
                    ]),                
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('url')->searchable()->sortable()->label('Website URL'),
                ColorColumn::make('primary_hex')->searchable()->sortable()->label('Primary color'),
                ColorColumn::make('primary_hex')->searchable()->sortable()->label('Primary color'),
                IconColumn::make('is_visible')->boolean()->toggleable(),
                TextColumn::make('updated_at')->searchable()->sortable()->date(),



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
            BrandResource::getRelations(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
