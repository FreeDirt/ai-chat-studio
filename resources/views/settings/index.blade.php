@extends('layouts.app')
@section('title', 'Settings — AI Chat Studio')

@section('content')
<div class="app-shell panel-collapsed">

    @include('layouts._sidebar')

    <!-- ===== SETTINGS MAIN ===== -->
    <main class="chat-main" style="overflow-y:auto;">
        <!-- Top Sticky Header -->
        <div class="settings-top-header">
            <div class="settings-header-left">
                <div class="settings-title-wrap">
                    <span class="settings-title-icon">⚙️</span>
                    <div>
                        <h1 class="settings-main-title">Settings & Configuration</h1>
                        <p class="settings-main-subtitle">Manage AI providers, model parameters, display preferences, and workspace identity</p>
                    </div>
                </div>
            </div>
            <div class="settings-header-actions">
                <div id="conn-status" style="display:none" class="provider-badge conn-status-badge">
                    <span class="dot" id="conn-dot" style="background:var(--warning)"></span>
                    <span id="conn-text">Not tested</span>
                </div>
                <button type="button" class="btn btn-ghost btn-sm-action" id="btn-test-conn">
                    <span>🔬</span> Test Connection
                </button>
                <button type="button" class="btn btn-primary btn-sm-action" id="btn-save-settings">
                    <span>💾</span> Save Changes
                </button>
            </div>
        </div>

        <div class="settings-content-wrapper">

            <!-- Category Navigation Subnav -->
            <div class="settings-subnav">
                <button type="button" class="settings-nav-item active" data-tab="tab-providers">
                    <span class="nav-icon">🔌</span>
                    <span>AI Providers</span>
                </button>
                <button type="button" class="settings-nav-item" data-tab="tab-parameters">
                    <span class="nav-icon">🎛️</span>
                    <span>Model Parameters</span>
                </button>
                <button type="button" class="settings-nav-item" data-tab="tab-appearance">
                    <span class="nav-icon">🌓</span>
                    <span>Appearance</span>
                </button>
                <button type="button" class="settings-nav-item" data-tab="tab-workspace">
                    <span class="nav-icon">🏢</span>
                    <span>Workspace & Branding</span>
                </button>
            </div>

            <!-- ===== TAB 1: AI PROVIDERS ===== -->
            <div class="settings-tab-pane active" id="tab-providers">
                
                @php
                    $providers = [
                        'openai'     => ['icon' => '🧠', 'name' => 'OpenAI',      'badge' => 'GPT-4o, o3-mini', 'tag' => 'Industry Standard'],
                        'openrouter' => ['icon' => '🔀', 'name' => 'OpenRouter',  'badge' => '100+ Models',     'tag' => 'Multi-Provider'],
                        'claude'     => ['icon' => '🤖', 'name' => 'Claude',      'badge' => 'Anthropic AI',    'tag' => 'Reasoning & Coding'],
                        'gemini'     => ['icon' => '💎', 'name' => 'Gemini',      'badge' => 'Google 2.5 Flash','tag' => 'Fast & Multimodal'],
                        'ollama'     => ['icon' => '🦙', 'name' => 'Ollama',      'badge' => 'Local & Offline', 'tag' => '100% Private'],
                    ];
                    $activeProvider = $settings['ai_provider'] ?? 'openai';

                    $activeKeySet = match($activeProvider) {
                        'openai'     => !empty($settings['openai_api_key']),
                        'openrouter' => !empty($settings['openrouter_api_key']),
                        'claude'     => !empty($settings['claude_api_key']),
                        'gemini'     => !empty($settings['gemini_api_key']),
                        'ollama'     => !empty($settings['ollama_url']),
                        default      => false
                    };
                    $activeModelName = $settings[$activeProvider . '_model'] ?? 'Default Model';
                @endphp

                <!-- Active Engine Status Banner -->
                <div class="active-engine-banner" style="margin-bottom:20px;padding:16px 20px;border-radius:var(--radius-xl);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;background:{{ $activeKeySet ? 'linear-gradient(135deg, rgba(34,197,94,0.1), rgba(15,23,42,0.6))' : 'linear-gradient(135deg, rgba(245,158,11,0.1), rgba(15,23,42,0.6))' }};border:1px solid {{ $activeKeySet ? 'rgba(34,197,94,0.35)' : 'rgba(245,158,11,0.35)' }};">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:44px;height:44px;border-radius:var(--radius-lg);background:{{ $activeKeySet ? 'rgba(34,197,94,0.2)' : 'rgba(245,158,11,0.2)' }};display:flex;align-items:center;justify-content:center;font-size:22px;border:1px solid {{ $activeKeySet ? 'rgba(34,197,94,0.4)' : 'rgba(245,158,11,0.4)' }}">
                            {{ $providers[$activeProvider]['icon'] ?? '🤖' }}
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                <span style="font-size:15px;font-weight:800;color:var(--text-primary)">
                                    Active AI Engine: {{ $providers[$activeProvider]['name'] ?? ucfirst($activeProvider) }}
                                </span>
                                <span style="font-size:11.5px;font-weight:700;padding:2px 10px;border-radius:99px;background:var(--bg-elevated);border:1px solid var(--border);color:var(--accent-light);font-family:var(--font-mono)">
                                    {{ $activeModelName }}
                                </span>
                            </div>
                            <div style="font-size:12px;margin-top:4px;color:{{ $activeKeySet ? 'var(--success)' : 'var(--warning)' }};font-weight:600;display:flex;align-items:center;gap:6px">
                                <span style="width:7px;height:7px;border-radius:50%;background:{{ $activeKeySet ? 'var(--success)' : 'var(--warning)' }};display:inline-block"></span>
                                <span>{{ $activeKeySet ? 'API Key Configured & Ready for Chat Generation' : '⚠️ No API Key saved for ' . ($providers[$activeProvider]['name'] ?? $activeProvider) . ' — Enter your key below and click Save' }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('btn-test-conn').click()" style="font-size:12px;padding:6px 14px;font-weight:600">
                        <span>🔬</span> Quick Ping Test
                    </button>
                </div>

                <!-- Provider Selector Grid -->
                <div class="settings-section-card">
                    <div class="section-card-header">
                        <div>
                            <div class="section-title">Select Active AI Provider</div>
                            <div class="section-desc">Choose which AI backend powers new chat completions and responses</div>
                        </div>
                    </div>

                    <div class="provider-selection-grid" id="provider-tabs">
                        @foreach($providers as $key => $p)
                            @php
                                $isKeySet = match($key) {
                                    'openai'     => !empty($settings['openai_api_key']),
                                    'openrouter' => !empty($settings['openrouter_api_key']),
                                    'claude'     => !empty($settings['claude_api_key']),
                                    'gemini'     => !empty($settings['gemini_api_key']),
                                    'ollama'     => !empty($settings['ollama_url']),
                                    default      => false
                                };
                                $pModel = $settings[$key . '_model'] ?? '';
                            @endphp
                            <div class="provider-card {{ $activeProvider === $key ? 'active' : '' }}" data-provider="{{ $key }}">
                                <div class="provider-card-top">
                                    <span class="provider-card-icon">{{ $p['icon'] }}</span>
                                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
                                        <span class="provider-tag-pill">{{ $p['tag'] }}</span>
                                        @if($isKeySet)
                                            <span style="font-size:10px;font-weight:700;color:var(--success);background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 7px;border-radius:99px;display:inline-flex;align-items:center;gap:4px">
                                                <span style="width:5px;height:5px;border-radius:50%;background:var(--success)"></span> Set
                                            </span>
                                        @else
                                            <span style="font-size:10px;font-weight:700;color:var(--warning);background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 7px;border-radius:99px;display:inline-flex;align-items:center;gap:4px">
                                                <span style="width:5px;height:5px;border-radius:50%;background:var(--warning)"></span> No Key
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="provider-card-name">{{ $p['name'] }}</div>
                                <div class="provider-card-badge" style="font-family:var(--font-mono);font-size:11px">{{ $pModel ?: $p['badge'] }}</div>
                                <div class="provider-active-indicator">
                                    <span class="indicator-dot"></span>
                                    <span>Active Default</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Provider Credentials & Models -->
                
                <!-- 1. OpenAI Panel -->
                <div class="settings-section-card provider-panel" id="panel-openai" style="{{ $activeProvider !== 'openai' ? 'display:none' : '' }}">
                    <div class="section-card-header">
                        <div class="provider-title-flex">
                            <span class="provider-logo-bubble">🧠</span>
                            <div>
                                <div class="section-title">OpenAI Configuration</div>
                                <div class="section-desc">Connect using your API key from platform.openai.com</div>
                            </div>
                        </div>
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="external-portal-link">
                            API Keys Portal ↗
                        </a>
                    </div>

                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label class="form-label" style="margin-bottom:0">OpenAI Secret API Key</label>
                            @if(!empty($settings['openai_api_key']))
                                <span style="font-size:11px;color:var(--success);font-weight:700;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:99px">
                                    ● Key Saved in DB
                                </span>
                            @else
                                <span style="font-size:11px;color:var(--warning);font-weight:700;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:99px">
                                    ▲ Key Missing
                                </span>
                            @endif
                        </div>
                        <div class="key-input-wrap">
                            <input type="password" id="openai_api_key" class="form-input" value="{{ $settings['openai_api_key'] ?? '' }}" placeholder="sk-proj-...">
                            <button type="button" class="key-toggle-btn" data-target="openai_api_key" title="Toggle visibility">👁</button>
                        </div>
                        <div class="field-hint">Never shared. Kept encrypted in your MariaDB database.</div>
                    </div>

                    @include('settings._model_field', [
                        'fieldId'     => 'openai_model',
                        'currentVal'  => $settings['openai_model'] ?? 'gpt-4o-mini',
                        'staticOpts'  => ['gpt-4o','gpt-4o-mini','gpt-4-turbo','gpt-4','o3-mini','o1','gpt-3.5-turbo'],
                        'provider'    => 'openai',
                    ])
                </div>

                <!-- 2. OpenRouter Panel -->
                <div class="settings-section-card provider-panel" id="panel-openrouter" style="{{ $activeProvider !== 'openrouter' ? 'display:none' : '' }}">
                    <div class="section-card-header">
                        <div class="provider-title-flex">
                            <span class="provider-logo-bubble">🔀</span>
                            <div>
                                <div class="section-title">OpenRouter Configuration</div>
                                <div class="section-desc">Access 100+ models (Claude, Llama 3.3, DeepSeek-R1, Mistral) with a single key</div>
                            </div>
                        </div>
                        <a href="https://openrouter.ai/keys" target="_blank" class="external-portal-link">
                            OpenRouter Keys ↗
                        </a>
                    </div>

                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label class="form-label" style="margin-bottom:0">OpenRouter API Key</label>
                            @if(!empty($settings['openrouter_api_key']))
                                <span style="font-size:11px;color:var(--success);font-weight:700;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:99px">
                                    ● Key Saved in DB
                                </span>
                            @else
                                <span style="font-size:11px;color:var(--warning);font-weight:700;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:99px">
                                    ▲ Key Missing
                                </span>
                            @endif
                        </div>
                        <div class="key-input-wrap">
                            <input type="password" id="openrouter_api_key" class="form-input" value="{{ $settings['openrouter_api_key'] ?? '' }}" placeholder="sk-or-v1-...">
                            <button type="button" class="key-toggle-btn" data-target="openrouter_api_key" title="Toggle visibility">👁</button>
                        </div>
                    </div>

                    @include('settings._model_field', [
                        'fieldId'     => 'openrouter_model',
                        'currentVal'  => $settings['openrouter_model'] ?? 'openai/gpt-4o-mini',
                        'staticOpts'  => ['openai/gpt-4o','openai/gpt-4o-mini','anthropic/claude-3.5-sonnet','google/gemini-2.0-flash','deepseek/deepseek-r1','meta-llama/llama-3.3-70b-instruct','mistralai/mistral-large'],
                        'provider'    => 'openrouter',
                    ])
                </div>

                <!-- 3. Claude Panel -->
                <div class="settings-section-card provider-panel" id="panel-claude" style="{{ $activeProvider !== 'claude' ? 'display:none' : '' }}">
                    <div class="section-card-header">
                        <div class="provider-title-flex">
                            <span class="provider-logo-bubble">🤖</span>
                            <div>
                                <div class="section-title">Anthropic Claude Configuration</div>
                                <div class="section-desc">Claude 3.5 Sonnet, Haiku, and Opus models</div>
                            </div>
                        </div>
                        <a href="https://console.anthropic.com/settings/keys" target="_blank" class="external-portal-link">
                            Anthropic Console ↗
                        </a>
                    </div>

                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label class="form-label" style="margin-bottom:0">Anthropic API Key</label>
                            @if(!empty($settings['claude_api_key']))
                                <span style="font-size:11px;color:var(--success);font-weight:700;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:99px">
                                    ● Key Saved in DB
                                </span>
                            @else
                                <span style="font-size:11px;color:var(--warning);font-weight:700;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:99px">
                                    ▲ Key Missing
                                </span>
                            @endif
                        </div>
                        <div class="key-input-wrap">
                            <input type="password" id="claude_api_key" class="form-input" value="{{ $settings['claude_api_key'] ?? '' }}" placeholder="sk-ant-api03-...">
                            <button type="button" class="key-toggle-btn" data-target="claude_api_key" title="Toggle visibility">👁</button>
                        </div>
                    </div>

                    @include('settings._model_field', [
                        'fieldId'     => 'claude_model',
                        'currentVal'  => $settings['claude_model'] ?? 'claude-3-5-sonnet-20241022',
                        'staticOpts'  => ['claude-3-5-sonnet-20241022','claude-3-5-haiku-20241022','claude-3-opus-20240229'],
                        'provider'    => 'claude',
                    ])
                </div>

                <!-- 4. Gemini Panel -->
                <div class="settings-section-card provider-panel" id="panel-gemini" style="{{ $activeProvider !== 'gemini' ? 'display:none' : '' }}">
                    <div class="section-card-header">
                        <div class="provider-title-flex">
                            <span class="provider-logo-bubble">💎</span>
                            <div>
                                <div class="section-title">Google Gemini Configuration</div>
                                <div class="section-desc">Gemini 2.5 Flash, 2.0 Flash, and 1.5 Pro via Google AI Studio</div>
                            </div>
                        </div>
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="external-portal-link">
                            Google AI Studio ↗
                        </a>
                    </div>

                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label class="form-label" style="margin-bottom:0">Google Gemini API Key</label>
                            @if(!empty($settings['gemini_api_key']))
                                <span style="font-size:11px;color:var(--success);font-weight:700;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:99px">
                                    ● Key Saved in DB
                                </span>
                            @else
                                <span style="font-size:11px;color:var(--warning);font-weight:700;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:99px">
                                    ▲ Key Missing
                                </span>
                            @endif
                        </div>
                        <div class="key-input-wrap">
                            <input type="password" id="gemini_api_key" class="form-input" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIzaSy...">
                            <button type="button" class="key-toggle-btn" data-target="gemini_api_key" title="Toggle visibility">👁</button>
                        </div>
                    </div>

                    @include('settings._model_field', [
                        'fieldId'     => 'gemini_model',
                        'currentVal'  => $settings['gemini_model'] ?? 'gemini-2.0-flash',
                        'staticOpts'  => ['gemini-2.5-pro','gemini-2.5-flash','gemini-2.0-flash','gemini-2.0-flash-lite','gemini-1.5-pro','gemini-1.5-flash'],
                        'provider'    => 'gemini',
                    ])
                </div>

                <!-- 5. Ollama Panel -->
                <div class="settings-section-card provider-panel" id="panel-ollama" style="{{ $activeProvider !== 'ollama' ? 'display:none' : '' }}">
                    <div class="section-card-header">
                        <div class="provider-title-flex">
                            <span class="provider-logo-bubble">🦙</span>
                            <div>
                                <div class="section-title">Ollama (Local LLMs)</div>
                                <div class="section-desc">Run local models completely private and offline</div>
                            </div>
                        </div>
                        <div id="ollama-online-badge" class="provider-badge" style="{{ $ollamaOnline ? '' : 'display:none' }}">
                            <span class="dot" style="background:var(--success)"></span> Online
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                            <label class="form-label" style="margin-bottom:0">Ollama Host URL</label>
                            @if(!empty($settings['ollama_url']))
                                <span style="font-size:11px;color:var(--success);font-weight:700;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:99px">
                                    ● Endpoint Set
                                </span>
                            @else
                                <span style="font-size:11px;color:var(--warning);font-weight:700;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);padding:2px 8px;border-radius:99px">
                                    ▲ URL Not Set
                                </span>
                            @endif
                        </div>
                        <input type="text" id="ollama_url" class="form-input" value="{{ $settings['ollama_url'] ?? 'http://host.docker.internal:11434' }}" placeholder="http://host.docker.internal:11434">
                        <div class="field-hint">From Docker on macOS/Windows use <code>http://host.docker.internal:11434</code></div>
                    </div>

                    @include('settings._model_field', [
                        'fieldId'     => 'ollama_model',
                        'currentVal'  => $settings['ollama_model'] ?? 'llama3',
                        'staticOpts'  => !empty($ollamaModels) ? $ollamaModels : ['llama3','llama3.1','llama3.2','mistral','phi3','gemma2','deepseek-r1'],
                        'provider'    => 'ollama',
                    ])
                </div>

            </div>

            <!-- ===== TAB 2: MODEL PARAMETERS ===== -->
            <div class="settings-tab-pane" id="tab-parameters">
                <div class="settings-section-card">
                    <div class="section-card-header">
                        <div>
                            <div class="section-title">Global Model Parameters</div>
                            <div class="section-desc">Default inference behavior applied across all conversations</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Default System Prompt
                            <span class="label-muted">(Can be overridden by custom Personas)</span>
                        </label>
                        <textarea id="system_prompt" class="form-textarea" rows="4" placeholder="You are a helpful, expert AI assistant.">{{ $settings['system_prompt'] ?? 'You are a helpful assistant.' }}</textarea>
                    </div>

                    <div class="param-grid-2col">
                        <!-- Temperature Slider -->
                        <div class="param-control-card">
                            <div class="param-header-flex">
                                <label class="form-label" style="margin-bottom:0">Temperature</label>
                                <span id="temp-val" class="param-val-badge">{{ number_format((float)($settings['temperature'] ?? 0.7), 2) }}</span>
                            </div>
                            <input type="range" id="temperature" min="0" max="2" step="0.05" value="{{ $settings['temperature'] ?? '0.7' }}" class="param-range-slider">
                            <div class="range-labels-flex">
                                <span>🎯 0.0 Precise</span>
                                <span>⚖️ 0.7 Balanced</span>
                                <span>🎨 2.0 Creative</span>
                            </div>
                            <div class="preset-pills-row">
                                <button type="button" class="btn-param-preset" data-target="temperature" data-val="0.2">Precise (0.2)</button>
                                <button type="button" class="btn-param-preset" data-target="temperature" data-val="0.7">Default (0.7)</button>
                                <button type="button" class="btn-param-preset" data-target="temperature" data-val="1.2">Creative (1.2)</button>
                            </div>
                        </div>

                        <!-- Max Tokens -->
                        <div class="param-control-card">
                            <div class="param-header-flex">
                                <label class="form-label" style="margin-bottom:0">Max Generation Tokens</label>
                                <span class="param-val-badge" id="token-label">{{ $settings['max_tokens'] ?? '2048' }}</span>
                            </div>
                            <input type="number" id="max_tokens" class="form-input" value="{{ $settings['max_tokens'] ?? '2048' }}" min="128" max="32768" step="128" placeholder="2048">
                            <div class="field-hint" style="margin-top:6px">Maximum response length per message turn.</div>
                            <div class="preset-pills-row">
                                <button type="button" class="btn-param-preset" data-target="max_tokens" data-val="1024">1K</button>
                                <button type="button" class="btn-param-preset" data-target="max_tokens" data-val="2048">2K</button>
                                <button type="button" class="btn-param-preset" data-target="max_tokens" data-val="4096">4K</button>
                                <button type="button" class="btn-param-preset" data-target="max_tokens" data-val="8192">8K</button>
                                <button type="button" class="btn-param-preset" data-target="max_tokens" data-val="16384">16K</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB 3: DISPLAY & APPEARANCE ===== -->
            <div class="settings-tab-pane" id="tab-appearance">
                <div class="settings-section-card">
                    <div class="section-card-header">
                        <div>
                            <div class="section-title">Personal Display Mode</div>
                            <div class="section-desc">Choose your preferred appearance for this browser and device</div>
                        </div>
                    </div>

                    <div class="display-mode-grid">
                        <div class="display-mode-card" data-mode="dark">
                            <div class="mode-preview-box mode-preview-dark">
                                <div class="preview-mini-sidebar"></div>
                                <div class="preview-mini-content">
                                    <div class="preview-mini-line"></div>
                                    <div class="preview-mini-bubble user"></div>
                                    <div class="preview-mini-bubble ai"></div>
                                </div>
                            </div>
                            <div class="mode-meta">
                                <div class="mode-name">🌙 Dark Obsidian</div>
                                <div class="mode-desc">High-contrast dark theme optimized for eye comfort</div>
                            </div>
                        </div>

                        <div class="display-mode-card" data-mode="light">
                            <div class="mode-preview-box mode-preview-light">
                                <div class="preview-mini-sidebar"></div>
                                <div class="preview-mini-content">
                                    <div class="preview-mini-line"></div>
                                    <div class="preview-mini-bubble user"></div>
                                    <div class="preview-mini-bubble ai"></div>
                                </div>
                            </div>
                            <div class="mode-meta">
                                <div class="mode-name">☀️ Crisp Light</div>
                                <div class="mode-desc">Clean bright interface for daylight environments</div>
                            </div>
                        </div>

                        <div class="display-mode-card" data-mode="system">
                            <div class="mode-preview-box mode-preview-system">
                                <div class="preview-half-dark"></div>
                                <div class="preview-half-light"></div>
                            </div>
                            <div class="mode-meta">
                                <div class="mode-name">💻 System Sync</div>
                                <div class="mode-desc">Automatically matches your operating system preference</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== TAB 4: WORKSPACE & BRANDING ===== -->
            <div class="settings-tab-pane" id="tab-workspace">
                @php
                    $appName = \App\Models\Setting::get('app_name', 'AI Chat Studio');
                    $primaryColor = \App\Models\Setting::get('app_primary_color', '#6c63ff');
                @endphp

                <div class="settings-section-card branding-hub-card">
                    <div class="branding-hub-flex">
                        <div class="branding-hub-info">
                            <div class="branding-pill-tag">✨ Centralized Workspace Identity</div>
                            <h2 class="branding-hub-title">{{ $appName }}</h2>
                            <p class="branding-hub-desc">
                                Organization branding, custom logos, home welcome banners, and default team accent colors are managed in the <strong>Workspace Branding Studio</strong>.
                            </p>
                            <div class="branding-preview-chips">
                                <span class="preview-chip">
                                    <span class="chip-dot" style="background: {{ $primaryColor }}"></span>
                                    <span>Accent: {{ strtoupper($primaryColor) }}</span>
                                </span>
                                <span class="preview-chip">
                                    <span>👑 Admin Protected</span>
                                </span>
                            </div>
                        </div>
                        <div class="branding-hub-action-box">
                            @if(auth()->user() && auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.branding.index') }}" class="btn btn-primary btn-branding-launch">
                                    <span>🎨 Open Branding Studio</span>
                                    <span>→</span>
                                </a>
                                <div class="branding-role-text">You have Super Admin access</div>
                            @else
                                <div class="branding-locked-badge">
                                    <span>🔒 Managed by Workspace Admin</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User Management Quick Portal -->
                @if(auth()->user() && auth()->user()->isSuperAdmin())
                <div class="settings-section-card" style="margin-top:16px">
                    <div class="section-card-header" style="justify-content:space-between;align-items:center">
                        <div>
                            <div class="section-title">👥 Team & User Management</div>
                            <div class="section-desc">Manage member roles, invites, and permissions across your workspace</div>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost" style="font-size:12px;padding:8px 14px">
                            Manage Users →
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Bottom Floating Action Bar -->
            <div class="settings-bottom-actions">
                <div class="bottom-actions-hint">Changes apply immediately upon saving.</div>
                <div style="display:flex;gap:10px">
                    <button type="button" class="btn btn-ghost" id="btn-bottom-test-conn">🔬 Test Connection</button>
                    <button type="button" class="btn btn-primary" id="btn-bottom-save-settings">💾 Save Settings</button>
                </div>
            </div>

        </div>
    </main>
</div>

@push('head')
<style>
/* Layout & Page Container */
.settings-top-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 32px;
    background: var(--bg-surface);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: blur(12px);
}
.settings-header-left { display: flex; align-items: center; gap: 16px; }
.settings-title-wrap { display: flex; align-items: center; gap: 14px; }
.settings-title-icon { font-size: 26px; }
.settings-main-title { font-size: 18px; font-weight: 800; letter-spacing: -0.3px; color: var(--text-primary); margin: 0; }
.settings-main-subtitle { font-size: 12px; color: var(--text-muted); margin: 2px 0 0; }
.settings-header-actions { display: flex; align-items: center; gap: 10px; }
.btn-sm-action { font-size: 12px; padding: 7px 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }

.settings-content-wrapper {
    max-width: 880px;
    margin: 0 auto;
    padding: 28px 24px 80px;
    width: 100%;
}

/* Category Subnav Pills */
.settings-subnav {
    display: flex;
    gap: 8px;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 5px;
    margin-bottom: 24px;
    overflow-x: auto;
}
.settings-nav-item {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: var(--radius-md);
    background: transparent;
    border: none;
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}
.settings-nav-item:hover {
    color: var(--text-primary);
    background: var(--bg-hover);
}
.settings-nav-item.active {
    background: var(--bg-elevated);
    color: var(--text-primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border: 1px solid var(--border-strong);
}
.settings-nav-item .nav-icon { font-size: 16px; }

/* Tab Panes */
.settings-tab-pane { display: none; }
.settings-tab-pane.active { display: block; animation: fadeIn 0.2s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

/* Section Cards */
.settings-section-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.section-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}
.section-title { font-size: 15px; font-weight: 700; color: var(--text-primary); }
.section-desc { font-size: 12px; color: var(--text-muted); margin-top: 3px; }

/* Provider Selection Grid */
.provider-selection-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
}
.provider-card {
    background: var(--bg-elevated);
    border: 2px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px 14px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    display: flex;
    flex-direction: column;
}
.provider-card:hover {
    border-color: var(--border-strong);
    background: var(--bg-hover);
    transform: translateY(-2px);
}
.provider-card.active {
    border-color: var(--accent);
    background: rgba(108,99,255,0.08);
    box-shadow: 0 0 20px rgba(108,99,255,0.2);
}
.provider-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.provider-card-icon { font-size: 24px; }
.provider-tag-pill {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 6px;
    border-radius: 99px;
    background: rgba(255,255,255,0.06);
    color: var(--text-muted);
}
.provider-card.active .provider-tag-pill {
    background: rgba(108,99,255,0.2);
    color: var(--accent-light);
}
.provider-card-name { font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
.provider-card-badge { font-size: 11px; color: var(--text-muted); }
.provider-active-indicator {
    display: none;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 700;
    color: var(--accent-light);
    margin-top: 10px;
    padding-top: 8px;
    border-top: 1px solid rgba(108,99,255,0.2);
}
.provider-card.active .provider-active-indicator { display: flex; }
.indicator-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); }

