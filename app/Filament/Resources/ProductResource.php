<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Str ;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Shops';
    protected static ?int $navigationSort = 0;


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
                        TextInput::make('slug')->dehydrated()->unique(Product::class , 'slug', ignoreRecord:true)
                        ->required(),
                        MarkdownEditor::make('description')->columnSpan('full') , 
                    ])->columns(2),

                    Section::make()->schema([
                        TextInput::make('sku')->required()->unique(),
                        TextInput::make('price')->required(),
                        TextInput::make('qunatity')->required(),
                        Select::make('type')->options([
                            'downloadable' =>ProductTypeEnum::DOWNLOADABLE->value,
                            'deliverable' =>ProductTypeEnum::DELIVERABLE->value,
                        ])->required(),
                    ])->columns(2),
                ]),

                Group::make()->schema([
                    Section::make('status')->schema([
                        Toggle::make('is_visible')->default(true),
                        Toggle::make('is_featured'),
                        DatePicker::make('published_at')->default(now()),
                    ]),

                    Section::make('image')->schema([
                        FileUpload::make('image')->directory('form-attachments')->preserveFilenames()->image()
                        ->imageEditor()
                    ])->collapsible(),
                    

                    Section::make('Assosiations')->schema([
                        Select::make('brand_id')->relationship('brand', 'name')
                    ])
                    
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image') ,
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('brand.name')->searchable()->sortable(),
                IconColumn::make('is_visible')->boolean()->toggleable(),
                TextColumn::make('price')->searchable()->sortable(),
                TextColumn::make('qunatity')->searchable()->sortable(),
                TextColumn::make('published_at')->searchable()->sortable()->date(),
                TextColumn::make('type')->searchable()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_visible')->label('visibility')->boolean()->
                trueLabel('show visible products')
                ->falseLabel('hide products')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
