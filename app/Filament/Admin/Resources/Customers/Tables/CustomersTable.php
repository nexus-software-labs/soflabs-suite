<?php

namespace App\Filament\Admin\Resources\Customers\Tables;

use App\Mail\CustomerVerifiedMail;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('customer.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('user.email')
                    ->label(__('customer.table.email'))
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->copyable(),

                TextColumn::make('locker_code')
                    ->label(__('customer.table.locker_code'))
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-m-lock-closed')
                    ->copyable(),

                TextColumn::make('phone')
                    ->label(__('customer.table.phone'))
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                TextColumn::make('country')
                    ->label(__('customer.table.country'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'SV' => __('customer.countries.SV'),
                        'GT' => __('customer.countries.GT'),
                        'HN' => __('customer.countries.HN'),
                        'NI' => __('customer.countries.NI'),
                        'CR' => __('customer.countries.CR'),
                        'PA' => __('customer.countries.PA'),
                        'US' => __('customer.countries.US'),
                        'MX' => __('customer.countries.MX'),
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('branch.name')
                    ->label(__('customer.table.branch'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('customerTier.name')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->customerTier?->color ?? 'gray')
                    ->placeholder('Sin categoría')
                    ->toggleable(),

                IconColumn::make('verified_at')
                    ->label(__('customer.table.verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable()
                    ->tooltip(fn ($record): string => $record->verified_at
                        ? __('customer.tooltips.verified_at', ['date' => $record->verified_at->format('d/m/Y H:i')])
                        : __('customer.tooltips.pending_verification')),

                TextColumn::make('cedula_rnc')
                    ->label(__('customer.table.cedula_rnc'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date_opened')
                    ->label(__('customer.table.registered'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->label(__('customer.table.updated'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->label(__('customer.filters.country'))
                    ->options([
                        'SV' => __('customer.countries.SV'),
                        'GT' => __('customer.countries.GT'),
                        'HN' => __('customer.countries.HN'),
                        'NI' => __('customer.countries.NI'),
                        'CR' => __('customer.countries.CR'),
                        'PA' => __('customer.countries.PA'),
                        'US' => __('customer.countries.US'),
                        'MX' => __('customer.countries.MX'),
                    ])
                    ->native(false),

                Filter::make('verified_at')
                    ->label(__('customer.filters.verification_status'))
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'verified' => $query->whereNotNull('verified_at'),
                        'pending' => $query->whereNull('verified_at'),
                        default => $query,
                    })
                    ->form([
                        Select::make('value')
                            ->label(__('customer.filters.status'))
                            ->options([
                                'verified' => __('customer.filters.verified'),
                                'pending' => __('customer.filters.pending'),
                            ])
                            ->native(false),
                    ]),

                Filter::make('has_locker')
                    ->label(__('customer.filters.has_locker'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('locker_code')),

                SelectFilter::make('customer_tier_id')
                    ->label('Categoría')
                    ->relationship('customerTier', 'name', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('date_opened')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('customer.filters.created_from')),
                        DatePicker::make('created_until')
                            ->label(__('customer.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_opened', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_opened', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('verify')
                    ->label(__('customer.actions.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(__('customer.actions.verify_modal_heading'))
                    ->modalDescription(__('customer.actions.verify_modal_description'))
                    ->modalSubmitActionLabel(__('customer.actions.verify_modal_submit'))
                    ->visible(fn ($record) => ! $record->verified_at)
                    ->action(function ($record) {
                        $record->update([
                            'verified_at' => now(),
                        ]);

                        // Enviar correo de verificación
                        try {
                            Mail::to($record->user->email)->send(new CustomerVerifiedMail($record));
                        } catch (\Exception $e) {
                            // Si falla el envío del correo, no bloqueamos la verificación
                            Notification::make()
                                ->warning()
                                ->title(__('customer.actions.verify_notification_title'))
                                ->body(__('customer.actions.verify_notification_body'))
                                ->send();
                        }
                    })
                    ->successNotificationTitle(__('customer.actions.verify_success')),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
