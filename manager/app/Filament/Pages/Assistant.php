<?php

namespace App\Filament\Pages;

use App\Models\Conservation;
use App\Services\AssistantResource;
use App\Services\Identification;
use App\Services\Orchestrator;
use App\Services\Security;
use App\Traits\Filament\Generals\Components\Button;
use App\Traits\Filament\Generals\Components\Field;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\Client\ConnectionException;
use Throwable;
use UnitEnum;

class Assistant extends Page
{
    use Button, Field;

    protected const CONSERVATIONS_PER_LOAD = 20;

    protected const MESSAGES_PER_LOAD = 40;

    protected const MAX_ATTACHED_RESOURCES = 10;

    protected string $view = 'filament.pages.assistant';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 1;

    protected Width|string|null $maxContentWidth = Width::Full;

    public string $message = '';

    /** @var array<int, array{role: string, content: string, resources: array}> */
    public array $messages = [];

    /** @var array<int, array{type: string, id: string, label: string}> */
    public array $attachedResources = [];

    /** @var array<int, array{id: string, title: string, is_pinned: bool}> */
    public array $conservations = [];

    public ?string $activeConservationId = null;

    public string $conservationSearch = '';

    public string $conservationFilter = 'all';

    public ?string $editingConservationId = null;

    public string $editingConservationTitle = '';

    public bool $awaitingAssistant = false;

    public int $conservationLimit = self::CONSERVATIONS_PER_LOAD;

    public int $messageLimit = self::MESSAGES_PER_LOAD;

    public bool $hasMoreConservations = false;

    public bool $hasMoreMessages = false;

    public function mount(): void
    {
        $this->refreshConservations();

        $firstConservationId = $this->conservations[0]['id'] ?? null;
        if ($firstConservationId !== null) {
            $this->selectConservation($firstConservationId);
        }
    }

    public static function getNavigationLabel(): string
    {
        return __('pages.customizations.assistant.title');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigations.groups.utilities');
    }

