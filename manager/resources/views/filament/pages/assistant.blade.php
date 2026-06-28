<x-filament-panels::page full-height class="defly-assistant-page">
    <div
        class="defly-assistant"
        x-data="{
            scrollToLatest(behavior = 'auto') {
                this.$nextTick(() => {
                    requestAnimationFrame(() => {
                        this.$refs.messages.scrollTo({
                            top: this.$refs.messages.scrollHeight,
                            behavior,
                        })
                    })
                })
            },
        }"
        x-init="scrollToLatest()"
        x-on:assistant-message-added.window="scrollToLatest('smooth')"
        x-on:assistant-requested.window="$nextTick(() => { $wire.requestAssistantResponse(); scrollToLatest('smooth') })"
    >
        <section class="defly-assistant__panel" aria-live="polite">
            <aside class="defly-assistant__sidebar">
                <div class="defly-assistant__sidebar-header">
                    <strong>{{ __('pages.customizations.assistant.conservations') }}</strong>
                    <button
                        type="button"
                        title="{{ __('pages.customizations.assistant.new_conservation') }}"
                        wire:click="startNewConservation"
                        wire:loading.attr="disabled"
                    >
                        <x-filament::icon icon="heroicon-o-plus" />
                    </button>
                </div>

                <div class="defly-assistant__sidebar-tools">
                    <label class="defly-assistant__search">
                        <x-filament::icon icon="heroicon-o-magnifying-glass" />
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="conservationSearch"
                            placeholder="{{ __('pages.customizations.assistant.search_conservations') }}"
                        />
                    </label>

                    <div class="defly-assistant__filter">
                        <x-filament::input.wrapper prefix-icon="heroicon-o-funnel">
                            <x-filament::input.select wire:model.live="conservationFilter">
                                <option value="all">{{ __('pages.customizations.assistant.filter_all') }}</option>
                                <option value="pinned">{{ __('pages.customizations.assistant.filter_pinned') }}</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <nav
                    class="defly-assistant__conservations"
                    aria-label="{{ __('pages.customizations.assistant.conservations') }}"
                >
                    @forelse ($conservations as $conservation)
                        <div
                            @class([
                                'defly-assistant__conservation-item',
                                'defly-assistant__conservation-item--active' => $activeConservationId === $conservation['id'],
                            ])
                            wire:key="conservation-{{ $conservation['id'] }}"
                        >
                            @if ($editingConservationId === $conservation['id'])
                                <form
                                    class="defly-assistant__rename"
                                    wire:submit.prevent="saveConservationTitle"
                                >
                                    <input
                                        type="text"
                                        maxlength="255"
                                        wire:model="editingConservationTitle"
                                        x-init="$nextTick(() => $el.select())"
                                    />
                                    <button
                                        type="submit"
                                        title="{{ __('pages.customizations.assistant.save') }}"
                                    >
                                        <x-filament::icon icon="heroicon-o-check" />
                                    </button>
                                    <button
                                        type="button"
                                        title="{{ __('pages.customizations.assistant.cancel') }}"
                                        wire:click="cancelRenameConservation"
                                    >
                                        <x-filament::icon icon="heroicon-o-x-mark" />
                                    </button>
                                </form>
                            @else
                                <button
                                    class="defly-assistant__conservation"
                                    type="button"
                                    wire:click="selectConservation('{{ $conservation['id'] }}')"
                                >
                                    <x-filament::icon icon="heroicon-o-chat-bubble-left" />
                                    <span>{{ $conservation['title'] }}</span>
                                    @if ($conservation['is_pinned'])
                                        <x-filament::icon
                                            class="defly-assistant__pin"
                                            icon="heroicon-s-bookmark"
                                        />
                                    @endif
                                </button>

                                <details
                                    class="defly-assistant__conservation-menu"
                                    x-on:click.outside="$el.removeAttribute('open')"
                                >
                                    <summary title="{{ __('pages.customizations.assistant.manage_conservation') }}">
                                        <x-filament::icon icon="heroicon-o-ellipsis-horizontal" />
                                    </summary>
                                    <div>
                                        <button
                                            type="button"
                                            wire:click="beginRenameConservation('{{ $conservation['id'] }}')"
                                        >
                                            <x-filament::icon icon="heroicon-o-pencil-square" />
                                            {{ __('pages.customizations.assistant.rename') }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="toggleConservationPin('{{ $conservation['id'] }}')"
                                        >
                                            <x-filament::icon icon="heroicon-o-bookmark" />
                                            {{ $conservation['is_pinned']
                                                ? __('pages.customizations.assistant.unpin')
                                                : __('pages.customizations.assistant.pin') }}
                                        </button>
                                        <button
                                            class="defly-assistant__menu-danger"
                                            type="button"
                                            wire:click="mountAction('deleteConservation', { conservationId: '{{ $conservation['id'] }}' })"
                                        >
                                            <x-filament::icon icon="heroicon-o-trash" />
                                            {{ __('pages.customizations.assistant.delete') }}
                                        </button>
                                    </div>
                                </details>

                                <div class="defly-assistant__mobile-actions">
                                    <button
                                        type="button"
                                        title="{{ __('pages.customizations.assistant.rename') }}"
                                        wire:click="beginRenameConservation('{{ $conservation['id'] }}')"
                                    >
                                        <x-filament::icon icon="heroicon-o-pencil-square" />
                                    </button>
                                    <button
                                        type="button"
                                        title="{{ $conservation['is_pinned']
                                            ? __('pages.customizations.assistant.unpin')
                                            : __('pages.customizations.assistant.pin') }}"
                                        wire:click="toggleConservationPin('{{ $conservation['id'] }}')"
                                    >
                                        <x-filament::icon icon="heroicon-o-bookmark" />
                                    </button>
                                    <button
                                        class="defly-assistant__mobile-danger"
                                        type="button"
                                        title="{{ __('pages.customizations.assistant.delete') }}"
                                        wire:click="mountAction('deleteConservation', { conservationId: '{{ $conservation['id'] }}' })"
                                    >
                                        <x-filament::icon icon="heroicon-o-trash" />
                                    </button>
                                </div>
                            @endif

                            @if ($editingConservationId === $conservation['id'])
                                @error('editingConservationTitle')
                                    <p class="defly-assistant__rename-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    @empty
                        <p class="defly-assistant__no-conservations">
                            {{ __('pages.customizations.assistant.no_conservations') }}
                        </p>
                    @endforelse

                    @if ($hasMoreConservations)
                        <div
                            class="defly-assistant__lazy-loader"
                            wire:intersect.once="loadMoreConservations"
                            wire:key="load-conservations-{{ $conservationLimit }}"
                        >
                            <x-filament::loading-indicator />
                        </div>
                    @endif
                </nav>
            </aside>

            <div class="defly-assistant__messages" x-ref="messages">
                @if ($hasMoreMessages)
                    <div
                        class="defly-assistant__lazy-loader defly-assistant__message-loader"
                        wire:intersect.once.preserve-scroll="loadMoreMessages"
                        wire:key="load-messages-{{ $activeConservationId }}-{{ $messageLimit }}"
                    >
                        <x-filament::loading-indicator />
                    </div>
                @endif

                @forelse ($messages as $index => $item)
                    <article
                        @class([
                            'defly-assistant__message-row',
                            'defly-assistant__message-row--user' => $item['role'] === 'user',
                        ])
                        wire:key="assistant-message-{{ $index }}"
                    >
                        @if ($item['role'] === 'assistant')
                            <div class="defly-assistant__message-avatar">
                                <x-filament::icon icon="heroicon-o-sparkles" />
                            </div>
                        @endif

                        <div
                            @class([
                                'defly-assistant__message',
                                'defly-assistant__message--user' => $item['role'] === 'user',
                                'defly-assistant__message--ai' => $item['role'] === 'assistant',
                            ])
                        >
                            @if (! empty($item['resources']))
                                <details class="defly-assistant__message-resources">
                                    <summary>
                                        <span>
                                            <x-filament::icon icon="heroicon-o-paper-clip" />
                                            {{ __('pages.customizations.assistant.attached_resources', [
                                                'count' => count($item['resources']),
                                            ]) }}
                                        </span>
                                        <x-filament::icon
                                            class="defly-assistant__resources-chevron"
                                            icon="heroicon-o-chevron-down"
                                        />
                                    </summary>

                                    <div class="defly-assistant__message-resources-list">
                                        @foreach ($item['resources'] as $resource)
                                            <div
                                                class="defly-assistant__message-resource"
                                                title="{{ $resource['id'] ?? '' }}"
                                            >
                                                <x-filament::icon icon="heroicon-o-cube" />
                                                <span>
                                                    <small>{{ $this->resourceTypeLabel($resource['type'] ?? '') }}</small>
                                                    <strong>{{ $resource['label'] ?? ($resource['id'] ?? '') }}</strong>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif

                            @if ($item['role'] === 'assistant')
                                <strong>{{ __('pages.customizations.assistant.ai') }}</strong>
                                <div class="defly-assistant__markdown">
                                    {!! \Illuminate\Support\Str::markdown($item['content'], [
                                        'html_input' => 'strip',
                                        'allow_unsafe_links' => false,
                                    ]) !!}
                                </div>
                            @else
                                <div>{{ $item['content'] }}</div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="defly-assistant__empty">
                        <div class="defly-assistant__empty-icon">
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" />
                        </div>
                        <strong>{{ __('pages.customizations.assistant.empty_title') }}</strong>
                        <p>{{ __('pages.customizations.assistant.empty_description') }}</p>
                    </div>
                @endforelse

                <article
                    class="defly-assistant__message-row defly-assistant__typing"
                    wire:loading.flex
                    wire:target="requestAssistantResponse"
                >
                    <div class="defly-assistant__message-avatar">
                        <x-filament::icon icon="heroicon-o-sparkles" />
                    </div>
                    <div class="defly-assistant__message defly-assistant__message--ai">
                        <strong>{{ __('pages.customizations.assistant.ai') }}</strong>
                        <div class="defly-assistant__typing-dots">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </article>
            </div>

            <form class="defly-assistant__composer" wire:submit.prevent="sendMessage">
                <label class="defly-assistant__sr-only" for="assistant-message">
                    {{ __('pages.customizations.assistant.input_label') }}
                </label>

                <div class="defly-assistant__input-shell">
                    @if ($attachedResources !== [])
                        <div class="defly-assistant__attached-resources">
                            @foreach ($attachedResources as $index => $resource)
                                <span wire:key="attached-resource-{{ $resource['type'] }}-{{ $resource['id'] }}">
                                    <x-filament::icon icon="heroicon-o-cube" />
                                    <small>{{ $this->resourceTypeLabel($resource['type']) }}</small>
                                    {{ $resource['label'] }}
                                    <button
                                        type="button"
                                        title="{{ __('pages.customizations.assistant.remove_resource') }}"
                                        wire:click="removeAttachedResource({{ $index }})"
                                        @disabled($awaitingAssistant)
                                    >
                                        <x-filament::icon icon="heroicon-o-x-mark" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <textarea
                        id="assistant-message"
                        wire:model="message"
                        rows="2"
                        maxlength="4000"
                        placeholder="{{ __('pages.customizations.assistant.placeholder') }}"
                        @disabled($awaitingAssistant)
                        wire:loading.attr="disabled"
                        wire:target="sendMessage,requestAssistantResponse"
                        x-on:keydown.enter="
                            if (! $event.shiftKey && ! $event.isComposing) {
                                $event.preventDefault()
                                $event.currentTarget.form.requestSubmit()
                            }
                        "
                    ></textarea>

                    <div class="defly-assistant__composer-footer">
                        <div class="defly-assistant__composer-meta">
                            <button
                                class="defly-assistant__attach-button"
                                type="button"
                                title="{{ __('pages.customizations.assistant.attach_resource') }}"
                                wire:click="mountAction('attachResource')"
                                wire:loading.attr="disabled"
                                wire:target="sendMessage,requestAssistantResponse"
                                @disabled($awaitingAssistant || count($attachedResources) >= 10)
                            >
                                <x-filament::icon icon="heroicon-o-paper-clip" />
                                <span>{{ __('pages.customizations.assistant.attach_resource') }}</span>
                            </button>
                            <span>{{ __('pages.customizations.assistant.enter_hint') }}</span>
                        </div>

                        <x-filament::button
                            icon="heroicon-o-paper-airplane"
                            :disabled="$awaitingAssistant"
                            size="lg"
                            type="submit"
                            wire:target="sendMessage,requestAssistantResponse"
                        >
                            {{ __('pages.customizations.assistant.send') }}
                        </x-filament::button>
                    </div>
                </div>

                @error('message')
                    <p class="defly-assistant__error">{{ $message }}</p>
                @enderror
            </form>
        </section>
    </div>

    <style>
        .defly-assistant-page {
            height: calc(100dvh - 4rem);
            min-height: 0;
        }

        .defly-assistant-page > .fi-page-header-main-ctn {
            min-height: 0;
        }

        .defly-assistant-page .fi-page-main {
            display: flex;
            height: auto;
            min-height: 0;
            flex: 1;
        }

        .defly-assistant-page .fi-page-content {
            height: auto;
            min-height: 0;
            flex: 1;
        }

        .defly-assistant {
            width: 100%;
            height: 100%;
            min-height: 0;
        }

        .defly-assistant__panel {
            display: grid;
            width: 100%;
            height: 100%;
            min-height: 0;
            grid-template-columns: 17rem minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr) auto;
            grid-template-areas:
                "sidebar messages"
                "sidebar composer";
            overflow: hidden;
            border: 1px solid rgb(229 231 235);
            border-radius: 1rem;
            background: white;
            box-shadow: 0 10px 30px rgb(15 23 42 / .06);
        }

        .defly-assistant__sidebar {
            display: grid;
            grid-area: sidebar;
            min-width: 0;
            grid-template-rows: auto auto minmax(0, 1fr);
            overflow: hidden;
            border-right: 1px solid rgb(229 231 235);
            background: rgb(249 250 251);
        }

        .defly-assistant__sidebar-header {
            display: flex;
            min-height: 4rem;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem 1rem;
            border-bottom: 1px solid rgb(229 231 235);
        }

        .defly-assistant__sidebar-header strong {
            overflow: hidden;
            color: rgb(31 41 55);
            font-size: .875rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .defly-assistant__sidebar-header button {
            display: grid;
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            place-items: center;
            border-radius: .55rem;
            color: white;
            background: var(--primary-600);
        }

        .defly-assistant__sidebar-header button:hover {
            background: var(--primary-500);
        }

        .defly-assistant__sidebar-header button:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        .defly-assistant__sidebar-header button svg,
        .defly-assistant__conservation > svg,
        .defly-assistant__sidebar-tools svg,
        .defly-assistant__rename svg,
        .defly-assistant__conservation-menu svg {
            width: 1rem;
            height: 1rem;
        }

        .defly-assistant__sidebar-tools {
            display: grid;
            gap: .45rem;
            padding: .6rem;
            border-bottom: 1px solid rgb(229 231 235);
        }

        .defly-assistant__search {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: .45rem;
            border: 1px solid rgb(209 213 219);
            border-radius: .55rem;
            padding: .45rem .55rem;
            color: rgb(107 114 128);
            background: white;
        }

        .defly-assistant__search input {
            width: 100%;
            min-width: 0;
            border: 0;
            color: rgb(55 65 81);
            background: transparent;
            font-size: .78rem;
            outline: none;
        }

        .defly-assistant__filter {
            min-width: 0;
        }

        .defly-assistant__filter .fi-input-wrp {
            box-shadow: none;
        }

        .defly-assistant__conservations {
            display: flex;
            min-height: 0;
            flex-direction: column;
            gap: .25rem;
            overflow-y: auto;
            padding: .6rem;
        }

        .defly-assistant__conservation-item {
            position: relative;
            flex: 0 0 auto;
            border-radius: .65rem;
        }

        .defly-assistant__conservation-item:hover {
            background: rgb(229 231 235 / .7);
        }

        .defly-assistant__conservation-item--active {
            color: var(--primary-700);
            background: var(--primary-50);
        }

        .defly-assistant__conservation {
            display: grid;
            width: 100%;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: .55rem;
            border-radius: .65rem;
            padding: .65rem 2.3rem .65rem .7rem;
            color: rgb(75 85 99);
            text-align: left;
        }

        .defly-assistant__conservation:hover {
            color: rgb(31 41 55);
        }

        .defly-assistant__conservation-item--active .defly-assistant__conservation {
            color: var(--primary-700);
        }

        .defly-assistant__conservation span {
            overflow: hidden;
            font-size: .825rem;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .defly-assistant__conservation .defly-assistant__pin {
            width: .8rem;
            height: .8rem;
            color: var(--primary-500);
        }

        .defly-assistant__conservation-menu {
            position: absolute;
            top: .35rem;
            right: .35rem;
            z-index: 5;
        }

        .defly-assistant__conservation-menu summary {
            display: grid;
            width: 1.75rem;
            height: 1.75rem;
            cursor: pointer;
            list-style: none;
            place-items: center;
            border-radius: .45rem;
            color: rgb(107 114 128);
        }

        .defly-assistant__conservation-menu summary::-webkit-details-marker {
            display: none;
        }

        .defly-assistant__conservation-menu summary:hover,
        .defly-assistant__conservation-menu[open] summary {
            color: rgb(31 41 55);
            background: rgb(209 213 219);
        }

        .defly-assistant__conservation-menu > div {
            position: absolute;
            top: 2rem;
            right: 0;
            width: 10rem;
            overflow: hidden;
            border: 1px solid rgb(229 231 235);
            border-radius: .65rem;
            padding: .3rem;
            background: white;
            box-shadow: 0 10px 25px rgb(15 23 42 / .16);
        }

        .defly-assistant__conservation-menu > div button {
            display: flex;
            width: 100%;
            align-items: center;
            gap: .5rem;
            border-radius: .45rem;
            padding: .5rem .55rem;
            color: rgb(55 65 81);
            font-size: .78rem;
            text-align: left;
        }

        .defly-assistant__conservation-menu > div button:hover {
            background: rgb(243 244 246);
        }

        .defly-assistant__conservation-menu > div .defly-assistant__menu-danger {
            color: rgb(220 38 38);
        }

        .defly-assistant__conservation-menu > div .defly-assistant__menu-danger:hover {
            background: rgb(254 242 242);
        }

        .defly-assistant__mobile-actions {
            display: none;
        }

        .defly-assistant__rename {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto auto;
            align-items: center;
            gap: .25rem;
            padding: .35rem;
        }

        .defly-assistant__rename input {
            min-width: 0;
            border: 1px solid var(--primary-500);
            border-radius: .45rem;
            padding: .4rem .45rem;
            color: rgb(31 41 55);
            background: white;
            font-size: .78rem;
            outline: none;
        }

        .defly-assistant__rename button {
            display: grid;
            width: 1.75rem;
            height: 1.75rem;
            place-items: center;
            border-radius: .4rem;
            color: rgb(75 85 99);
        }

        .defly-assistant__rename button:hover {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        .defly-assistant__rename-error {
            padding: 0 .5rem .35rem;
            color: rgb(220 38 38);
            font-size: .7rem;
        }

        .defly-assistant__no-conservations {
            padding: 1.25rem .5rem;
            color: rgb(156 163 175);
            font-size: .78rem;
            text-align: center;
        }

        .defly-assistant__lazy-loader {
            display: grid;
            min-height: 2.5rem;
            flex: 0 0 2.5rem;
            place-items: center;
            color: var(--primary-500);
        }

        .defly-assistant__lazy-loader svg {
            width: 1.1rem;
            height: 1.1rem;
        }

        .defly-assistant__message-loader {
            width: 100%;
        }

        .defly-assistant__message-row {
            display: flex;
            align-items: center;
        }

        .defly-assistant__empty-icon {
            display: grid;
            flex: 0 0 auto;
            place-items: center;
            color: var(--primary-600);
            background: linear-gradient(145deg, var(--primary-50), var(--primary-100));
        }

        .defly-assistant__messages {
            grid-area: messages;
            display: flex;
            min-height: 0;
            flex-direction: column;
            gap: 1.25rem;
            overflow-y: auto;
            padding: 2rem clamp(1rem, 3vw, 3rem);
            background:
                radial-gradient(circle at top left, color-mix(in oklab, var(--primary-50) 70%, transparent), transparent 28rem),
                rgb(249 250 251);
            scroll-behavior: smooth;
        }

        .defly-assistant__message-row {
            width: 100%;
            align-self: center;
            align-items: flex-end;
            gap: .65rem;
        }

        .defly-assistant__message-row--user {
            flex-direction: row-reverse;
        }

        .defly-assistant__message-avatar {
            display: grid;
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            place-items: center;
            border: 1px solid rgb(229 231 235);
            border-radius: .65rem;
            color: var(--primary-600);
            background: rgb(243 244 246);
            font-size: .7rem;
            font-weight: 800;
        }

        .defly-assistant__message-avatar svg {
            width: 1rem;
            height: 1rem;
        }

        .defly-assistant__message {
            max-width: min(78%, 52rem);
            padding: .8rem 1rem;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgb(15 23 42 / .05);
        }

        .defly-assistant__message > strong {
            display: block;
            margin-bottom: .3rem;
            font-size: .72rem;
        }

        .defly-assistant__message > div {
            overflow-wrap: anywhere;
            line-height: 1.6;
        }

        .defly-assistant__message--user > div {
            white-space: pre-wrap;
        }

        .defly-assistant__markdown {
            overflow-x: auto;
        }

        .defly-assistant__markdown > :first-child {
            margin-top: 0;
        }

        .defly-assistant__markdown > :last-child {
            margin-bottom: 0;
        }

        .defly-assistant__markdown :is(p, ul, ol, blockquote, pre, table) {
            margin-block: .65rem;
        }

        .defly-assistant__markdown :is(ul, ol) {
            padding-left: 1.25rem;
        }

        .defly-assistant__markdown ul {
            list-style: disc;
        }

        .defly-assistant__markdown ol {
            list-style: decimal;
        }

        .defly-assistant__markdown a {
            color: var(--primary-600);
            text-decoration: underline;
            text-underline-offset: .15rem;
        }

        .defly-assistant__markdown blockquote {
            padding-left: .75rem;
            border-left: 3px solid var(--primary-400);
            color: rgb(75 85 99);
        }

        .defly-assistant__markdown code {
            padding: .1rem .3rem;
            border-radius: .3rem;
            background: rgb(229 231 235);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: .875em;
        }

        .defly-assistant__markdown pre {
            overflow-x: auto;
            padding: .75rem;
            border-radius: .6rem;
            background: rgb(31 41 55);
            color: rgb(243 244 246);
        }

        .defly-assistant__markdown pre code {
            padding: 0;
            background: transparent;
            color: inherit;
        }

        .defly-assistant__markdown table {
            width: 100%;
            border-collapse: collapse;
        }

        .defly-assistant__markdown :is(th, td) {
            padding: .4rem .6rem;
            border: 1px solid rgb(209 213 219);
            text-align: left;
        }

        .defly-assistant__message--user {
            border-bottom-right-radius: .25rem;
            color: white;
            background: var(--primary-600);
        }

        .defly-assistant__message--ai {
            border: 1px solid rgb(229 231 235);
            border-bottom-left-radius: .25rem;
            color: rgb(31 41 55);
            background: rgb(243 244 246);
        }

        .defly-assistant__attached-resources {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .defly-assistant__message-resources {
            width: min(100%, 32rem);
            margin-bottom: .6rem;
        }

        .defly-assistant__attached-resources > span {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: .3rem;
            border: 1px solid rgb(209 213 219);
            border-radius: .5rem;
            padding: .3rem .45rem;
            color: rgb(55 65 81);
            background: rgb(249 250 251);
            font-size: .75rem;
            line-height: 1.2;
        }

        .defly-assistant__attached-resources > span > svg {
            width: .9rem;
            height: .9rem;
            flex: 0 0 .9rem;
        }

        .defly-assistant__attached-resources small {
            color: rgb(107 114 128);
            font-size: .68rem;
        }

        .defly-assistant__message-resources summary {
            display: flex;
            min-width: 12rem;
            cursor: pointer;
            list-style: none;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border: 1px solid rgb(209 213 219);
            border-radius: .55rem;
            padding: .45rem .6rem;
            color: rgb(55 65 81);
            background: rgb(249 250 251);
            font-size: .75rem;
            font-weight: 600;
        }

        .defly-assistant__message-resources summary::-webkit-details-marker {
            display: none;
        }

        .defly-assistant__message-resources summary > span {
            display: inline-flex;
            min-width: 0;
            align-items: center;
            gap: .4rem;
        }

        .defly-assistant__message-resources summary svg {
            width: .95rem;
            height: .95rem;
            flex: 0 0 .95rem;
        }

        .defly-assistant__message-resources .defly-assistant__resources-chevron {
            transition: transform .15s ease;
        }

        .defly-assistant__message-resources[open] .defly-assistant__resources-chevron {
            transform: rotate(180deg);
        }

        .defly-assistant__message-resources-list {
            display: grid;
            max-height: 12rem;
            gap: .35rem;
            overflow-y: auto;
            margin-top: .4rem;
            border: 1px solid rgb(209 213 219);
            border-radius: .55rem;
            padding: .4rem;
            background: rgb(249 250 251);
        }

        .defly-assistant__message-resource {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: .5rem;
            border-radius: .4rem;
            padding: .45rem .5rem;
            color: rgb(55 65 81);
            background: white;
        }

        .defly-assistant__message-resource > svg {
            width: 1rem;
            height: 1rem;
            flex: 0 0 1rem;
        }

        .defly-assistant__message-resource > span {
            display: grid;
            min-width: 0;
            gap: .1rem;
        }

        .defly-assistant__message-resource small {
            color: rgb(107 114 128);
            font-size: .65rem;
            font-weight: 500;
        }

        .defly-assistant__message-resource strong {
            overflow: hidden;
            font-size: .75rem;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .defly-assistant__message--user .defly-assistant__message-resources summary,
        .defly-assistant__message--user .defly-assistant__message-resources-list {
            border-color: rgb(255 255 255 / .3);
            color: white;
            background: rgb(255 255 255 / .12);
        }

        .defly-assistant__message--user .defly-assistant__message-resource {
            color: white;
            background: rgb(255 255 255 / .1);
        }

        .defly-assistant__message--user .defly-assistant__message-resource small {
            color: rgb(255 255 255 / .72);
        }

        .defly-assistant__empty {
            display: grid;
            width: min(100%, 32rem);
            margin: auto;
            place-items: center;
            text-align: center;
            color: rgb(107 114 128);
        }

        .defly-assistant__empty-icon {
            width: 4rem;
            height: 4rem;
            margin-bottom: 1rem;
            border-radius: 1.25rem;
            box-shadow: 0 8px 24px color-mix(in oklab, var(--primary-500) 12%, transparent);
        }

        .defly-assistant__empty-icon svg {
            width: 1.75rem;
            height: 1.75rem;
        }

        .defly-assistant__empty strong {
            color: rgb(17 24 39);
            font-size: 1.1rem;
        }

        .defly-assistant__empty p {
            margin: .4rem 0 0;
            font-size: .875rem;
        }

        .defly-assistant__composer {
            grid-area: composer;
            padding: 1rem clamp(1rem, 3vw, 3rem) 1.25rem;
            border-top: 1px solid rgb(229 231 235);
            background: white;
        }

        .defly-assistant__input-shell {
            width: 100%;
            margin-inline: auto;
            overflow: hidden;
            border: 1px solid rgb(209 213 219);
            border-radius: .9rem;
            background: white;
            box-shadow: 0 2px 10px rgb(15 23 42 / .04);
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .defly-assistant__input-shell:focus-within {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary-500) 12%, transparent);
        }

        .defly-assistant__attached-resources {
            padding: .7rem 1rem 0;
        }

        .defly-assistant__attached-resources button {
            display: grid;
            width: 1rem;
            height: 1rem;
            margin-left: .1rem;
            place-items: center;
            border-radius: .25rem;
            color: rgb(107 114 128);
        }

        .defly-assistant__attached-resources button:hover {
            color: rgb(220 38 38);
            background: rgb(254 226 226);
        }

        .defly-assistant__attached-resources button svg {
            width: .75rem;
            height: .75rem;
        }

        .defly-assistant__input-shell textarea {
            display: block;
            width: 100%;
            min-height: 4rem;
            max-height: 12rem;
            resize: vertical;
            border: 0;
            padding: .9rem 1rem .35rem;
            color: rgb(17 24 39);
            background: transparent;
            outline: none;
        }

        .defly-assistant__input-shell textarea::placeholder {
            color: rgb(156 163 175);
        }

        .defly-assistant__composer-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .35rem .5rem .5rem 1rem;
        }

        .defly-assistant__composer-meta {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: .75rem;
        }

        .defly-assistant__composer-meta > span {
            color: rgb(156 163 175);
            font-size: .72rem;
        }

        .defly-assistant__attach-button {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: .45rem;
            padding: .35rem .45rem;
            color: rgb(75 85 99);
            font-size: .78rem;
            font-weight: 500;
        }

        .defly-assistant__attach-button:hover {
            color: var(--primary-600);
            background: color-mix(in oklab, var(--primary-500) 8%, transparent);
        }

        .defly-assistant__attach-button:disabled {
            cursor: not-allowed;
            opacity: .5;
        }

        .defly-assistant__attach-button svg {
            width: 1rem;
            height: 1rem;
        }

        .defly-assistant__error {
            width: 100%;
            margin: .4rem auto 0;
            color: rgb(220 38 38);
            font-size: .8rem;
        }

        .defly-assistant__sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        .defly-assistant__typing {
            display: none;
        }

        .defly-assistant__typing-dots {
            display: flex;
            gap: .25rem;
            padding-block: .2rem;
        }

        .defly-assistant__typing-dots span {
            width: .4rem;
            height: .4rem;
            border-radius: 999px;
            background: rgb(156 163 175);
            animation: defly-assistant-pulse 1.2s infinite ease-in-out;
        }

        .defly-assistant__typing-dots span:nth-child(2) { animation-delay: .15s; }
        .defly-assistant__typing-dots span:nth-child(3) { animation-delay: .3s; }

        .dark .defly-assistant__panel {
            border-color: rgb(47 47 52);
            background: rgb(24 24 27);
            box-shadow: 0 10px 30px rgb(0 0 0 / .18);
        }

        .dark .defly-assistant__sidebar {
            border-color: rgb(47 47 52);
            background: rgb(24 24 27);
        }

        .dark .defly-assistant__sidebar-header {
            border-color: rgb(47 47 52);
        }

        .dark .defly-assistant__sidebar-tools {
            border-color: rgb(47 47 52);
        }

        .dark .defly-assistant__search {
            border-color: rgb(63 63 70);
            color: rgb(161 161 170);
            background: rgb(39 39 42);
        }

        .dark .defly-assistant__search input,
        .dark .defly-assistant__rename input {
            color: rgb(244 244 245);
        }

        .dark .defly-assistant__sidebar-header strong {
            color: rgb(244 244 245);
        }

        .dark .defly-assistant__conservation {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__conservation-item:hover {
            background: rgb(39 39 42);
        }

        .dark .defly-assistant__conservation:hover {
            color: rgb(244 244 245);
        }

        .dark .defly-assistant__conservation-item--active {
            background: color-mix(in oklab, var(--primary-500) 14%, rgb(39 39 42));
        }

        .dark .defly-assistant__conservation-item--active .defly-assistant__conservation {
            color: var(--primary-300);
        }

        .dark .defly-assistant__conservation-menu summary {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__conservation-menu summary:hover,
        .dark .defly-assistant__conservation-menu[open] summary {
            color: rgb(244 244 245);
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__conservation-menu > div {
            border-color: rgb(63 63 70);
            background: rgb(39 39 42);
            box-shadow: 0 10px 25px rgb(0 0 0 / .35);
        }

        .dark .defly-assistant__conservation-menu > div button {
            color: rgb(212 212 216);
        }

        .dark .defly-assistant__conservation-menu > div button:hover {
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__conservation-menu > div .defly-assistant__menu-danger {
            color: rgb(248 113 113);
        }

        .dark .defly-assistant__conservation-menu > div .defly-assistant__menu-danger:hover {
            background: rgb(127 29 29 / .25);
        }

        .dark .defly-assistant__rename input {
            border-color: var(--primary-500);
            background: rgb(39 39 42);
        }

        .dark .defly-assistant__messages {
            background: rgb(24 24 27);
        }

        .dark .defly-assistant__composer {
            border-color: rgb(47 47 52);
            background: rgb(24 24 27);
        }

        .dark .defly-assistant__input-shell {
            border-color: rgb(63 63 70);
            background: rgb(39 39 42);
            box-shadow: none;
        }

        .dark .defly-assistant__input-shell:focus-within {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 3px color-mix(in oklab, var(--primary-500) 14%, transparent);
        }

        .dark .defly-assistant__input-shell textarea {
            color: rgb(244 244 245);
        }

        .dark .defly-assistant__input-shell textarea::placeholder,
        .dark .defly-assistant__composer-meta > span,
        .dark .defly-assistant__empty {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__attached-resources > span {
            border-color: rgb(82 82 91);
            color: rgb(228 228 231);
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__attached-resources small {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__message--ai .defly-assistant__message-resources summary,
        .dark .defly-assistant__message--ai .defly-assistant__message-resources-list {
            border-color: rgb(82 82 91);
            color: rgb(228 228 231);
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__message--ai .defly-assistant__message-resource {
            color: rgb(228 228 231);
            background: rgb(39 39 42);
        }

        .dark .defly-assistant__message--ai .defly-assistant__message-resource small {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__attached-resources button,
        .dark .defly-assistant__attach-button {
            color: rgb(161 161 170);
        }

        .dark .defly-assistant__attached-resources button:hover {
            color: rgb(248 113 113);
            background: rgb(127 29 29 / .25);
        }

        .dark .defly-assistant__attach-button:hover {
            color: var(--primary-300);
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__empty strong {
            color: rgb(250 250 250);
        }

        .dark .defly-assistant__empty-icon {
            color: var(--primary-400);
            background: rgb(39 39 42);
            box-shadow: 0 8px 24px rgb(0 0 0 / .2);
        }

        .dark .defly-assistant__message--ai,
        .dark .defly-assistant__message-avatar {
            border-color: rgb(63 63 70);
            color: rgb(244 244 245);
            background: rgb(39 39 42);
        }

        .dark .defly-assistant__message--user {
            border-color: var(--primary-500);
            color: white;
            background: var(--primary-600);
        }

        .dark .defly-assistant__markdown a {
            color: var(--primary-300);
        }

        .dark .defly-assistant__markdown blockquote {
            color: rgb(212 212 216);
        }

        .dark .defly-assistant__markdown code {
            background: rgb(63 63 70);
        }

        .dark .defly-assistant__markdown pre {
            background: rgb(9 9 11);
        }

        .dark .defly-assistant__markdown :is(th, td) {
            border-color: rgb(82 82 91);
        }

        @keyframes defly-assistant-pulse {
            0%, 60%, 100% { opacity: .35; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-.2rem); }
        }

        @media (max-width: 900px) {
            .defly-assistant__panel {
                grid-template-columns: minmax(0, 1fr);
                grid-template-rows: auto minmax(0, 1fr) auto;
                grid-template-areas:
                    "sidebar"
                    "messages"
                    "composer";
                border-radius: .75rem;
            }

            .defly-assistant__sidebar {
                height: 11rem;
                border-right: 0;
                border-bottom: 1px solid rgb(229 231 235);
            }

            .dark .defly-assistant__sidebar {
                border-bottom-color: rgb(47 47 52);
            }

            .defly-assistant__sidebar-header {
                min-height: 3.5rem;
                padding: .5rem .75rem;
            }

            .defly-assistant__sidebar-tools {
                grid-template-columns: minmax(0, 1fr) 8.5rem;
                padding: .4rem;
            }

            .defly-assistant__conservations {
                flex-direction: row;
                overflow-x: auto;
                overflow-y: hidden;
                padding: .4rem;
            }

            .defly-assistant__conservation-item {
                width: 16rem;
                flex: 0 0 16rem;
            }

            .defly-assistant__conservation {
                padding-right: 6.5rem;
            }

            .defly-assistant__conservation-menu {
                display: none;
            }

            .defly-assistant__mobile-actions {
                position: absolute;
                top: .3rem;
                right: .3rem;
                display: flex;
                align-items: center;
                gap: .1rem;
            }

            .defly-assistant__mobile-actions button {
                display: grid;
                width: 1.75rem;
                height: 1.75rem;
                place-items: center;
                border-radius: .4rem;
                color: rgb(107 114 128);
            }

            .defly-assistant__mobile-actions button:hover {
                color: rgb(31 41 55);
                background: rgb(209 213 219);
            }

            .defly-assistant__mobile-actions svg {
                width: .9rem;
                height: .9rem;
            }

            .defly-assistant__mobile-actions .defly-assistant__mobile-danger {
                color: rgb(220 38 38);
            }

            .dark .defly-assistant__mobile-actions button {
                color: rgb(161 161 170);
            }

            .dark .defly-assistant__mobile-actions button:hover {
                color: rgb(244 244 245);
                background: rgb(63 63 70);
            }

            .dark .defly-assistant__mobile-actions .defly-assistant__mobile-danger {
                color: rgb(248 113 113);
            }

            .defly-assistant__composer-meta > span,
            .defly-assistant__attach-button > span {
                display: none;
            }

            .defly-assistant__messages {
                padding: 1.25rem 1rem;
            }

            .defly-assistant__message {
                max-width: 88%;
            }

            .defly-assistant__composer {
                padding: .75rem;
            }

            .defly-assistant__composer-footer {
                justify-content: flex-end;
            }
        }
    </style>
</x-filament-panels::page>
