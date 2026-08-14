@extends('layouts.app')
@section('title', 'Settings — AI Chat UI')

@section('content')
<div class="app-shell panel-collapsed">

    @include('layouts._sidebar')

    <!-- ===== SETTINGS MAIN ===== -->
    <main class="chat-main" style="overflow-y:auto;">
        <div class="chat-header">
            <div class="chat-header-title">⚙️ Settings</div>
            <div id="conn-status" style="display:none" class="provider-badge">
                <span class="dot" id="conn-dot" style="background:var(--warning)"></span>
                <span id="conn-text">Not tested</span>
            </div>
        </div>

        <div style="max-width:700px;margin:0 auto;padding:28px 24px;width:100%">

            <!-- ===== PROVIDER SELECTOR ===== -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🔌</div>
                    <div>
                        <div class="settings-card-title">AI Provider</div>
                        <div class="settings-card-desc">Choose your AI backend — cloud or local</div>
                    </div>
                </div>
                <div class="provider-tabs" id="provider-tabs">
                    @php
                        $providers = [
                            'openai'     => ['icon' => '🧠', 'name' => 'OpenAI',      'desc' => 'GPT-4o, GPT-4…'],
                            'openrouter' => ['icon' => '🔀', 'name' => 'OpenRouter',  'desc' => '100+ models'],
                            'claude'     => ['icon' => '🤖', 'name' => 'Claude',      'desc' => 'Anthropic AI'],
                            'gemini'     => ['icon' => '💎', 'name' => 'Gemini',      'desc' => 'Google AI'],
                            'ollama'     => ['icon' => '🦙', 'name' => 'Ollama',      'desc' => 'Local LLMs'],
                        ];
                        $activeProvider = $settings['ai_provider'] ?? 'openai';
                    @endphp
                    @foreach($providers as $key => $p)
                        <button class="provider-tab {{ $activeProvider === $key ? 'active' : '' }}" data-provider="{{ $key }}">
                            <span class="provider-tab-icon">{{ $p['icon'] }}</span>
                            <span class="provider-tab-name">{{ $p['name'] }}</span>
                            <span class="provider-tab-desc">{{ $p['desc'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- ===== APPEARANCE MODE (DARK, LIGHT, SYSTEM) ===== -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🌓</div>
                    <div>
                        <div class="settings-card-title">Appearance Mode</div>
                        <div class="settings-card-desc">Choose between Dark, Light, or automatic System OS theme</div>
                    </div>
                </div>
                <div class="mode-options-grid">
                    <div class="mode-card" data-mode="dark">
                        <div class="mode-icon">🌙</div>
                        <div class="mode-name">Dark Mode</div>
                        <div class="mode-desc">Obsidian dark UI</div>
                    </div>
                    <div class="mode-card" data-mode="light">
                        <div class="mode-icon">☀️</div>
                        <div class="mode-name">Light Mode</div>
                        <div class="mode-desc">Clean crisp light UI</div>
                    </div>
                    <div class="mode-card" data-mode="system">
                        <div class="mode-icon">💻</div>
                        <div class="mode-name">System Default</div>
                        <div class="mode-desc">Matches your OS preference</div>
                    </div>
                </div>
            </div>

            <!-- ===== THEME COLOR CUSTOMIZER ===== -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🎨</div>
                    <div>
                        <div class="settings-card-title">Theme Color & Aesthetics</div>
                        <div class="settings-card-desc">Personalize your AI Chat workspace color palette</div>
                    </div>
                </div>
                <div class="theme-options-grid">
                    <div class="theme-card" data-theme="purple">
                        <div class="theme-preview" style="background: linear-gradient(135deg, #0a0c10, #151923); border-color: #6c63ff;">
                            <div class="theme-swatch" style="background: #6c63ff; box-shadow: 0 0 12px rgba(108,99,255,0.6);"></div>
                        </div>
                        <div class="theme-name">Purple Glow</div>
                    </div>
                    <div class="theme-card" data-theme="cyberpunk">
                        <div class="theme-preview" style="background: linear-gradient(135deg, #0a0c10, #151923); border-color: #f43f5e;">
                            <div class="theme-swatch" style="background: #f43f5e; box-shadow: 0 0 12px rgba(244,63,94,0.6);"></div>
                        </div>
                        <div class="theme-name">Cyberpunk Neon</div>
                    </div>
                    <div class="theme-card" data-theme="emerald">
                        <div class="theme-preview" style="background: linear-gradient(135deg, #0a0c10, #151923); border-color: #10b981;">
                            <div class="theme-swatch" style="background: #10b981; box-shadow: 0 0 12px rgba(16,185,129,0.6);"></div>
                        </div>
                        <div class="theme-name">Emerald Dark</div>
                    </div>
                    <div class="theme-card" data-theme="slate">
                        <div class="theme-preview" style="background: linear-gradient(135deg, #0a0c10, #151923); border-color: #3b82f6;">
                            <div class="theme-swatch" style="background: #3b82f6; box-shadow: 0 0 12px rgba(59,130,246,0.6);"></div>
                        </div>
                        <div class="theme-name">Midnight Slate</div>
                    </div>
                </div>
            </div>

            <!-- ===== OPENAI SETTINGS ===== -->
            <div class="settings-card provider-panel" id="panel-openai" style="{{ $activeProvider !== 'openai' ? 'display:none' : '' }}">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🔑</div>
                    <div><div class="settings-card-title">OpenAI</div><div class="settings-card-desc">platform.openai.com — GPT-4o, GPT-4, GPT-3.5</div></div>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <div class="key-input-wrap">
                        <input type="password" id="openai_api_key" class="form-input" value="{{ $settings['openai_api_key'] ?? '' }}" placeholder="sk-...">
                        <button type="button" class="key-toggle-btn" data-target="openai_api_key">👁</button>
                    </div>
                    <div class="field-hint">Get key → <a href="https://platform.openai.com/api-keys" target="_blank">platform.openai.com/api-keys</a></div>
                </div>
                @include('settings._model_field', [
                    'fieldId'     => 'openai_model',
                    'currentVal'  => $settings['openai_model'] ?? 'gpt-4o-mini',
                    'staticOpts'  => ['gpt-4o','gpt-4o-mini','gpt-4-turbo','gpt-4','gpt-3.5-turbo'],
                    'provider'    => 'openai',
                ])
            </div>

            <!-- ===== OPENROUTER SETTINGS ===== -->
            <div class="settings-card provider-panel" id="panel-openrouter" style="{{ $activeProvider !== 'openrouter' ? 'display:none' : '' }}">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🔀</div>
                    <div><div class="settings-card-title">OpenRouter</div><div class="settings-card-desc">openrouter.ai — 100+ models from many providers</div></div>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <div class="key-input-wrap">
                        <input type="password" id="openrouter_api_key" class="form-input" value="{{ $settings['openrouter_api_key'] ?? '' }}" placeholder="sk-or-...">
                        <button type="button" class="key-toggle-btn" data-target="openrouter_api_key">👁</button>
                    </div>
                    <div class="field-hint">Get key → <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai/keys</a></div>
                </div>
                @include('settings._model_field', [
                    'fieldId'     => 'openrouter_model',
                    'currentVal'  => $settings['openrouter_model'] ?? 'openai/gpt-4o-mini',
                    'staticOpts'  => ['openai/gpt-4o','openai/gpt-4o-mini','anthropic/claude-3.5-sonnet','google/gemini-2.0-flash','meta-llama/llama-3.3-70b-instruct','mistralai/mistral-large','deepseek/deepseek-r1'],
                    'provider'    => 'openrouter',
                ])
            </div>

            <!-- ===== CLAUDE SETTINGS ===== -->
            <div class="settings-card provider-panel" id="panel-claude" style="{{ $activeProvider !== 'claude' ? 'display:none' : '' }}">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🤖</div>
                    <div><div class="settings-card-title">Claude (Anthropic)</div><div class="settings-card-desc">console.anthropic.com — Claude 3.5 Sonnet, Opus, Haiku</div></div>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <div class="key-input-wrap">
                        <input type="password" id="claude_api_key" class="form-input" value="{{ $settings['claude_api_key'] ?? '' }}" placeholder="sk-ant-...">
                        <button type="button" class="key-toggle-btn" data-target="claude_api_key">👁</button>
                    </div>
                    <div class="field-hint">Get key → <a href="https://console.anthropic.com/settings/keys" target="_blank">console.anthropic.com</a></div>
                </div>
                @include('settings._model_field', [
                    'fieldId'     => 'claude_model',
                    'currentVal'  => $settings['claude_model'] ?? 'claude-3-5-sonnet-20241022',
                    'staticOpts'  => ['claude-opus-4-5','claude-sonnet-4-5','claude-haiku-4-5','claude-3-5-sonnet-20241022','claude-3-5-haiku-20241022','claude-3-opus-20240229','claude-3-haiku-20240307'],
                    'provider'    => 'claude',
                ])
            </div>

            <!-- ===== GEMINI SETTINGS ===== -->
            <div class="settings-card provider-panel" id="panel-gemini" style="{{ $activeProvider !== 'gemini' ? 'display:none' : '' }}">
                <div class="settings-card-header">
                    <div class="settings-card-icon">💎</div>
                    <div><div class="settings-card-title">Gemini (Google)</div><div class="settings-card-desc">aistudio.google.com — Gemini 2.0, 1.5 Pro &amp; Flash</div></div>
                </div>
                <div class="form-group">
                    <label class="form-label">API Key</label>
                    <div class="key-input-wrap">
                        <input type="password" id="gemini_api_key" class="form-input" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIza...">
                        <button type="button" class="key-toggle-btn" data-target="gemini_api_key">👁</button>
                    </div>
                    <div class="field-hint">Get key → <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a></div>
                </div>
                @include('settings._model_field', [
                    'fieldId'     => 'gemini_model',
                    'currentVal'  => $settings['gemini_model'] ?? 'gemini-2.0-flash',
                    'staticOpts'  => ['gemini-2.5-pro','gemini-2.5-flash','gemini-2.0-flash','gemini-2.0-flash-lite','gemini-1.5-pro','gemini-1.5-flash','gemini-1.5-flash-8b'],
                    'provider'    => 'gemini',
                ])
            </div>

            <!-- ===== OLLAMA SETTINGS ===== -->
            <div class="settings-card provider-panel" id="panel-ollama" style="{{ $activeProvider !== 'ollama' ? 'display:none' : '' }}">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🦙</div>
                    <div style="flex:1">
                        <div class="settings-card-title">Ollama (Local)</div>
                        <div class="settings-card-desc">Run LLMs privately on your own machine</div>
                    </div>
                    <div id="ollama-online-badge" class="provider-badge" style="{{ $ollamaOnline ? '' : 'display:none' }}">
                        <span class="dot"></span> Online
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Server URL</label>
                    <input type="text" id="ollama_url" class="form-input" value="{{ $settings['ollama_url'] ?? 'http://host.docker.internal:11434' }}" placeholder="http://localhost:11434">
                    <div class="field-hint">From Docker use <code>http://host.docker.internal:11434</code> to reach your Mac's localhost</div>
                </div>
                @include('settings._model_field', [
                    'fieldId'     => 'ollama_model',
                    'currentVal'  => $settings['ollama_model'] ?? 'llama3',
                    'staticOpts'  => !empty($ollamaModels) ? $ollamaModels : ['llama3','llama3.1','llama3.2','mistral','phi3','gemma2','codellama','deepseek-r1'],
                    'provider'    => 'ollama',
                ])
            </div>

            <!-- ===== MODEL PARAMETERS ===== -->
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon">🎛️</div>
                    <div><div class="settings-card-title">Model Parameters</div><div class="settings-card-desc">Applied to all providers</div></div>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        System Prompt
                        <span style="color:var(--text-muted);font-weight:400;text-transform:none;letter-spacing:0">(default for all conversations)</span>
                    </label>
                    <textarea id="system_prompt" class="form-textarea" rows="4" placeholder="You are a helpful assistant.">{{ $settings['system_prompt'] ?? 'You are a helpful assistant.' }}</textarea>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Temperature <span id="temp-val" style="color:var(--accent-light);font-weight:700">{{ number_format((float)($settings['temperature'] ?? 0.7), 2) }}</span></label>
                        <input type="range" id="temperature" min="0" max="2" step="0.05" value="{{ $settings['temperature'] ?? '0.7' }}" style="width:100%;accent-color:var(--accent);cursor:pointer">
                        <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-top:3px"><span>Precise</span><span>Creative</span></div>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Max Tokens</label>
                        <input type="number" id="max_tokens" class="form-input" value="{{ $settings['max_tokens'] ?? '2048' }}" min="128" max="32768" step="128" placeholder="2048">
                    </div>
                </div>
            </div>

            <!-- ===== ACTIONS ===== -->
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;flex-wrap:wrap">
                <button type="button" class="btn btn-ghost" id="btn-test-conn">🔬 Test Connection</button>
                <button type="button" class="btn btn-primary" id="btn-save-settings">💾 Save Settings</button>
            </div>

        </div>
    </main>

    <!-- ===== RIGHT TIPS PANEL ===== -->
    <aside class="right-panel">
        <div class="panel-header"><div class="panel-title">Provider Guide</div></div>
        <div class="panel-body">

            <div class="settings-tip">
                <div class="tip-icon">🧠</div>
                <div><strong>OpenAI</strong><p>Best quality. Needs billing enabled on <a href="https://platform.openai.com" target="_blank" style="color:var(--accent-light)">platform.openai.com</a>.</p></div>
            </div>
            <div class="settings-tip">
                <div class="tip-icon">🔀</div>
                <div><strong>OpenRouter</strong><p>Access 100+ models (GPT, Claude, Llama, Gemini…) with a single API key. Great for comparing models.</p></div>
            </div>
            <div class="settings-tip">
                <div class="tip-icon">🤖</div>
                <div><strong>Claude</strong><p>Anthropic's Claude excels at long documents, nuanced writing, and following complex instructions.</p></div>
            </div>
            <div class="settings-tip">
                <div class="tip-icon">💎</div>
                <div><strong>Gemini</strong><p>Google's model. Fast and free tier available via AI Studio. Great for multimodal tasks.</p></div>
            </div>
            <div class="settings-tip">
                <div class="tip-icon">🦙</div>
                <div><strong>Ollama</strong><p>100% private. Runs on your Mac. Use <code>http://host.docker.internal:11434</code> as the URL from Docker.</p></div>
            </div>

            <div style="margin-top:16px">
                <div class="panel-title" style="margin-bottom:10px">Manual Model</div>
                <p style="font-size:12px;color:var(--text-muted);line-height:1.6">Type any model name directly in the <strong>Custom model</strong> field — it overrides the dropdown. Useful for new or fine-tuned models not yet in the list.</p>
            </div>
        </div>
    </aside>
</div>

@push('head')
<style>
.settings-card { background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-lg);padding:22px;margin-bottom:16px; }
.settings-card-header { display:flex;align-items:center;gap:14px;margin-bottom:20px; }
.settings-card-icon { width:42px;height:42px;background:var(--bg-elevated);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;border:1px solid var(--border); }

/* Mode Customizer Grid */
.mode-options-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 14px;
}
.mode-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 14px;
    cursor: pointer;
    text-align: center;
    transition: var(--transition);
}
.mode-card:hover, .mode-card.active {
    border-color: var(--accent);
    box-shadow: 0 0 15px var(--accent-glow);
}
.mode-icon {
    font-size: 22px;
    margin-bottom: 6px;
}
.mode-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
}
.mode-desc {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Theme Customizer Grid */
.theme-options-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-top: 14px;
}
.theme-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 12px;
    cursor: pointer;
    text-align: center;
    transition: var(--transition);
}
.theme-card:hover, .theme-card.active {
    border-color: var(--accent);
    box-shadow: 0 0 15px var(--accent-glow);
}
.theme-preview {
    height: 48px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
}
.theme-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
}
.theme-name {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text-primary);
}
.settings-card-title { font-size:15px;font-weight:600; }
.settings-card-desc { font-size:12px;color:var(--text-muted);margin-top:2px; }

