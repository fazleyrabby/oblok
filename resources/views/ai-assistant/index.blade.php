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

    <div
        x-data="assistant()"
        class="space-y-6"
    >
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider">Conversation</h3>
                <span class="text-xs text-gray-500">Answers use live project context</span>
            </div>

            <div class="space-y-4 max-h-96 overflow-y-auto pr-1" x-ref="messages">
                <template x-if="messages.length === 0">
                    <div class="text-sm text-gray-500 space-y-2">
                        <p>Ask the assistant anything about this project, for example:</p>
                        <ul class="space-y-1">
                            <li>
                                <button type="button" @click="ask('What services are currently unhealthy?')"
                                        class="text-indigo-400 hover:text-indigo-300 text-left">"What services are currently unhealthy?"</button>
                            </li>
                            <li>
                                <button type="button" @click="ask('Summarize recent deployments.')"
                                        class="text-indigo-400 hover:text-indigo-300 text-left">"Summarize recent deployments."</button>
                            </li>
                            <li>
                                <button type="button" @click="ask('What alerts fired in the last day?')"
                                        class="text-indigo-400 hover:text-indigo-300 text-left">"What alerts fired in the last day?"</button>
                            </li>
                        </ul>
                    </div>
                </template>

                <template x-for="(message, index) in messages" :key="index">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1"
                           :class="message.role === 'user' ? 'text-indigo-400' : 'text-emerald-400'">
                            <span x-text="message.role === 'user' ? 'You' : 'Assistant'"></span>
                        </p>
                        <div class="text-sm text-gray-200 bg-gray-950 border border-gray-800 rounded-lg px-4 py-3 whitespace-pre-wrap"
                             x-text="message.content"></div>
                    </div>
                </template>
            </div>

            <form class="mt-4 flex items-start gap-3" @submit.prevent="submit">
                <textarea
                    x-model="draft"
                    rows="2"
                    placeholder="Ask about this project…"
                    class="flex-1 bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                ></textarea>
                <button
                    type="submit"
                    :disabled="loading || draft.trim() === ''"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition"
                >
                    <span x-show="!loading">Send</span>
                    <span x-show="loading">Asking…</span>
                </button>
            </form>

            <p x-show="error" x-text="error" class="mt-2 text-xs text-red-400"></p>
        </div>
    </div>

    <script>
        function assistant() {
            return {
                messages: [],
                draft: '',
                loading: false,
                error: null,

                ask(question) {
                    this.draft = question;
                    this.submit();
                },

                async submit() {
                    const question = this.draft.trim();
                    if (question === '' || this.loading) {
                        return;
                    }

                    this.messages.push({ role: 'user', content: question });
                    this.draft = '';
                    this.error = null;
                    this.loading = true;

                    try {
                        const response = await fetch(@json(route('api.v1.projects.ai.assistant', $project)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': @json(csrf_token()),
                            },
                            body: JSON.stringify({ message: question }),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload.message ?? 'The request failed.');
                        }

                        this.messages.push({ role: 'assistant', content: payload.data.answer });
                    } catch (e) {
                        this.error = e.message || 'Something went wrong.';
                        this.messages.push({ role: 'assistant', content: 'Sorry, I could not answer that right now.' });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            const el = this.$refs.messages;
                            if (el) {
                                el.scrollTop = el.scrollHeight;
                            }
                        });
                    }
                },
            };
        }
    </script>
</x-app-layout>
