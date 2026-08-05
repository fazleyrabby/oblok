<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-project-switcher :projects="$projects" :current="$project" :route="'projects.ai-settings.index'" />
        </div>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                    AI Settings for {{ $project->name }}
                </h2>
                <p class="text-xs text-gray-400 mt-1">Configure custom AI providers and model options for this project</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="aiSettingsForm()">
        @if(session('status'))
            <div class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-sm text-gray-300">
                {{ session('status') }}
            </div>
        @endif

        <!-- Configured Providers -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-200 mb-4">Configured AI Providers</h3>
            @if($providers->isEmpty())
                <p class="text-sm text-gray-500">No custom AI providers configured. The system will fallback to default `.env` settings.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-400 border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="py-3 px-4">Name</th>
                                <th class="py-3 px-4">Endpoint</th>
                                <th class="py-3 px-4">Models</th>
                                <th class="py-3 px-4">Timeout</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($providers as $provider)
                                <tr>
                                    <td class="py-4 px-4 font-medium text-gray-200">{{ $provider->name }}</td>
                                    <td class="py-4 px-4 font-mono text-xs">{{ $provider->endpoint }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($provider->models as $model)
                                                <span class="px-2 py-0.5 rounded-full bg-gray-800 text-gray-300 text-[10px] border border-gray-700 font-mono">{{ $model }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">{{ $provider->timeout }}s</td>
                                    <td class="py-4 px-4 text-right">
                                        <form method="POST" action="{{ route('projects.ai-settings.destroy', [$project, $provider]) }}"
                                              onsubmit="return confirm('Are you sure you want to remove this AI provider?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-400 text-xs font-semibold uppercase tracking-wider transition">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Add Provider Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-200">Add custom AI provider</h3>
            <p class="mt-1 text-sm text-gray-400">
                Register a new OpenAI-compatible API endpoint (like Groq, OpenRouter, OpenCode Zen, or your local Llama.cpp server).
            </p>

            <form method="POST" action="{{ route('projects.ai-settings.store', $project) }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="preset" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Preset Template</label>
                        <select id="preset" x-model="preset" @change="applyPreset" class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="custom">-- Custom Endpoint --</option>
                            <option value="groq">Groq (Cloud)</option>
                            <option value="openrouter">OpenRouter (Cloud)</option>
                            <option value="opencode-zen">OpenCode Zen (Cloud)</option>
                            <option value="llama-cpp">Local Llama.cpp (Docker/Homelab)</option>
                        </select>
                    </div>

                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Provider Name</label>
                        <input type="text" name="name" id="name" required x-model="name" placeholder="e.g. Local Llama"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="endpoint" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Endpoint base URL</label>
                        <input type="url" name="endpoint" id="endpoint" required x-model="endpoint" placeholder="e.g. http://localhost:8080/v1"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs" />
                        @error('endpoint')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-gray-500">The endpoint should exclude the path suffix `/chat/completions` (e.g. `https://api.groq.com/openai/v1` or `http://192.168.0.222:8080/v1`).</p>
                    </div>

                    <div>
                        <label for="api_key" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">API Key</label>
                        <input type="password" name="api_key" id="api_key" x-model="apiKey" placeholder="••••••••"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('api_key')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-gray-500" x-text="apiKeyHint"></p>
                    </div>

                    <div>
                        <label for="timeout" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Timeout (seconds)</label>
                        <input type="number" name="timeout" id="timeout" required x-model="timeout" min="5" max="300"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        @error('timeout')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="models" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Available Models (Comma-separated)</label>
                        <input type="text" name="models" id="models" required x-model="models" placeholder="e.g. model-a, model-b"
                               class="w-full bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-xs" />
                        @error('models')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        <p class="mt-1 text-[11px] text-gray-500">Comma-separated list of exact model identifiers to expose in the model selector selector dropdown.</p>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold uppercase tracking-wider transition">
                        Add AI Provider
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function aiSettingsForm() {
            return {
                preset: 'custom',
                name: '',
                endpoint: '',
                apiKey: '',
                timeout: 60,
                models: '',
                apiKeyHint: 'Leave blank if your local provider does not require authentication.',

                applyPreset() {
                    if (this.preset === 'groq') {
                        this.name = 'Groq';
                        this.endpoint = 'https://api.groq.com/openai/v1';
                        this.models = 'llama-3.3-70b-versatile, llama-3.1-8b-instant, mixtral-8x7b-32768, gemma2-9b-it';
                        this.timeout = 60;
                        this.apiKeyHint = 'Enter your Groq Console API key (gsk_...).';
                    } else if (this.preset === 'openrouter') {
                        this.name = 'OpenRouter';
                        this.endpoint = 'https://openrouter.ai/api/v1';
                        this.models = 'google/gemini-2.5-flash, google/gemini-2.5-pro, deepseek/deepseek-chat, meta-llama/llama-3.3-70b-instruct';
                        this.timeout = 60;
                        this.apiKeyHint = 'Enter your OpenRouter API key (sk-or-...).';
                    } else if (this.preset === 'opencode-zen') {
                        this.name = 'OpenCode Zen';
                        this.endpoint = 'https://opencode.ai/zen/v1';
                        this.models = 'opencode-zen';
                        this.timeout = 60;
                        this.apiKeyHint = 'Enter your OpenCode API key.';
                    } else if (this.preset === 'llama-cpp') {
                        this.name = 'Local Llama.cpp';
                        this.endpoint = 'http://192.168.0.222:8080/v1';
                        this.models = 'LFM2.5-2.6B-Q4_K_M.gguf, qwen.gguf';
                        this.timeout = 60;
                        this.apiKeyHint = 'Leave blank (llama.cpp runs locally on your homelab without auth).';
                    } else {
                        this.name = '';
                        this.endpoint = '';
                        this.models = '';
                        this.timeout = 60;
                        this.apiKeyHint = 'Leave blank if your local provider does not require authentication.';
                    }
                }
            };
        }
    </script>
</x-app-layout>