.provider-tabs { display:grid;grid-template-columns:repeat(5,1fr);gap:8px; }
.provider-tab { padding:12px 8px;background:var(--bg-elevated);border:2px solid var(--border);border-radius:var(--radius-md);cursor:pointer;transition:var(--transition);display:flex;flex-direction:column;align-items:center;gap:3px;text-align:center;font-family:var(--font-sans); }
.provider-tab:hover { border-color:var(--border-strong);background:var(--bg-hover); }
.provider-tab.active { border-color:var(--accent);background:rgba(108,99,255,0.12); }
.provider-tab-icon { font-size:22px; }
.provider-tab-name { font-size:12px;font-weight:700;color:var(--text-primary); }
.provider-tab-desc { font-size:10px;color:var(--text-muted); }

.key-input-wrap { position:relative; }
.key-input-wrap .form-input { padding-right:44px; }
.key-toggle-btn { position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:16px;padding:0; }

.field-hint { font-size:11px;color:var(--text-muted);margin-top:4px; }
.field-hint a { color:var(--accent-light); }
.field-hint code { background:var(--bg-active);padding:1px 5px;border-radius:3px;color:var(--accent-light);font-family:var(--font-mono);font-size:10.5px; }

/* Model field */
.model-field-wrap { display:flex;flex-direction:column;gap:8px; }
.model-row { display:flex;gap:8px; }
.model-manual-row { display:flex;align-items:center;gap:8px; }
.model-manual-label { font-size:11px;color:var(--text-muted);white-space:nowrap; }
.model-manual-input { flex:1;background:var(--bg-base);border:1px dashed var(--border-strong);border-radius:var(--radius-md);color:var(--text-primary);font-family:var(--font-mono);font-size:13px;padding:8px 12px;transition:var(--transition);outline:none; }
.model-manual-input:focus { border-color:var(--accent);border-style:solid;box-shadow:0 0 0 3px var(--accent-glow); }
.model-manual-input::placeholder { color:var(--text-muted);font-family:var(--font-sans); }
.model-manual-input.has-value { border-color:var(--accent);border-style:solid;background:rgba(108,99,255,0.05); }

