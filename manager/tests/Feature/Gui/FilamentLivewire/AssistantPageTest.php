<?php

namespace Tests\Feature\Gui\FilamentLivewire;

use App\Filament\Pages\Assistant;
use App\Models\Conservation;
use App\Models\Label;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\FilamentLivewireTestHelpers;
use Tests\TestCase;

class AssistantPageTest extends TestCase
{
    use FilamentLivewireTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFilamentLivewire();
    }

    public function test_assistant_page_saves_messages_and_sends_conservation_id_to_orchestrator(): void
    {
        Http::fake(function (Request $request) {
            $path = (string) parse_url($request->url(), PHP_URL_PATH);
            $conservationId = rawurldecode((string) basename($path));

            $this->assertNotSame('', $conservationId);
            $this->assertNull(data_get($request->data(), 'id'));
            $this->assertStringNotContainsString('id=', (string) parse_url($request->url(), PHP_URL_QUERY));
            $this->assertDatabaseHas('messages', [
                'conservation_id' => $conservationId,
                'role' => 'user',
                'content' => 'Xin chào',
            ]);

            return Http::response([
                'message' => [
                    'role' => 'assistant',
                    'content' => 'Xin chào từ AI.',
                ],
                'model' => 'test-model',
            ]);
        });

        $this->assertSame(Width::Full, (new Assistant)->getMaxContentWidth());
        $this->assertSame(
            __('pages.customizations.assistant.subheading'),
            (new Assistant)->getSubheading(),
        );

        $component = $this->livewirePage(Assistant::class)
            ->assertOk()
            ->assertSee(__('pages.customizations.assistant.send'))
            ->assertSee(__('pages.customizations.assistant.conservations'))
            ->set('message', 'Xin chào')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSet('message', '')
            ->assertSet('awaitingAssistant', true)
            ->assertSet('messages.0.role', 'user')
            ->assertSet('messages.0.content', 'Xin chào')
            ->assertSet('messages', fn (array $messages): bool => count($messages) === 1)
            ->call('requestAssistantResponse')
            ->assertSet('awaitingAssistant', false)
            ->assertSet('messages.1.role', 'assistant')
            ->assertSet('messages.1.content', 'Xin chào từ AI.');

        $conservation = Conservation::query()->sole();

        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && str_ends_with($request->url(), "/api/v1/assistant/{$conservation->id}")
            && ! str_contains($request->url(), 'id=')
            && $request->hasHeader('X-Executor', $this->root->email));

        $this->assertSame($this->root->id, $conservation->created_by);
        $this->assertSame('...', $conservation->title);
        $this->assertSame(2, $conservation->messages()->count());

        $component
            ->call('startNewConservation')
            ->assertSet('activeConservationId', null)
            ->assertSet('messages', [])
            ->call('selectConservation', $conservation->id)
            ->assertSet('activeConservationId', $conservation->id)
            ->assertSet('messages.0.content', 'Xin chào')
            ->assertSet('messages.1.content', 'Xin chào từ AI.');
    }

    public function test_assistant_page_validates_and_can_clear_messages(): void
    {
        $component = $this->livewirePage(Assistant::class)
            ->set('message', '   ')
            ->call('sendMessage')
            ->assertHasErrors(['message' => 'required']);

        $component
            ->set('messages', [['role' => 'user', 'content' => 'Temporary']])
            ->call('clearConversation')
            ->assertSet('messages', [])
            ->assertSet('message', '');
    }

    public function test_assistant_page_attaches_system_resources_to_a_message(): void
    {
        $label = Label::query()->create([
            'name' => 'Suspicious traffic',
            'color' => '#ef4444',
        ]);

        $component = $this->livewirePage(Assistant::class)
            ->assertActionExists('attachResource')
            ->callAction('attachResource', [
                'type' => 'label',
                'id' => $label->id,
            ])
            ->assertHasNoFormErrors()
            ->assertSet('attachedResources.0.type', 'label')
            ->assertSet('attachedResources.0.id', $label->id)
            ->assertSet('attachedResources.0.label', 'Suspicious traffic')
            ->set('message', 'Review this resource')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSet('attachedResources', [])
            ->assertSet('messages.0.resources.0.type', 'label')
            ->assertSet('messages.0.resources.0.data.name', 'Suspicious traffic')
            ->assertSee(__('pages.customizations.assistant.attached_resources', ['count' => 1]))
            ->assertSee('Suspicious traffic');

        $message = Conservation::query()->sole()->messages()->sole();

        $this->assertSame('label', $message->resources[0]['type']);
        $this->assertSame($label->id, $message->resources[0]['id']);
        $this->assertSame('Suspicious traffic', $message->resources[0]['data']['name']);

        $component
            ->call('selectConservation', $message->conservation_id)
            ->assertSet('messages.0.resources.0.id', $label->id)
            ->assertSee('Suspicious traffic');
    }

    public function test_assistant_messages_render_markdown_without_raw_html(): void
    {
        Http::fake([
            '*' => Http::response([
                'message' => [
                    'role' => 'assistant',
                    'content' => "**Bold reply**\n\n<script>alert('unsafe')</script>",
                ],
                'model' => 'test-model',
            ]),
        ]);

        $this->livewirePage(Assistant::class)
            ->set('message', 'Render markdown')
            ->call('sendMessage')
            ->call('requestAssistantResponse')
            ->assertSeeHtml('<strong>Bold reply</strong>')
            ->assertDontSeeHtml('<script>');
    }

    public function test_conservation_sidebar_can_search_filter_rename_and_pin(): void
    {
        $first = Conservation::query()->create([
            'title' => 'First chat',
            'created_by' => $this->root->id,
        ]);
        $message = $first->messages()->create([
            'role' => 'user',
            'content' => 'Delete me with the conservation.',
        ]);
        $pinned = Conservation::query()->create([
            'title' => 'Pinned chat',
            'is_pinned' => true,
            'created_by' => $this->root->id,
        ]);

        $component = $this->livewirePage(Assistant::class)
            ->assertSet('conservations', fn (array $items): bool => count($items) === 2)
            ->set('conservationFilter', 'pinned')
            ->assertSet('conservations', fn (array $items): bool => count($items) === 1
                && $items[0]['id'] === $pinned->id)
            ->call('toggleConservationPin', $first->id)
            ->assertSet('conservations', fn (array $items): bool => count($items) === 2)
            ->call('beginRenameConservation', $first->id)
            ->set('editingConservationTitle', 'Renamed chat')
            ->call('saveConservationTitle')
            ->assertHasNoErrors()
            ->set('conservationSearch', 'Renamed')
            ->assertSet('conservations', fn (array $items): bool => count($items) === 1
                && $items[0]['title'] === 'Renamed chat');

        $this->assertDatabaseHas('conservations', [
            'id' => $first->id,
            'title' => 'Renamed chat',
            'is_pinned' => true,
        ]);
        $component
            ->assertSet('editingConservationId', null)
            ->call('selectConservation', $first->id)
            ->assertActionExists(
                'deleteConservation',
                fn (Action $action): bool => $action->isConfirmationRequired(),
            )
            ->callAction('deleteConservation', [], ['conservationId' => $first->id])
            ->assertSet('activeConservationId', null)
            ->assertSet('messages', []);

        $this->assertDatabaseMissing('conservations', ['id' => $first->id]);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_conservations_and_messages_are_loaded_incrementally(): void
    {
        $target = Conservation::query()->create([
            'title' => 'Long conversation',
            'created_by' => $this->root->id,
        ]);

        foreach (range(1, 25) as $index) {
            Conservation::query()->create([
                'title' => "Conversation {$index}",
                'created_by' => $this->root->id,
            ]);
        }

        foreach (range(1, 45) as $index) {
            $target->messages()->create([
                'role' => $index % 2 === 0 ? 'assistant' : 'user',
                'content' => "Message {$index}",
            ]);
        }

        $this->livewirePage(Assistant::class)
            ->assertSet('conservations', fn (array $items): bool => count($items) === 20)
            ->assertSet('hasMoreConservations', true)
            ->call('selectConservation', $target->id)
            ->assertSet('activeConservationId', $target->id)
            ->assertSet('messages', fn (array $items): bool => count($items) === 40
                && $items[0]['content'] === 'Message 6')
            ->assertSet('hasMoreMessages', true)
            ->call('loadMoreConservations')
            ->assertSet('conservations', fn (array $items): bool => count($items) === 26)
            ->assertSet('hasMoreConservations', false)
            ->call('loadMoreMessages')
            ->assertSet('messages', fn (array $items): bool => count($items) === 45
                && $items[0]['content'] === 'Message 1')
            ->assertSet('hasMoreMessages', false);
    }
}