/* Provider Details Panel Header */
.provider-title-flex { display: flex; align-items: center; gap: 12px; }
.provider-logo-bubble {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md);
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.external-portal-link {
    font-size: 11px;
    font-weight: 600;
    color: var(--accent-light);
    text-decoration: none;
    background: rgba(108,99,255,0.1);
    padding: 4px 10px;
    border-radius: 99px;
    transition: var(--transition);
}
.external-portal-link:hover { background: rgba(108,99,255,0.2); }

/* Inputs & Key Wrappers */
.key-input-wrap { position: relative; }
.key-input-wrap .form-input { padding-right: 44px; font-family: var(--font-mono); }
.key-toggle-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 15px;
    padding: 4px;
}

/* Parameters Tab */
.param-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 16px;
}
.param-control-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 16px;
}
.param-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.param-val-badge {
    font-family: var(--font-mono);
    font-weight: 700;
    font-size: 12px;
    color: var(--accent-light);
    background: rgba(108,99,255,0.15);
    padding: 2px 8px;
    border-radius: var(--radius-sm);
}
.param-range-slider {
    width: 100%;
    accent-color: var(--accent);
    cursor: pointer;
    margin: 8px 0;
}
.range-labels-flex {
    display: flex;
    justify-content: space-between;
    font-size: 10px;
    color: var(--text-muted);
}
.preset-pills-row {
    display: flex;
    gap: 6px;
    margin-top: 12px;
    flex-wrap: wrap;
}
.btn-param-preset {
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 99px;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}
.btn-param-preset:hover {
    border-color: var(--accent);
    color: var(--text-primary);
}