.settings-tip { display:flex;gap:12px;padding:12px;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);margin-bottom:8px; }
.tip-icon { font-size:18px;flex-shrink:0; }
.settings-tip strong { font-size:12px;font-weight:600;display:block;margin-bottom:3px; }
.settings-tip p { font-size:11px;color:var(--text-muted);line-height:1.5;margin:0; }
.settings-tip a { color:var(--accent-light); }
.settings-tip code { background:var(--bg-active);padding:1px 4px;border-radius:3px;font-size:10px;color:var(--accent-light);font-family:var(--font-mono); }
</style>
@endpush

@push('scripts')
<script>
let selectedProvider = '{{ $activeProvider }}';

// ===== PROVIDER TABS =====
document.querySelectorAll('.provider-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        selectedProvider = tab.dataset.provider;
        document.querySelectorAll('.provider-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.provider-panel').forEach(p => p.style.display = 'none');
        document.getElementById('panel-' + selectedProvider).style.display = '';
    });
});

// ===== API KEY SHOW/HIDE =====
document.querySelectorAll('.key-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const inp = document.getElementById(btn.dataset.target);
        inp.type = inp.type === 'password' ? 'text' : 'password';
        btn.textContent = inp.type === 'password' ? '👁' : '🙈';
    });
});

// ===== TEMPERATURE SLIDER =====
document.getElementById('temperature').addEventListener('input', e => {
    document.getElementById('temp-val').textContent = parseFloat(e.target.value).toFixed(2);
});

