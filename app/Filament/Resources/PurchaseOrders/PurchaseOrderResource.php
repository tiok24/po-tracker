<?php

namespace App\Filament\Resources\PurchaseOrders;

use App\Enums\OrderStatus;
use App\Filament\Resources\PurchaseOrders\Pages\CreatePurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\EditPurchaseOrder;
use App\Filament\Resources\PurchaseOrders\Pages\ListPurchaseOrders;
use App\Models\PurchaseOrder;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Purchase Orders';

    /** Status yang boleh dipilih admin (Delayed otomatis). */
    protected static function statusOptions(): array
    {
        return collect(OrderStatus::selectable())
            ->mapWithKeys(fn ($s) => [$s->value => $s->value])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Order Number')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->required(),
                TextInput::make('product_name')
                    ->label('Product Name')
                    ->required(),
                TextInput::make('order_quantity')
                    ->label('Order Quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('progress')
                    ->label('Progres Pengerjaan')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0)
                    ->suffix('%')
                    ->required(),
                DatePicker::make('po_date')
                    ->label('PO Date')
                    ->required()
                    ->default(today()),
                Select::make('order_status')
                    ->label('Order Status')
                    ->options(static::statusOptions())
                    ->default('Waiting')
                    ->required()
                    ->live(),
                DatePicker::make('production_start')
                    ->label('Production Start')
                    ->required(fn (Get $get) => $get('order_status') === OrderStatus::OnProcess->value),
                DatePicker::make('estimated_finish')
                    ->label('Estimated Finish')
                    ->afterOrEqual('production_start'),
                DatePicker::make('actual_finish')
                    ->label('Actual Finish')
                    ->helperText('Otomatis terisi saat status Completed.'),
                Textarea::make('remarks')
                    ->label('Remarks (informasi kendala)')
                    ->columnSpanFull()
                    ->required(fn (Get $get) => $get('order_status') === OrderStatus::Hold->value)
                    ->helperText('Wajib diisi jika status Hold. Tampil ke client.'),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('po_date', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order No.')->searchable()->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable(),
                TextColumn::make('product_name')->label('Product')->searchable(),
                TextColumn::make('order_quantity')->label('Qty')->numeric(),
                TextColumn::make('progress')->label('Progres')->suffix('%')->sortable(),
                TextColumn::make('po_date')->label('PO Date')->date('d M Y')->sortable(),
                TextColumn::make('estimated_finish')->label('Est. Finish')->date('d M Y')->sortable(),
                TextColumn::make('display_status')->label('Status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn ($s) => [$s->value => $s->value])
                            ->all()
                    )
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->displayStatus($data['value'])
                        : $query),
            ])
            ->recordActions([
                Action::make('updateStatus')
                    ->label('Update status')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->schema([
                        Select::make('order_status')
                            ->label('Order Status')
                            ->options(static::statusOptions())
                            ->required()
                            ->live(),
                        TextInput::make('progress')
                            ->label('Progres Pengerjaan')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),
                        Textarea::make('remarks')
                            ->label('Remarks')
                            ->required(fn (Get $get) => $get('order_status') === OrderStatus::Hold->value),
                    ])
                    ->fillForm(fn (PurchaseOrder $record) => [
                        'order_status' => $record->order_status->value,
                        'progress'     => $record->progress,
                        'remarks'      => $record->remarks,
                    ])
                    ->action(fn (PurchaseOrder $record, array $data) => $record->update($data)),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPurchaseOrders::route('/'),
            'create' => CreatePurchaseOrder::route('/create'),
            'edit'   => EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}