<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-100 leading-tight">
            Edit Notification Channel: {{ $notificationChannel->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-sm">
                <form method="POST" action="{{ route('projects.notification-channels.update', [$project, $notificationChannel]) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('Channel Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $notificationChannel->name)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('Channel Type')" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-lg border-gray-700 bg-gray-950 text-white text-sm p-2.5 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Enums\NotificationChannelType::cases() as $type)
                                <option value="{{ $type->value }}" @selected(old('type', $notificationChannel->type->value) === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />
                    </div>

                    <div id="config-fields">
                        <div data-config="slack">
                            <x-input-label for="config_webhook_url" :value="__('Slack Incoming Webhook URL')" />
                            <x-text-input id="config_webhook_url" name="config[webhook_url]" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('config.webhook_url', $notificationChannel->encrypted_config['webhook_url'] ?? '')" placeholder="https://hooks.slack.com/services/..." />
                            <x-input-error class="mt-2" :messages="$errors->get('config.webhook_url')" />
                        </div>

                        <div data-config="webhook" class="space-y-4">
                            <div>
                                <x-input-label for="config_url" :value="__('Webhook URL')" />
                                <x-text-input id="config_url" name="config[url]" type="url" class="mt-1 block w-full font-mono text-sm" :value="old('config.url', $notificationChannel->encrypted_config['url'] ?? '')" placeholder="https://your-endpoint.example.com/hooks/atlas" />
                                <x-input-error class="mt-2" :messages="$errors->get('config.url')" />
                            </div>
                            <div>
                                <x-input-label for="config_secret" :value="__('HMAC Secret (Leave blank to keep unchanged)')" />
                                <x-text-input id="config_secret" name="config[secret]" type="password" class="mt-1 block w-full font-mono text-sm" :value="old('config.secret')" placeholder="Shared secret for X-Atlas-Signature" />
                                <x-input-error class="mt-2" :messages="$errors->get('config.secret')" />
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="enabled" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-600 focus:ring-indigo-500" @checked(old('enabled', $notificationChannel->enabled))>
                        <span class="text-sm text-gray-300">Enable channel</span>
                    </label>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>{{ __('Update Channel') }}</x-primary-button>
                        <a href="{{ route('projects.notification-channels.index', $project) }}" class="text-sm font-medium text-gray-400 hover:text-gray-200">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-gray-900 border border-red-900/50 rounded-xl p-6 shadow-sm">
                <h3 class="text-md font-semibold text-red-400 mb-2">Delete Notification Channel</h3>
                <p class="text-xs text-gray-400 mb-4">Alert rules attached to this channel will stop delivering notifications through it.</p>
                <form method="POST" action="{{ route('projects.notification-channels.destroy', [$project, $notificationChannel]) }}" onsubmit="return confirm('Are you sure you want to delete this notification channel?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete Channel') }}</x-danger-button>
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