// ===== MANUAL MODEL HIGHLIGHT =====
document.querySelectorAll('.model-manual-input').forEach(inp => {
    inp.addEventListener('input', () => {
        inp.classList.toggle('has-value', inp.value.trim().length > 0);
    });
    // Init
    if (inp.value.trim()) inp.classList.add('has-value');
});

// ===== FETCH MODELS =====
document.querySelectorAll('.btn-fetch-models').forEach(btn => {
    btn.addEventListener('click', async () => {
        const provider = btn.dataset.provider;
        const selId    = btn.dataset.select;
        const orig     = btn.textContent;
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
                toast(`Loaded ${res.models.length} models`, 'success');
            } else {
                toast(res.error ? 'Error: ' + res.error : 'No models found', 'error');
            }
        } catch (e) {
            toast('Failed: ' + e.message, 'error');
        } finally {
            btn.textContent = orig;
            btn.disabled = false;
        }
    });
});

// ===== COLLECT ALL FIELD VALUES =====
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
        system_prompt:      document.getElementById('system_prompt').value,
        temperature:        document.getElementById('temperature').value,
        max_tokens:         document.getElementById('max_tokens').value,
    };
}

// ===== SAVE =====
document.getElementById('btn-save-settings').addEventListener('click', async () => {
    const btn = document.getElementById('btn-save-settings');
    btn.textContent = '⏳ Saving...';
    btn.disabled = true;
    try {
        const res = await api('{{ route("settings.update") }}', { method: 'POST', body: JSON.stringify(collectPayload()) });
        toast(res.message || 'Settings saved!', 'success');
    } catch (e) {
        toast('Failed: ' + e.message, 'error');
    } finally {
        btn.textContent = '💾 Save Settings';
        btn.disabled = false;
    }
});

