<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function isScopedToTenant(): bool
    {
        return true;
    }

    public static function getNavigationIcon(): ?string
    {
        return  config('ryoogen.users.resource.icon');
    }

    public static function getLabel(): string
    {
        return config('ryoogen.users.resource.label');
    }

    public static function getPluralLabel(): string
    {
        return config('ryoogen.users.resource.labels');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama pengguna')
                            ->type('text')
                            ->required()
                            ->minLength(2)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Surel (Email)')
                            ->placeholder('Masukkan alamat surel (email)')
                            ->type('email')
                            ->required()
                            ->minLength(2)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi (Password)')
                            ->placeholder('Masukkan kata sandi (password)')
                            ->password()
                            ->revealable()
                            ->confirmed()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(Page $livewire) => $livewire instanceof CreateRecord)
                            ->minLength(8),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Kata Sandi')
                            ->placeholder('Masukkan konfirmasi kata sandi')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(Page $livewire) => $livewire instanceof CreateRecord)
                            ->minLength(8),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('role')
                                    ->label('Level Pengguna')
                                    ->required()
                                    ->relationship('roles', 'name'),
                            ])->columns(1),

                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')
                                    ->default(fn($record) => $record?->avatar ? asset('storage/' . $record->avatar) : null)
                                    ->label('Avatar')
                                    ->directory('avatars')
                                    ->visibility('public')
                                    ->alignCenter(),
                            ])->columns(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->getStateUsing(function ($record) {
                        return $record->avatar ? asset('storage/' . $record->avatar) : 'https://gravatar.com/avatar/' . md5(strtolower(trim($record->email))) . '?s=1024';
                    })
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Pengguna'),

                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->label('Surel'),

                Tables\Columns\TextColumn::make('role')
                    ->sortable()
                    ->searchable()
                    ->label('Level'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Buat')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->created_at)->format('d/m/Y')),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Update')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'admin',
                        'kasir' => 'kasir',
                        'pelanggan' => 'pelanggan',
                        'pelayan' => 'pelayan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Pengguna')
                    ->schema([
                        Grid::make()->schema([
                            ImageEntry::make('avatar')
                                ->hiddenLabel()
                                ->circular()
                                ->getStateUsing(function ($record) {
                                    return $record->avatar ? asset('storage/' . $record->avatar) : 'https://gravatar.com/avatar/' . md5(strtolower(trim($record->email))) . '?s=1024';
                                })->alignment(Alignment::Center)
                        ])->columns(1),

                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('email')->label('Sure'),
                        TextEntry::make('role')->label('Level'),
                        TextEntry::make('created_at')->label('Tanggal Buat')
                            ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->created_at)->diffForHumans()),
                    ])->columns(2),
            ]);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'role', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Email' => $record->email,
            'Level' => $record->role,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
