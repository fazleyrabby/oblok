<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            New Notification Channel for {{ $project->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <form method="POST" action="{{ route('projects.notification-channels.store', $project) }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Channel Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g. Team Slack Alerts" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('Channel Type')" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Enums\NotificationChannelType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />
                    </div>

                    <div id="config-fields">
                        <div data-config="slack">
                            <x-input-label for="config_webhook_url" :value="__('Slack Incoming Webhook URL')" />
                            <x-text-input id="config_webhook_url" name="config[webhook_url]" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('config.webhook_url')" placeholder="https://hooks.slack.com/services/..." />
                            <x-input-error class="mt-2" :messages="$errors->get('config.webhook_url')" />
                        </div>

                        <div data-config="webhook" class="space-y-4">
                            <div>
                                <x-input-label for="config_url" :value="__('Webhook URL')" />
                                <x-text-input id="config_url" name="config[url]" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('config.url')" placeholder="https://your-endpoint.example.com/hooks/oblok" />
                                <x-input-error class="mt-2" :messages="$errors->get('config.url')" />
                            </div>
                            <div>
                                <x-input-label for="config_secret" :value="__('HMAC Secret (Optional)')" />
                                <x-text-input id="config_secret" name="config[secret]" type="password" class="mt-1 block w-full font-mono text-sm" :value="old('config.secret')" placeholder="Shared secret for X-oblok-Signature" />
                                <x-input-error class="mt-2" :messages="$errors->get('config.secret')" />
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="enabled" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-600 focus:ring-indigo-500" @checked(old('enabled', true))>
                        <span class="text-sm text-gray-300">Enable channel</span>
                    </label>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Create Channel') }}</x-primary-button>
                        <a href="{{ route('projects.notification-channels.index', $project) }}" class="text-sm font-medium text-gray-400 hover:text-gray-200">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const typeSelect = document.getElementById('type');
        const toggleConfig = () => {
            document.querySelectorAll('[data-config]').forEach(el => el.style.display = 'none');
            const target = document.querySelector(`[data-config="${typeSelect.value}"]`);
            if (target) target.style.display = 'block';
        };
        typeSelect.addEventListener('change', toggleConfig);
        toggleConfig();
    </script>
</x-app-layout>
