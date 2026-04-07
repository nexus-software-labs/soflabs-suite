<?php

namespace App\Filament\Admin\Resources\Payments\Schemas;

use App\Models\PreAlertOrder;
use App\Models\PrintOrder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('payment.infolist.section_payment_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reference_number')
                                    ->label(__('payment.infolist.reference'))
                                    ->weight('bold')
                                    ->copyable(),

                                TextEntry::make('gateway')
                                    ->label(__('payment.infolist.method'))
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => Arr::get(__('payment.gateways'), $state, $state)),

                                TextEntry::make('amount')
                                    ->label(__('payment.infolist.amount'))
                                    ->money('USD'),

                                TextEntry::make('status')
                                    ->label(__('payment.infolist.status'))
                                    ->badge()
                                    ->formatStateUsing(fn ($record) => __("payment.statuses.{$record->status}"))
                                    ->color(fn ($record) => $record->status_color),

                                TextEntry::make('customer_name')
                                    ->label(__('payment.infolist.customer')),

                                TextEntry::make('customer_email')
                                    ->label(__('payment.infolist.email')),

                                TextEntry::make('transfer_reference')
                                    ->label(__('payment.infolist.transfer_reference'))
                                    ->placeholder('—')
                                    ->visible(fn ($record) => $record->gateway === 'transfer'),

                                TextEntry::make('transfer_notes')
                                    ->label(__('payment.infolist.transfer_notes'))
                                    ->placeholder('—')
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => $record->gateway === 'transfer'),

                                TextEntry::make('created_at')
                                    ->label(__('payment.infolist.created_at'))
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('completed_at')
                                    ->label(__('payment.infolist.completed_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('—'),
                            ]),
                    ]),

                Section::make(__('payment.infolist.section_transfer_proof'))
                    ->schema([
                        TextEntry::make('transfer_proof')
                            ->label('')
                            ->html()
                            ->getStateUsing(function ($record) {
                                $media = $record->getFirstMedia('transfer_proof');
                                if (! $media) {
                                    return '<p class="text-gray-500">'.e(__('payment.infolist.proof_no_uploaded')).'</p>';
                                }
                                $url = $media->getUrl();
                                if (str_starts_with($media->mime_type, 'image/')) {
                                    return '<a href="'.e($url).'" target="_blank" class="text-primary-600 hover:underline">'.
                                        '<img src="'.e($url).'" alt="'.e(__('payment.infolist.proof_alt')).'" class="max-w-md rounded-lg border shadow" />'.
                                        '</a>';
                                }

                                return '<a href="'.e($url).'" target="_blank" class="text-primary-600 hover:underline">'.e(__('payment.infolist.proof_view_pdf')).'</a>';
                            })
                            ->visible(fn ($record) => $record->gateway === 'transfer'),
                    ])
                    ->visible(fn ($record) => $record->gateway === 'transfer'),

                Section::make(__('payment.infolist.section_order'))
                    ->schema([
                        TextEntry::make('paymentable_id')
                            ->label(__('payment.infolist.id'))
                            ->formatStateUsing(function ($record) {
                                $payable = $record->paymentable;
                                if (! $payable) {
                                    return '—';
                                }
                                if ($payable instanceof PreAlertOrder) {
                                    return __('payment.infolist.prealert_id', ['track' => $payable->track_number]);
                                }
                                if ($payable instanceof PrintOrder) {
                                    return __('payment.infolist.order_id', ['number' => $payable->order_number]);
                                }

                                return $payable->id;
                            }),
                    ]),
            ]);
    }
}
