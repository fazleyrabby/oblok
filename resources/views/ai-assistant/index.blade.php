<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-project-switcher :projects="$projects" :current="$project" :route="'projects.ai-assistant'" />
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    AI Assistant for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Ask questions about your services, incidents, deployments, alerts, and logs</p>
            </div>
        </div>
    </x-slot>

    <div x-data="assistant()" class="mx-auto max-w-3xl space-y-4">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-600/20 text-indigo-300 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-100">oblok Assistant</p>
                        <p class="text-xs text-gray-500 flex items-center gap-1.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Using live project context
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($availableModels->isNotEmpty())
                        <select 
                            x-model="selectedModel" 
                            @change="changeModel($event)" 
                            class="bg-gray-950 border border-gray-850 rounded-lg px-2 py-1 text-xs text-gray-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 font-mono max-w-[12rem] truncate"
                        >
                            @foreach($availableModels as $modelOption)
                                <option value="{{ $modelOption['model'] }}" data-provider-id="{{ $modelOption['provider_id'] }}">
                                    {{ $modelOption['label'] }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <button
                        type="button"
                        @click="clear()"
                        class="text-xs text-gray-500 hover:text-gray-300 transition"
                    >Clear</button>
                </div>
            </div>

            <div class="px-5 py-5">
                <div class="max-h-[28rem] overflow-y-auto pr-2 space-y-5" x-ref="messages">
                    <template x-if="messages.length === 0 && !loading">
                        <div class="text-center py-10">
                            <div class="mx-auto w-12 h-12 rounded-2xl bg-gray-800 text-indigo-300 flex items-center justify-center mb-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-400 mb-1">Ask anything about this project</p>
                            <p class="text-xs text-gray-600 mb-5">Try one of these:</p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <button type="button" @click="ask('What services are currently unhealthy?')"
                                        class="text-xs px-3 py-1.5 rounded-full border border-gray-700 bg-gray-950 text-gray-300 hover:border-indigo-500 hover:text-indigo-300 transition">
                                    What services are unhealthy?
                                </button>
                                <button type="button" @click="ask('Summarize recent deployments.')"
                                        class="text-xs px-3 py-1.5 rounded-full border border-gray-700 bg-gray-950 text-gray-300 hover:border-indigo-500 hover:text-indigo-300 transition">
                                    Summarize recent deployments
                                </button>
                                <button type="button" @click="ask('What alerts fired in the last day?')"
                                        class="text-xs px-3 py-1.5 rounded-full border border-gray-700 bg-gray-950 text-gray-300 hover:border-indigo-500 hover:text-indigo-300 transition">
                                    What alerts fired recently?
                                </button>
                            </div>
                        </div>
                    </template>

                    <template x-for="(message, index) in messages" :key="index">
                        <div x-show="message.role === 'user' || message.content !== ''"
                             class="flex flex-col"
                             :class="message.role === 'user' ? 'items-end' : 'items-start'">
                            <p class="text-[11px] font-semibold uppercase tracking-wider mb-1 px-1"
                               :class="message.role === 'user' ? 'text-indigo-400' : 'text-emerald-400'"
                               x-text="message.role === 'user' ? 'You' : 'Assistant'"></p>
                            <div class="max-w-[85%] px-4 py-2.5 text-sm leading-relaxed whitespace-pre-wrap break-words"
                                 :class="message.role === 'user'
                                     ? 'bg-indigo-600 text-white rounded-2xl rounded-br-sm'
                                     : 'bg-gray-950 border border-gray-800 text-gray-200 rounded-2xl rounded-bl-sm'"
                                 x-text="message.content"></div>
                        </div>
                    </template>

                    <div x-show="loading && (messages.length === 0 || messages[messages.length - 1].role !== 'assistant' || messages[messages.length - 1].content === '')" class="flex flex-col items-start">
                        <p class="text-[11px] font-semibold uppercase tracking-wider mb-1 px-1 text-emerald-400">Assistant</p>
                        <div class="bg-gray-950 border border-gray-800 rounded-2xl rounded-bl-sm px-4 py-3 flex items-center gap-2.5">
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="text-xs text-gray-500 ml-1">Thinking…</span>
                        </div>
                    </div>
                </div>
            </div>

            <form class="px-5 py-4 border-t border-gray-800" @submit.prevent="submit">
                <div class="flex items-end gap-3">
                    <textarea
                        x-model="draft"
                        rows="1"
                        x-ref="input"
                        placeholder="Ask about this project…"
                        @keydown.enter.exact.prevent="submit"
                        class="flex-1 bg-gray-950 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-100 placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="loading || draft.trim() === ''"
                        class="shrink-0 h-10 w-10 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white flex items-center justify-center transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!loading">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="loading">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                    </button>
                </div>
                <p x-show="error" x-text="error"
                   class="mt-2 text-xs text-red-400 flex items-center gap-1.5">
                </p>
                <p class="mt-2 text-[11px] text-gray-600">Enter to send · Shift+Enter for a new line</p>
            </form>
        </div>
    </div>

    <style>
        .typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background-color: #46e1d5;
            display: inline-block;
            animation: typing-bounce 1.2s infinite ease-in-out;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-dot:nth-child(3) { animation-delay: 0.3s; }
        @keyframes typing-bounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-4px); opacity: 1; }
        }
    </style>

    <script>
        function assistant() {
            return {
                messages: @json($messages->map(fn ($message) => ['role' => $message->role, 'content' => $message->content])),
                draft: '',
                loading: false,
                error: null,
                selectedModel: @json($selectedModel),
                selectedProviderId: @json($selectedProviderId),

                async changeModel(event) {
                    const option = event.target.options[event.target.selectedIndex];
                    const providerId = option.getAttribute('data-provider-id') || null;
                    const model = this.selectedModel;
                    
                    try {
                        await fetch(@json(route('projects.ai-assistant.select-model', $project)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({
                                provider_id: providerId,
                                model: model
                            })
                        });
                    } catch (e) {
                        // Failed to save model preference
                    }
                },

                ask(question) {
                    this.draft = question;
                    this.submit();
                },

                async clear() {
                    this.messages = [];
                    this.error = null;

                    try {
                        await fetch(@json(route('projects.ai-assistant.clear', $project)), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                        });
                    } catch (e) {
                        // History is best-effort; the UI is already cleared.
                    }
                },

                async submit() {
                    const question = this.draft.trim();
                    if (question === '' || this.loading) {
                        return;
                    }

                    this.messages.push({ role: 'user', content: question });
                    this.messages.push({ role: 'assistant', content: '' });
                    this.draft = '';
                    this.error = null;
                    this.loading = true;

                    this.scrollToBottom();

                    try {
                        const response = await fetch(@json(route('projects.ai-assistant.ask', $project)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'text/event-stream',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({ message: question }),
                        });

                        if (!response.ok || !response.body) {
                            throw new Error('The request failed.');
                        }

                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        let buffer = '';

                        while (true) {
                            const { value, done } = await reader.read();
                            if (done) break;
                            buffer += decoder.decode(value, { stream: true });

                            const events = buffer.split('\n\n');
                            buffer = events.pop() || '';

                            for (const event of events) {
                                const [eventLine, ...dataLines] = event.split('\n');
                                const data = dataLines
                                    .filter(line => line.startsWith('data:'))
                                    .map(line => line.replace(/^data:\s*/, ''))
                                    .join('');

                                if (!data) continue;

                                let payload;
                                try {
                                    payload = JSON.parse(data);
                                } catch (e) {
                                    continue;
                                }

                                if (eventLine.includes('token')) {
                                    this.messages[this.messages.length - 1].content += payload.answer ?? '';
                                    this.$nextTick(() => this.scrollToBottom());
                                } else if (eventLine.includes('error')) {
                                    throw new Error(payload.message ?? 'The AI assistant could not be reached.');
                                } else if (eventLine.includes('done')) {
                                    break;
                                }
                            }
                        }
                    } catch (e) {
                        this.error = e.message || 'Something went wrong.';
                        const last = this.messages[this.messages.length - 1];
                        if (last && last.role === 'assistant' && last.content === '') {
                            last.content = 'Sorry, I could not answer that right now.';
                        }
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => this.scrollToBottom());
                    }
                },

                scrollToBottom() {
                    const el = this.$refs.messages;
                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                },
            };
        }
    </script>
</x-app-layout>