// ===== TEST CONNECTION =====
document.getElementById('btn-test-conn').addEventListener('click', async () => {
    const btn    = document.getElementById('btn-test-conn');
    const status = document.getElementById('conn-status');
    const dot    = document.getElementById('conn-dot');
    const text   = document.getElementById('conn-text');

    btn.textContent = '⏳ Testing...';
    btn.disabled = true;
    status.style.display = 'flex';
    dot.style.background = 'var(--warning)';
    text.textContent = 'Connecting…';

    try {
        // Save first so test uses current values
        await api('{{ route("settings.update") }}', { method: 'POST', body: JSON.stringify(collectPayload()) });
        const res = await api('{{ route("settings.test") }}', { method: 'POST' });
        dot.style.background = 'var(--success)';
        text.textContent = `✓ ${res.model} · ${res.time_ms}ms`;
        toast(`✅ Connected! Response: "${res.response.substring(0,60)}"`, 'success', 5000);
    } catch (e) {
        dot.style.background = 'var(--danger)';
        text.textContent = '✕ Failed';
        toast('❌ ' + e.message, 'error', 6000);
    } finally {
        btn.textContent = '🔬 Test Connection';
        btn.disabled = false;
    }
});

// ===== APPEARANCE MODE SELECTION HANDLER =====
const currentMode = localStorage.getItem('app_mode') || 'dark';
document.querySelectorAll('.mode-card').forEach(card => {
    if (card.dataset.mode === currentMode) {
        card.classList.add('active');
    } else {
        card.classList.remove('active');
    }

    card.addEventListener('click', () => {
        const mode = card.dataset.mode;
        document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        localStorage.setItem('app_mode', mode);

        let effectiveMode = mode;
        if (mode === 'system') {
            effectiveMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-mode', effectiveMode);
        document.documentElement.setAttribute('data-mode-setting', mode);

        toast(`🌓 Appearance set to ${card.querySelector('.mode-name').textContent}!`, 'success');
    });
});

// ===== THEME SELECTION HANDLER =====
const currentTheme = localStorage.getItem('app_theme') || 'purple';
document.querySelectorAll('.theme-card').forEach(card => {
    if (card.dataset.theme === currentTheme) {
        card.classList.add('active');
    } else {
        card.classList.remove('active');
    }

    card.addEventListener('click', () => {
        const theme = card.dataset.theme;
        document.querySelectorAll('.theme-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');

        if (theme === 'purple') {
            document.documentElement.removeAttribute('data-theme');
            localStorage.removeItem('app_theme');
        } else {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('app_theme', theme);
        }
        toast(`🎨 Theme updated to ${card.querySelector('.theme-name').textContent}!`, 'success');
    });
});
</script>
@endpush
@endsection