    public function getTitle(): string|Htmlable
    {
        return __('pages.customizations.assistant.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('pages.customizations.assistant.subheading');
    }

    public static function canAccess(): bool
    {
        return Security::can(Conservation::class, 'viewAny');
    }

    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:4000'],
            'attachedResources' => ['array', 'max:'.self::MAX_ATTACHED_RESOURCES],
            'attachedResources.*' => ['array'],
            'attachedResources.*.type' => ['required', 'string'],
            'attachedResources.*.id' => ['required', 'string'],
        ];
    }

    public function sendMessage(): void
    {
        if ($this->awaitingAssistant) {
            return;
        }

        $this->message = trim($this->message);
        $this->validate();
        $this->authorize('chat', Conservation::class);

        $conservation = $this->activeConservation()
            ?? $this->createConservation();

        $userMessage = [
            'role' => 'user',
            'content' => $this->message,
            'resources' => AssistantResource::snapshots($this->attachedResources),
        ];
        $this->messages[] = $userMessage;
        $this->messageLimit++;

        $conservation->messages()->create($userMessage);
        $this->message = '';
        $this->attachedResources = [];
        $this->resetValidation();
        $this->refreshConservations();
        $this->awaitingAssistant = true;
        $this->dispatch('assistant-requested');
    }

    public function requestAssistantResponse(): void
    {
        $lastMessage = $this->messages[array_key_last($this->messages)] ?? null;
        if (
            ! $this->awaitingAssistant
            || ($lastMessage['role'] ?? null) !== 'user'
        ) {
            return;
        }

        $conservation = $this->activeConservation();
        if ($conservation === null) {
            $this->awaitingAssistant = false;

            return;
        }

        $this->authorize('chat', $conservation);

        try {
            $response = Orchestrator::chat(
                $conservation->id,
                requesterEmail: (string) Identification::getEmail(),
            );
        } catch (ConnectionException $exception) {
            report($exception);
            $this->notifyFailure(__('pages.customizations.assistant.errors.connection'));
            $this->awaitingAssistant = false;

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->notifyFailure(__('pages.customizations.assistant.errors.upstream'));
            $this->awaitingAssistant = false;

            return;
        }

        if ($response->failed()) {
            $detail = trim((string) $response->json('detail'));
            $this->notifyFailure(
                $detail !== ''
                    ? $detail
                    : __('pages.customizations.assistant.errors.upstream')
            );
            $this->awaitingAssistant = false;

            return;
        }

        $content = trim((string) $response->json('message.content'));
        if ($content === '') {
            $this->notifyFailure(__('pages.customizations.assistant.errors.empty'));
            $this->awaitingAssistant = false;

            return;
        }

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content,
            'resources' => [],
        ];
        $this->messageLimit++;
        $conservation->messages()->create([
            'role' => 'assistant',
            'content' => $content,
        ]);
        $this->awaitingAssistant = false;
        $this->refreshConservations();
        $this->dispatch('assistant-message-added');
    }

    public function startNewConservation(): void
    {
        $this->activeConservationId = null;
        $this->editingConservationId = null;
        $this->editingConservationTitle = '';
        $this->messages = [];
        $this->messageLimit = self::MESSAGES_PER_LOAD;
        $this->hasMoreMessages = false;
        $this->message = '';
        $this->attachedResources = [];
        $this->resetValidation();
    }

    public function selectConservation(string $conservationId): void
    {
        $conservation = $this->conservationQuery()->find($conservationId);
        if ($conservation === null) {
            return;
        }

        $this->authorize('view', $conservation);

        $this->activeConservationId = $conservation->id;
        $this->editingConservationId = null;
        $this->editingConservationTitle = '';
        $this->messageLimit = self::MESSAGES_PER_LOAD;
        $this->loadMessages($conservation);
        $this->message = '';
        $this->attachedResources = [];
        $this->resetValidation();
        $this->dispatch('assistant-message-added');
    }

    public function beginRenameConservation(string $conservationId): void
    {
        $conservation = $this->conservationQuery()->find($conservationId);
        if ($conservation === null) {
            return;
        }

        $this->authorize('update', $conservation);

        $this->editingConservationId = $conservation->id;
        $this->editingConservationTitle = $conservation->title;
        $this->resetValidation('editingConservationTitle');
    }

    public function saveConservationTitle(): void
    {
        $this->editingConservationTitle = trim($this->editingConservationTitle);
        $this->validate([
            'editingConservationTitle' => ['required', 'string', 'max:255'],
        ]);

        $conservation = $this->editingConservationId === null
            ? null
            : $this->conservationQuery()->find($this->editingConservationId);
        if ($conservation === null) {
            return;
        }

        $this->authorize('update', $conservation);

        $conservation->update(['title' => $this->editingConservationTitle]);
        $this->cancelRenameConservation();
        $this->refreshConservations();
    }

    public function cancelRenameConservation(): void
    {
        $this->editingConservationId = null;
        $this->editingConservationTitle = '';
        $this->resetValidation('editingConservationTitle');
    }

    public function toggleConservationPin(string $conservationId): void
    {
        $conservation = $this->conservationQuery()->find($conservationId);
        if ($conservation === null) {
            return;
        }

        $this->authorize('pin', $conservation);

        $conservation->update(['is_pinned' => ! $conservation->is_pinned]);
        $this->refreshConservations();
    }

    public function deleteConservation(string $conservationId): void
    {
        $conservation = $this->conservationQuery()->find($conservationId);
        if ($conservation === null) {
            return;
        }

        $this->authorize('delete', $conservation);

        $isActive = $this->activeConservationId === $conservation->id;
        $isEditing = $this->editingConservationId === $conservation->id;
        $conservation->delete();

        if ($isActive) {
            $this->startNewConservation();
        } elseif ($isEditing) {
            $this->cancelRenameConservation();
        }

        $this->refreshConservations();
    }

    public function deleteConservationAction(): Action
    {
        return self::button(
            'deleteConservation',
            __('pages.customizations.assistant.delete'),
            Heroicon::OutlinedTrash,
            function (array $arguments): void {
                $conservationId = (string) ($arguments['conservationId'] ?? '');
                if ($conservationId !== '') {
                    $this->deleteConservation($conservationId);
                }
            },
        )
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(__('pages.customizations.assistant.delete_heading'))
            ->modalDescription(__('pages.customizations.assistant.delete_confirmation'))
            ->modalSubmitActionLabel(__('pages.customizations.assistant.delete'));
    }

    public function attachResourceAction(): Action
    {
        return self::button(
            'attachResource',
            __('pages.customizations.assistant.attach_resource'),
            Heroicon::OutlinedPaperClip,
        )
            ->modalHeading(__('pages.customizations.assistant.attach_resource'))
            ->modalSubmitActionLabel(__('pages.customizations.assistant.attach'))
            ->disabled(fn (): bool => $this->awaitingAssistant
                || count($this->attachedResources) >= self::MAX_ATTACHED_RESOURCES)
            ->schema([
                self::select('type', __('pages.customizations.assistant.resource_type'))
                    ->options(fn (): array => AssistantResource::typeOptions())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('id', null)),
                self::select('id', __('pages.customizations.assistant.resource'))
                    ->options(fn (Get $get): array => AssistantResource::options((string) $get('type')))
                    ->getSearchResultsUsing(fn (Get $get, string $search): array => AssistantResource::options(
                        (string) $get('type'),
                        $search,
                    ))
                    ->getOptionLabelUsing(fn (Get $get, mixed $value): ?string => AssistantResource::optionLabel(
                        (string) $get('type'),
                        (string) $value,
                    ))
                    ->required()
                    ->disabled(fn (Get $get): bool => blank($get('type'))),
            ])
            ->action(function (array $data): void {
                $reference = AssistantResource::reference(
                    (string) ($data['type'] ?? ''),
                    (string) ($data['id'] ?? ''),
                );
                if ($reference === null) {
                    return;
                }

                $isAttached = collect($this->attachedResources)->contains(
                    fn (array $attached): bool => $attached['type'] === $reference['type']
                        && $attached['id'] === $reference['id'],
                );
                if (! $isAttached) {
                    $this->attachedResources[] = $reference;
                }
            });
    }

    public function removeAttachedResource(int $index): void
    {
        if (! array_key_exists($index, $this->attachedResources)) {
            return;
        }

        unset($this->attachedResources[$index]);
        $this->attachedResources = array_values($this->attachedResources);
    }

    public function resourceTypeLabel(string $type): string
    {
        return AssistantResource::typeLabel($type);
    }

    public function updatedConservationSearch(): void
    {
        $this->conservationLimit = self::CONSERVATIONS_PER_LOAD;
        $this->refreshConservations();
    }

    public function updatedConservationFilter(): void
    {
        if (! in_array($this->conservationFilter, ['all', 'pinned'], true)) {
            $this->conservationFilter = 'all';
        }

        $this->conservationLimit = self::CONSERVATIONS_PER_LOAD;
        $this->refreshConservations();
    }

    public function loadMoreConservations(): void
    {
        if (! $this->hasMoreConservations) {
            return;
        }

        $this->conservationLimit += self::CONSERVATIONS_PER_LOAD;
        $this->refreshConservations();
    }

    public function loadMoreMessages(): void
    {
        if (! $this->hasMoreMessages) {
            return;
        }

        $conservation = $this->activeConservation();
        if ($conservation === null) {
            return;
        }

        $this->messageLimit += self::MESSAGES_PER_LOAD;
        $this->loadMessages($conservation);
    }

    public function clearConversation(): void
    {
        $this->messages = [];
        $this->message = '';
        $this->attachedResources = [];
        $this->resetValidation();
    }

    protected function notifyFailure(string $message): void
    {
        Notification::make()
            ->title(__('pages.customizations.assistant.errors.title'))
            ->body($message)
            ->danger()
            ->send();
    }

    protected function activeConservation(): ?Conservation
    {
        if ($this->activeConservationId === null) {
            return null;
        }

        return $this->conservationQuery()->find($this->activeConservationId);
    }

    protected function createConservation(): Conservation
    {
        $this->authorize('create', Conservation::class);

        $conservation = Conservation::query()->create([
            'title' => '...',
        ]);

        $this->activeConservationId = $conservation->id;

        return $conservation;
    }

    protected function refreshConservations(): void
    {
        $search = trim($this->conservationSearch);

        $items = $this->conservationQuery()
            ->when(
                $search !== '',
                fn ($query) => $query->where('title', 'like', "%{$search}%"),
            )
            ->when(
                $this->conservationFilter === 'pinned',
                fn ($query) => $query->where('is_pinned', true),
            )
            ->orderByDesc('is_pinned')
            ->latest('updated_at')
            ->latest('id')
            ->limit($this->conservationLimit + 1)
            ->get(['id', 'title', 'is_pinned']);

        $this->hasMoreConservations = $items->count() > $this->conservationLimit;
        $this->conservations = $items
            ->take($this->conservationLimit)
            ->map(fn (Conservation $conservation): array => [
                'id' => $conservation->id,
                'title' => $conservation->title,
                'is_pinned' => $conservation->is_pinned,
            ])
            ->all();
    }

    protected function loadMessages(Conservation $conservation): void
    {
        $this->authorize('view', $conservation);

        $items = $conservation->messages()
            ->reorder()
            ->orderByDesc('id')
            ->limit($this->messageLimit + 1)
            ->get(['role', 'content', 'resources']);

        $this->hasMoreMessages = $items->count() > $this->messageLimit;
        $this->messages = $items
            ->take($this->messageLimit)
            ->reverse()
            ->values()
            ->map(fn ($message): array => [
                'role' => $message->role,
                'content' => $message->content,
                'resources' => $message->resources ?? [],
            ])
            ->all();
    }

    protected function conservationQuery()
    {
        return Conservation::query()->where('created_by', Identification::getId());
    }
}