/* Appearance Tab */
.display-mode-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.display-mode-card {
    background: var(--bg-elevated);
    border: 2px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.display-mode-card:hover {
    border-color: var(--border-strong);
    background: var(--bg-hover);
}
.display-mode-card.active {
    border-color: var(--accent);
    background: rgba(108,99,255,0.06);
    box-shadow: 0 0 20px rgba(108,99,255,0.15);
}
.mode-preview-box {
    height: 90px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    margin-bottom: 12px;
    display: flex;
    overflow: hidden;
    position: relative;
}
.mode-preview-dark { background: #0b0f19; }
.mode-preview-dark .preview-mini-sidebar { width: 28px; background: #111827; border-right: 1px solid #1f2937; }
.mode-preview-dark .preview-mini-content { flex: 1; padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.mode-preview-dark .preview-mini-line { width: 60%; height: 5px; background: #374151; border-radius: 3px; }
.mode-preview-dark .preview-mini-bubble.user { width: 45%; height: 12px; background: #6c63ff; border-radius: 4px; align-self: flex-end; }
.mode-preview-dark .preview-mini-bubble.ai { width: 70%; height: 16px; background: #1f2937; border-radius: 4px; }

.mode-preview-light { background: #f8fafc; }
.mode-preview-light .preview-mini-sidebar { width: 28px; background: #f1f5f9; border-right: 1px solid #e2e8f0; }
.mode-preview-light .preview-mini-content { flex: 1; padding: 8px; display: flex; flex-direction: column; gap: 4px; }
.mode-preview-light .preview-mini-line { width: 60%; height: 5px; background: #cbd5e1; border-radius: 3px; }
.mode-preview-light .preview-mini-bubble.user { width: 45%; height: 12px; background: #4f46e5; border-radius: 4px; align-self: flex-end; }
.mode-preview-light .preview-mini-bubble.ai { width: 70%; height: 16px; background: #e2e8f0; border-radius: 4px; }

.mode-preview-system { display: flex; }
.preview-half-dark { flex: 1; background: #0b0f19; border-right: 1px solid #374151; }
.preview-half-light { flex: 1; background: #f8fafc; }

.mode-meta .mode-name { font-size: 13px; font-weight: 700; color: var(--text-primary); }
.mode-meta .mode-desc { font-size: 11px; color: var(--text-muted); margin-top: 2px; line-height: 1.4; }

/* Branding Hub Card */
.branding-hub-card {
    border: 1px solid rgba(108,99,255,0.3);
    background: linear-gradient(135deg, rgba(108,99,255,0.08), rgba(15,23,42,0.8));
    padding: 32px;
}
.branding-hub-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}
.branding-hub-info { max-width: 520px; }
.branding-pill-tag {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--accent-light);
    margin-bottom: 8px;
}
.branding-hub-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 8px;
    letter-spacing: -0.5px;
}
.branding-hub-desc { font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin: 0 0 16px; }
.branding-preview-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.preview-chip {
    font-size: 11px;
    font-weight: 600;
    color: var(--text-primary);
    background: rgba(255,255,255,0.06);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: 99px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.chip-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.btn-branding-launch { font-size: 13px; padding: 10px 20px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; }
.branding-role-text { font-size: 11px; color: var(--text-muted); margin-top: 6px; text-align: center; }
.branding-locked-badge { font-size: 12px; color: var(--text-muted); background: rgba(255,255,255,0.05); padding: 8px 14px; border-radius: 99px; border: 1px solid var(--border); }

/* Bottom Floating Bar */
.settings-bottom-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 14px 20px;
    margin-top: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}
.bottom-actions-hint { font-size: 12px; color: var(--text-muted); }

/* Responsive */
@media(max-width: 768px) {
    .settings-top-header { flex-direction: column; align-items: stretch; gap: 14px; padding: 16px; }
    .settings-header-actions { justify-content: flex-end; }
    .param-grid-2col { grid-template-columns: 1fr; }
    .display-mode-grid { grid-template-columns: 1fr; }
    .branding-hub-flex { flex-direction: column; align-items: stretch; }
    .provider-selection-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@push('scripts')
<script>
let selectedProvider = '{{ $activeProvider }}';

// ===== SUBNAV TAB SWITCHING =====
document.querySelectorAll('.settings-nav-item').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabId = btn.dataset.tab;
        document.querySelectorAll('.settings-nav-item').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.settings-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(tabId)?.classList.add('active');
    });
});

// ===== PROVIDER CARD SELECTION =====
document.querySelectorAll('.provider-card').forEach(card => {
    card.addEventListener('click', () => {
        selectedProvider = card.dataset.provider;
        document.querySelectorAll('.provider-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        document.querySelectorAll('.provider-panel').forEach(p => p.style.display = 'none');
        const activePanel = document.getElementById('panel-' + selectedProvider);
        if (activePanel) activePanel.style.display = '';
    });
});

// ===== API KEY SHOW/HIDE =====
document.querySelectorAll('.key-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const inp = document.getElementById(btn.dataset.target);
        if (inp) {
            inp.type = inp.type === 'password' ? 'text' : 'password';
            btn.textContent = inp.type === 'password' ? '👁' : '🙈';
        }
    });
});

// ===== PARAMETER PRESETS & SLIDER =====
const tempSlider = document.getElementById('temperature');
const tempLabel  = document.getElementById('temp-val');
if (tempSlider && tempLabel) {
    tempSlider.addEventListener('input', e => {
        tempLabel.textContent = parseFloat(e.target.value).toFixed(2);
    });
}

const maxTokensInput = document.getElementById('max_tokens');
const tokenLabel     = document.getElementById('token-label');
if (maxTokensInput && tokenLabel) {
    maxTokensInput.addEventListener('input', e => {
        tokenLabel.textContent = e.target.value || '2048';
    });
}

document.querySelectorAll('.btn-param-preset').forEach(btn => {
    btn.addEventListener('click', () => {
        const targetId = btn.dataset.target;
        const val = btn.dataset.val;
        const input = document.getElementById(targetId);
        if (input) {
            input.value = val;
            input.dispatchEvent(new Event('input'));
        }
    });
});

// ===== MANUAL MODEL HIGHLIGHT =====
document.querySelectorAll('.model-manual-input').forEach(inp => {
    inp.addEventListener('input', () => {
        inp.classList.toggle('has-value', inp.value.trim().length > 0);
    });
    if (inp.value.trim()) inp.classList.add('has-value');
});

// ===== FETCH MODELS AJAX =====
document.querySelectorAll('.btn-fetch-models').forEach(btn => {
    btn.addEventListener('click', async () => {
        const provider = btn.dataset.provider;
        const selId    = btn.dataset.select;
        const origText = btn.textContent;
        btn.textContent = '⏳';
        btn.disabled = true;

        try {
            const res = await api(`/settings/models?provider=${provider}`);
            const sel = document.getElementById(selId);
            const current = sel.value;
            if (res.models && res.models.length) {
                sel.innerHTML = res.models.map(m =>
                    `<option value="${m}" ${m === current ? 'selected' : ''}>${m}</option>`
                ).join('');
                toast(`Loaded ${res.models.length} models from ${provider}`, 'success');
            } else {
                toast(res.error ? 'Error: ' + res.error : 'No models found', 'error');
            }
        } catch (e) {
            toast('Failed to fetch models: ' + e.message, 'error');
        } finally {
            btn.textContent = origText;
            btn.disabled = false;
        }
    });
});

// ===== COLLECT PAYLOAD =====
function collectPayload() {
    const manualModel = (fieldId) => {
        const manualInp = document.getElementById(fieldId + '_manual');
        const selectEl  = document.getElementById(fieldId);
        return manualInp?.value.trim() || selectEl?.value || '';
    };

    return {
        ai_provider:        selectedProvider,
        openai_api_key:     document.getElementById('openai_api_key')?.value     || '',
        openai_model:       manualModel('openai_model'),
        openrouter_api_key: document.getElementById('openrouter_api_key')?.value || '',
        openrouter_model:   manualModel('openrouter_model'),
        claude_api_key:     document.getElementById('claude_api_key')?.value     || '',
        claude_model:       manualModel('claude_model'),
        gemini_api_key:     document.getElementById('gemini_api_key')?.value     || '',
        gemini_model:       manualModel('gemini_model'),
        ollama_url:         document.getElementById('ollama_url')?.value         || '',
        ollama_model:       manualModel('ollama_model'),
        system_prompt:      document.getElementById('system_prompt')?.value      || '',
        temperature:        document.getElementById('temperature')?.value        || '0.7',
        max_tokens:         document.getElementById('max_tokens')?.value         || '2048',
    };
}

// ===== SAVE SETTINGS FUNCTION =====
async function saveSettings(triggerBtn) {
    const origText = triggerBtn.innerHTML;
    triggerBtn.innerHTML = '<span>⏳</span> Saving...';
    triggerBtn.disabled = true;

    try {
        const res = await api('{{ route("settings.update") }}', {
            method: 'POST',
            body: JSON.stringify(collectPayload())
        });
        toast(res.message || 'Settings saved successfully!', 'success');
    } catch (e) {
        toast('Failed to save settings: ' + e.message, 'error');
    } finally {
        triggerBtn.innerHTML = origText;
        triggerBtn.disabled = false;
    }
}

document.getElementById('btn-save-settings')?.addEventListener('click', function() { saveSettings(this); });
document.getElementById('btn-bottom-save-settings')?.addEventListener('click', function() { saveSettings(this); });

// ===== TEST CONNECTION FUNCTION =====
async function testConnection(triggerBtn) {
    const status = document.getElementById('conn-status');
    const dot    = document.getElementById('conn-dot');
    const text   = document.getElementById('conn-text');
    const origText = triggerBtn.innerHTML;

    triggerBtn.innerHTML = '<span>⏳</span> Testing...';
    triggerBtn.disabled = true;
    if (status) status.style.display = 'flex';
    if (dot) dot.style.background = 'var(--warning)';
    if (text) text.textContent = 'Connecting…';

    try {
        // Save first so test uses current values
        await api('{{ route("settings.update") }}', { method: 'POST', body: JSON.stringify(collectPayload()) });
        const res = await api('{{ route("settings.test") }}', { method: 'POST' });
        if (dot) dot.style.background = 'var(--success)';
        if (text) text.textContent = `✓ ${res.model} (${res.time_ms}ms)`;
        toast(`✅ Connected to ${res.model}! Test ping: ${res.time_ms}ms`, 'success', 5000);
    } catch (e) {
        if (dot) dot.style.background = 'var(--danger)';
        if (text) text.textContent = '✕ Failed';
        toast('❌ Connection failed: ' + e.message, 'error', 6000);
    } finally {
        triggerBtn.innerHTML = origText;
        triggerBtn.disabled = false;
    }
}

document.getElementById('btn-test-conn')?.addEventListener('click', function() { testConnection(this); });
document.getElementById('btn-bottom-test-conn')?.addEventListener('click', function() { testConnection(this); });

// ===== APPEARANCE MODE SELECTION =====
const currentMode = localStorage.getItem('app_mode') || 'dark';
document.querySelectorAll('.display-mode-card').forEach(card => {
    if (card.dataset.mode === currentMode) {
        card.classList.add('active');
    } else {
        card.classList.remove('active');
    }

    card.addEventListener('click', () => {
        const mode = card.dataset.mode;
        document.querySelectorAll('.display-mode-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        localStorage.setItem('app_mode', mode);

        let effectiveMode = mode;
        if (mode === 'system') {
            effectiveMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-mode', effectiveMode);
        document.documentElement.setAttribute('data-mode-setting', mode);

        toast(`🌓 Display mode set to ${card.querySelector('.mode-name').textContent}!`, 'success');
    });
});
</script>
@endpush
@endsection
