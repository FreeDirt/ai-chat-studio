@extends('layouts.app')
@section('title', 'Personas — AI Chat UI')

@section('content')
<div class="app-shell panel-collapsed">

    @include('layouts._sidebar')

    <!-- ===== PERSONAS MAIN ===== -->
    <main class="chat-main" style="overflow-y:auto;">
        <div class="chat-header">
            <div class="chat-header-title">🎭 Personas &amp; Prompt Studio</div>
            <button class="btn-send" id="btn-add-persona" style="font-size:13px;padding:8px 16px">
                + New Persona
            </button>
        </div>

        <div style="max-width:850px;margin:0 auto;padding:28px 24px;width:100%">

            <p style="color:var(--text-muted);font-size:14px;margin-bottom:24px;line-height:1.6">
            <!-- Search & Filter Bar with View Mode Toggle -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
                <div style="flex:1;min-width:240px;position:relative">
                    <input type="text" id="persona-studio-search" placeholder="🔍 Search personas by name or system prompt instructions..." 
                           style="width:100%;background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--radius-md);color:var(--text-primary);padding:10px 14px;font-size:13.5px;outline:none;transition:var(--transition)"
                           autocomplete="off">
                </div>
                <div class="view-toggle-group">
                    <button type="button" class="btn-view-toggle active" id="btn-view-list" title="List View">☰ List</button>
                    <button type="button" class="btn-view-toggle" id="btn-view-grid" title="Grid View">🞉 Grid</button>
                </div>
                <div style="font-size:12px;color:var(--text-muted);white-space:nowrap" id="persona-count-label">
                    Showing {{ $personas->count() }} personas
                </div>
            </div>

            <!-- Personas Grid -->
            <div class="personas-grid" id="personas-grid">
                @foreach($personas as $persona)
                    <div class="persona-full-card" data-id="{{ $persona->id }}" data-name="{{ strtolower($persona->name) }}" data-prompt="{{ strtolower($persona->system_prompt) }}" draggable="true">
                        <div class="pfc-drag-handle" title="Drag to reorder">⠿</div>
                        <div class="pfc-icon" style="background:{{ $persona->color }}22;color:{{ $persona->color }}">
                            {!! $persona->formatted_icon !!}
                        </div>
                        <div class="pfc-body">
                            <div class="pfc-header">
                                <div class="pfc-name">{{ $persona->name }}</div>
                                <div class="pfc-badges">
                                    <span class="pfc-badge" style="background:{{ $persona->color }}22;color:{{ $persona->color }}">
                                        Persona
                                    </span>
                                    @if($persona->documents_count ?? $persona->documents->count())
                                        <span class="pfc-badge" style="background:rgba(108,99,255,0.15);color:var(--accent-light)">
                                            📎 {{ $persona->documents_count ?? $persona->documents->count() }} Docs (RAG)
                                        </span>
                                    @endif
                                    @if(!$persona->is_active)
                                        <span class="pfc-badge" style="background:var(--bg-active);color:var(--text-muted)">Hidden</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pfc-prompt">{{ Str::limit($persona->system_prompt, 140) }}</div>
                        </div>
                        <div class="pfc-actions">
                            <button class="btn btn-ghost pfc-edit-btn" data-id="{{ $persona->id }}"
                                    data-name="{{ $persona->name }}"
                                    data-icon="{{ $persona->icon }}"
                                    data-prompt="{{ $persona->system_prompt }}"
                                    data-color="{{ $persona->color }}"
                                    data-active="{{ $persona->is_active ? '1' : '0' }}"
                                    style="font-size:12px;padding:6px 12px">
                                ✏️ Edit Studio
                            </button>
                            <button class="btn btn-danger pfc-delete-btn" data-id="{{ $persona->id }}"
                                    style="font-size:12px;padding:6px 12px">
                                🗑️
                            </button>
                        </div>
                    </div>
                @endforeach

                @if($personas->isEmpty())
                    <div style="text-align:center;padding:48px;color:var(--text-muted);grid-column:1/-1">
                        <div style="font-size:48px;margin-bottom:12px">🎭</div>
                        <p>No personas yet. Create your first one!</p>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <!-- Info Panel -->
    <aside class="right-panel">
        <div class="panel-header">
            <div class="panel-title">Prompt Engineering</div>
        </div>
        <div class="panel-body">
            <div style="font-size:13px;color:var(--text-secondary);line-height:1.7">
                <p style="margin-bottom:12px">Use <strong>Advanced Prompting</strong> to turn standard AI into tailored agents.</p>
            </div>

            <div style="margin-top:16px">
                <div class="panel-title" style="margin-bottom:10px">Dynamic Variables</div>
                <div class="var-badge-list">
                    <span class="var-tag">@{{date}}</span>
                    <span class="var-tag">@{{time}}</span>
                    <span class="var-tag">@{{day}}</span>
                    <span class="var-tag">@{{datetime}}</span>
                    <span class="var-tag">@{{timezone}}</span>
                </div>
                <p style="font-size:11px;color:var(--text-muted);margin-top:8px">These automatically expand to live server values when sending messages.</p>
            </div>

            <div style="margin-top:20px">
                <div class="panel-title" style="margin-bottom:10px">Persona RAG Knowledge</div>
                <p style="font-size:11px;color:var(--text-muted);line-height:1.6">
                    Attach PDF, DOCX, Code or Text files directly to a Persona. Whenever that Persona is active, its documents are searched for relevant context!
                </p>
            </div>
        </div>
    </aside>
</div>

<!-- ===== ADVANCED PERSONA EDIT / PROMPT STUDIO MODAL ===== -->
<div class="modal-overlay hidden" id="persona-modal">
    <div class="modal modal-lg" style="max-width:900px;width:95%;max-height:90vh;display:flex;flex-direction:column">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h3 id="modal-title" style="margin:0">Persona Studio</h3>
            <button class="btn btn-ghost" id="btn-fullscreen-toggle" title="Toggle Fullscreen" style="font-size:12px;padding:4px 8px">
                ⛶ Fullscreen
            </button>
        </div>

        <div style="overflow-y:auto;flex:1;padding-right:4px">
            <div style="display:grid;grid-template-columns:1.8fr 1.5fr 1fr;gap:16px;margin-bottom:14px">
                <div class="form-group" style="margin:0">
                    <label class="form-label">Persona Name</label>
                    <input type="text" class="form-input" id="p-name" placeholder="e.g. Senior Tech Lead" maxlength="100">
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label" style="display:flex;justify-content:space-between;align-items:center">
                        <span>Icon (Emoji or GIF)</span>
                        <button type="button" class="btn btn-ghost" id="btn-upload-icon-file" style="font-size:10px;padding:1px 6px;margin:0" title="Upload local GIF, PNG, or JPG file">
                            📁 Upload GIF
                        </button>
                    </label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" class="form-input" id="p-icon" placeholder="🤖 or URL / Upload GIF" style="font-size:12.5px;flex:1">
                        <div id="p-icon-preview" style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--bg-elevated);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px;overflow:hidden;flex-shrink:0">
                            🤖
                        </div>
                    </div>
                    <input type="file" id="p-icon-file" accept=".gif,.png,.jpg,.jpeg,.svg,.webp" style="display:none">
                </div>
                <div class="form-group" style="margin:0">
                    <label class="form-label">Accent Color</label>
                    <input type="color" class="form-input" id="p-color" value="#6c63ff" style="height:38px;padding:2px;cursor:pointer">
                </div>
            </div>

            <!-- TAB BAR FOR EDITOR / TEMPLATES / DOCUMENTS / TESTER -->
            <div class="studio-tabs">
                <button class="studio-tab active" data-tab="tab-prompt">📝 System Prompt</button>
                <button class="studio-tab" data-tab="tab-templates">⚡ Prompt Patterns</button>
                <button class="studio-tab" data-tab="tab-docs">📎 Knowledge Docs (<span id="p-doc-count">0</span>)</button>
                <button class="studio-tab" data-tab="tab-test">🔬 Test Persona</button>
            </div>

            <!-- TAB 1: SYSTEM PROMPT EDITOR -->
            <div class="tab-content" id="tab-prompt">
                <div class="var-bar">
                    <span style="font-size:11px;font-weight:600;color:var(--text-muted)">Insert Variable:</span>
                    <button type="button" class="btn-var-insert" data-var="@{{date}}">+ @{{date}}</button>
                    <button type="button" class="btn-var-insert" data-var="@{{time}}">+ @{{time}}</button>
                    <button type="button" class="btn-var-insert" data-var="@{{day}}">+ @{{day}}</button>
                    <button type="button" class="btn-var-insert" data-var="@{{datetime}}">+ @{{datetime}}</button>
                    <button type="button" class="btn-var-insert" data-var="@{{timezone}}">+ @{{timezone}}</button>
                </div>

                <div class="form-group" style="margin-top:10px">
                    <textarea class="form-textarea" id="p-prompt" rows="12" style="font-family:var(--font-mono);font-size:13px;line-height:1.6"
                              placeholder="Define the AI's role, instructions, constraints, and format requirements..."></textarea>
                    <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-muted);margin-top:4px">
                        <span>Advanced syntax supported: XML tags <code>&lt;context&gt;</code>, Few-Shot examples, Markdown tables.</span>
                        <span id="prompt-char-count">0 / 32,768</span>
                    </div>
                </div>
            </div>

            <!-- TAB 2: PROMPT TEMPLATE PATTERNS -->
            <div class="tab-content hidden" id="tab-templates">
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                    Click any pattern to append its structure into your System Prompt:
                </div>
                <div class="template-grid">
                    <div class="template-card" data-template="role">
                        <div class="tpl-title">🎯 Role &amp; Persona Definition</div>
                        <div class="tpl-desc">Standard high-performance system role definition with strict boundaries.</div>
                    </div>
                    <div class="template-card" data-template="cot">
                        <div class="tpl-title">🧠 Chain-of-Thought (Reasoning)</div>
                        <div class="tpl-desc">Instructs the AI to think step-by-step before outputting the final answer.</div>
                    </div>
                    <div class="template-card" data-template="xml">
                        <div class="tpl-title">🏷️ Structured XML Prompt</div>
                        <div class="tpl-desc">Uses &lt;role&gt;, &lt;rules&gt;, &lt;constraints&gt;, and &lt;output_format&gt; for maximum prompt adherence.</div>
                    </div>
                    <div class="template-card" data-template="fewshot">
                        <div class="tpl-title">📋 Few-Shot Examples Pattern</div>
                        <div class="tpl-desc">Provides sample Input / Output pairs to guide AI style and output format.</div>
                    </div>
                    <div class="template-card" data-template="react">
                        <div class="tpl-title">🔄 ReAct (Reason + Action)</div>
                        <div class="tpl-desc">Guides the AI through Thought -&gt; Action -&gt; Observation loops.</div>
                    </div>
                    <div class="template-card" data-template="json">
                        <div class="tpl-title">📦 Strict JSON Output</div>
                        <div class="tpl-desc">Forces AI to respond only in valid JSON conforming to a schema.</div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: PERSONA DOCUMENTS (RAG) -->
            <div class="tab-content hidden" id="tab-docs">
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                    Documents attached here are <strong>always accessible via RAG</strong> when this persona is active in chat.
                </div>
                <div id="p-doc-list" style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px"></div>

                <div id="p-doc-upload-zone" class="doc-upload-zone">
                    <div style="font-size:24px">📎</div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-secondary)">Upload knowledge document for Persona</div>
                    <div style="font-size:11px;color:var(--text-muted)">PDF, DOCX, TXT, MD, Code, CSV (Max 20MB)</div>
                </div>
                <input type="file" id="p-file-input" style="display:none"
                       accept=".pdf,.docx,.txt,.md,.php,.js,.ts,.py,.java,.go,.rb,.cs,.cpp,.c,.h,.html,.css,.json,.yaml,.yml,.xml,.sh,.sql,.csv,.jsx,.tsx,.vue,.rs,.env">
            </div>

            <!-- TAB 4: TEST PERSONA -->
            <div class="tab-content hidden" id="tab-test">
                <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px">
                    Test your system prompt against your active AI provider in real-time.
                </div>
                <div class="form-group">
                    <label class="form-label">Test User Message</label>
                    <input type="text" class="form-input" id="p-test-input" placeholder="e.g. Introduce yourself and explain your approach to writing code.">
                </div>
                <button type="button" class="btn btn-ghost" id="p-btn-test-run">🔬 Run Test Message</button>

                <div id="p-test-result" style="display:none;margin-top:14px;padding:14px;background:var(--bg-base);border:1px solid var(--border);border-radius:var(--radius-md)">
                    <div style="font-size:11px;font-weight:700;color:var(--accent-light);margin-bottom:6px" id="p-test-meta"></div>
                    <div style="font-size:13px;line-height:1.6;white-space:pre-wrap" id="p-test-output"></div>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;margin-bottom:0">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <div class="toggle-switch" id="p-active-toggle">
                        <div class="toggle-knob"></div>
                    </div>
                    <span style="font-size:13px;color:var(--text-secondary)">Active (visible in chat selection)</span>
                </label>
            </div>
        </div>

        <div class="modal-footer" style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
            <button class="btn btn-ghost" id="persona-modal-cancel">Cancel</button>
            <button class="btn btn-primary" id="persona-modal-save">💾 Save Persona Studio</button>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay hidden" id="delete-persona-modal">
    <div class="modal">
        <h3>Delete Persona?</h3>
        <p style="color:var(--text-secondary);font-size:14px;line-height:1.6">This persona and all its attached RAG documents will be permanently removed.</p>
        <div class="modal-footer">
            <button class="btn btn-ghost" id="delete-persona-cancel">Cancel</button>
            <button class="btn btn-danger" id="delete-persona-confirm">Delete</button>
        </div>
    </div>
</div>

@push('head')
<style>
.personas-grid { transition: var(--transition); }
.personas-grid.view-list { display: flex; flex-direction: column; gap: 10px; }
.personas-grid.view-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 14px; }

.personas-grid.view-grid .persona-full-card {
    flex-direction: column;
    align-items: flex-start;
    position: relative;
    padding: 22px 20px;
    height: 100%;
}
.personas-grid.view-grid .pfc-icon {
    width: 72px;
    height: 72px;
    font-size: 38px;
    border-radius: var(--radius-lg);
    margin-bottom: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.35);
    border: 1px solid rgba(255,255,255,0.08);
}
.personas-grid.view-grid .pfc-name {
    font-size: 16px;
    font-weight: 700;
}
.personas-grid.view-grid .pfc-drag-handle {
    position: absolute;
    top: 16px;
    right: 16px;
}
.personas-grid.view-grid .pfc-actions {
    margin-top: 14px;
    width: 100%;
    justify-content: flex-end;
    border-top: 1px solid var(--border);
    padding-top: 10px;
}
.personas-grid.view-grid .pfc-prompt {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.view-toggle-group {
    display: inline-flex;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 2px;
}
.btn-view-toggle {
    background: none;
    border: none;
    color: var(--text-muted);
    padding: 5px 11px;
    font-size: 12px;
    font-weight: 600;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
}
.btn-view-toggle:hover { color: var(--text-primary); }
.btn-view-toggle.active { background: var(--bg-active); color: var(--accent-light); }

.persona-full-card {
    display: flex; align-items: center; gap: 14px; padding: 16px 18px;
    background: var(--bg-surface); border: 1px solid var(--border);
    border-radius: var(--radius-lg); transition: var(--transition); cursor: grab;
}
.persona-full-card:hover { background: var(--bg-elevated); border-color: var(--border-strong); }
.persona-full-card.drag-over { border-color: var(--accent); background: rgba(108,99,255,0.08); }
.persona-full-card.dragging { opacity: 0.4; }

.pfc-drag-handle { color: var(--text-muted); font-size: 18px; cursor: grab; padding: 0 4px; flex-shrink: 0; user-select: none; }
.pfc-icon { width: 48px; height: 48px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.pfc-body { flex: 1; min-width: 0; }
.pfc-header { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; flex-wrap: wrap; }
.pfc-name { font-size: 15px; font-weight: 700; }
.pfc-badges { display: flex; gap: 6px; flex-wrap: wrap; }
.pfc-badge { padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 500; }
.pfc-prompt { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; }
.pfc-actions { display: flex; gap: 8px; flex-shrink: 0; }

.var-badge-list { display: flex; flex-wrap: wrap; gap: 6px; }
.var-tag { background: var(--bg-elevated); border: 1px solid var(--border); padding: 2px 8px; border-radius: 4px; font-family: var(--font-mono); font-size: 11px; color: var(--accent-light); }

/* Studio Tabs */
.studio-tabs { display: flex; gap: 8px; border-bottom: 1px solid var(--border); padding-bottom: 8px; margin-bottom: 14px; }
.studio-tab { background: none; border: none; padding: 6px 14px; font-size: 12.5px; font-weight: 600; color: var(--text-muted); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); }
.studio-tab:hover { color: var(--text-primary); background: var(--bg-hover); }
.studio-tab.active { color: var(--accent-light); background: rgba(108,99,255,0.12); }

.var-bar { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background: var(--bg-base); padding: 8px 12px; border-radius: var(--radius-md); border: 1px solid var(--border); }
.btn-var-insert { background: var(--bg-surface); border: 1px solid var(--border); border-radius: 4px; padding: 2px 8px; font-family: var(--font-mono); font-size: 11px; color: var(--accent-light); cursor: pointer; }
.btn-var-insert:hover { border-color: var(--accent); background: var(--bg-hover); }

.template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.template-card { padding: 12px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); cursor: pointer; transition: var(--transition); }
.template-card:hover { border-color: var(--accent); background: var(--bg-elevated); transform: translateY(-1px); }
.tpl-title { font-size: 13px; font-weight: 700; margin-bottom: 4px; color: var(--text-primary); }
.tpl-desc { font-size: 11.5px; color: var(--text-muted); line-height: 1.4; }

/* Toggle switch */
.toggle-switch { width: 40px; height: 22px; background: var(--bg-active); border-radius: 99px; position: relative; cursor: pointer; transition: background 0.2s; flex-shrink: 0; border: 1px solid var(--border); }
.toggle-switch.on { background: var(--accent); }
.toggle-knob { width: 16px; height: 16px; background: #fff; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: left 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.3); }
.toggle-switch.on .toggle-knob { left: 20px; }
</style>
@endpush

@push('scripts')
<script>
let editingPersonaId = null;
let isPersonaActive  = true;
let deletePersonaId  = null;

const PROMPT_PATTERNS = {
    role: `### ROLE & PURPOSE\nYou are an expert [insert role, e.g., Senior Software Architect]. Your objective is to provide precise, clean, and highly effective solutions.\n\n### STYLISTIC RULES\n- Be clear, concise, and direct.\n- Avoid unnecessary conversational filler.\n- Prioritize practical code snippets and structured lists.`,
    cot: `### REASONING PROCESS\nFor every request, think step-by-step before providing your answer:\n1. Analyze the core requirements.\n2. Identify constraints and edge cases.\n3. Formulate the optimal approach.\n\nOutput your reasoning inside <thinking>...</thinking> tags, followed by your final answer in <response>...</response>.`,
    xml: `<role>\nYou are a specialized AI assistant.\n</role>\n\n<constraints>\n- Do not guess unverified facts.\n- Use Markdown for all formatting.\n</constraints>\n\n<output_format>\nProvide answers in bullet points.\n</output_format>`,
    fewshot: `### EXAMPLES\n\nUser: Convert "Hello World" to uppercase.\nAssistant: HELLO WORLD\n\nUser: Convert "Laravel Framework" to uppercase.\nAssistant: LARAVEL FRAMEWORK`,
    react: `### REACT PROTOCOL\nFor complex queries, proceed through:\nThought: What do I need to know?\nAction: Define the required lookup or logical step.\nObservation: Evaluate the result.\nFinal Answer: Output the concluded response.`,
    json: `### OUTPUT INSTRUCTION\nYou MUST reply strictly with a valid JSON object. Do not include markdown codeblocks or extra text.\nJSON Schema:\n{\n  "status": "success",\n  "data": {}\n}`
};

// TAB SWITCHING
document.querySelectorAll('.studio-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.studio-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.remove('hidden');
    });
});

// VARIABLE INSERTS
document.querySelectorAll('.btn-var-insert').forEach(btn => {
    btn.addEventListener('click', () => {
        const textarea = document.getElementById('p-prompt');
        const insert = btn.dataset.var;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        textarea.value = textarea.value.substring(0, start) + insert + textarea.value.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + insert.length;
        updatePromptCount();
    });
});

// PATTERN INSERTS
document.querySelectorAll('.template-card').forEach(card => {
    card.addEventListener('click', () => {
        const pat = PROMPT_PATTERNS[card.dataset.template];
        if (!pat) return;
        const textarea = document.getElementById('p-prompt');
        textarea.value = textarea.value ? textarea.value + "\n\n" + pat : pat;
        updatePromptCount();
        toast('Appended prompt pattern!', 'success');
        // Switch back to prompt tab
        document.querySelector('[data-tab="tab-prompt"]').click();
    });
});

// ICON PREVIEW & FILE UPLOAD
function updateIconPreview(val) {
    const preview = document.getElementById('p-icon-preview');
    if (!preview) return;
    val = (val || '🤖').trim();
    if (val.startsWith('http://') || val.startsWith('https://') || val.startsWith('/') || val.startsWith('data:image/')) {
        preview.innerHTML = `<img src="${escapeHtml(val)}" alt="icon" style="width:100%;height:100%;object-fit:cover">`;
    } else {
        preview.textContent = val;
    }
}

document.getElementById('p-icon')?.addEventListener('input', (e) => {
    updateIconPreview(e.target.value);
});

document.getElementById('btn-upload-icon-file')?.addEventListener('click', () => {
    document.getElementById('p-icon-file')?.click();
});

document.getElementById('p-icon-file')?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('icon_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        toast('Uploading GIF/Image icon...', 'info');
        const res = await fetch('/personas/upload-icon', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Upload failed');
        
        document.getElementById('p-icon').value = data.url;
        updateIconPreview(data.url);
        toast('✅ Icon GIF uploaded successfully!', 'success');
    } catch (err) {
        toast('Icon upload failed: ' + err.message, 'error');
    }
});

// OPEN ADD MODAL
document.getElementById('btn-add-persona').addEventListener('click', () => {
    editingPersonaId = null;
    document.getElementById('modal-title').textContent = 'New Persona Studio';
    document.getElementById('p-name').value   = '';
    document.getElementById('p-icon').value   = '🤖';
    updateIconPreview('🤖');
    document.getElementById('p-prompt').value = '';
    document.getElementById('p-color').value  = '#6c63ff';
    document.getElementById('p-doc-list').innerHTML = '';
    document.getElementById('p-doc-count').textContent = '0';
    updatePromptCount();
    setActiveToggle(true);
    document.getElementById('persona-modal').classList.remove('hidden');
    document.getElementById('p-name').focus();
});

// OPEN EDIT MODAL
document.querySelectorAll('.pfc-edit-btn').forEach(btn => {
    btn.addEventListener('click', () => openEditModal(btn.dataset));
});

function openEditModal(d) {
    editingPersonaId = d.id;
    document.getElementById('modal-title').textContent = 'Persona Studio — ' + d.name;
    document.getElementById('p-name').value   = d.name;
    document.getElementById('p-icon').value   = d.icon;
    updateIconPreview(d.icon);
    document.getElementById('p-prompt').value = d.prompt;
    document.getElementById('p-color').value  = d.color;
    updatePromptCount();
    setActiveToggle(d.active === '1');
    loadPersonaDocuments(d.id);
    document.getElementById('persona-modal').classList.remove('hidden');
}

// CLOSE MODAL
document.getElementById('persona-modal-cancel').addEventListener('click', () => {
    document.getElementById('persona-modal').classList.add('hidden');
});

// SAVE PERSONA
document.getElementById('persona-modal-save').addEventListener('click', async () => {
    const name   = document.getElementById('p-name').value.trim();
    const icon   = document.getElementById('p-icon').value.trim() || '🤖';
    const prompt = document.getElementById('p-prompt').value.trim();
    const color  = document.getElementById('p-color').value.trim() || '#6c63ff';

    if (!name) { toast('Name is required', 'error'); return; }
    if (!prompt) { toast('System prompt is required', 'error'); return; }

    const payload = { name, icon, system_prompt: prompt, color, is_active: isPersonaActive };
    const btn = document.getElementById('persona-modal-save');
    btn.textContent = '⏳ Saving...';
    btn.disabled = true;

    try {
        if (editingPersonaId) {
            await api(`/personas/${editingPersonaId}`, { method: 'PUT', body: JSON.stringify(payload) });
            toast('Persona updated!', 'success');
        } else {
            await api('/personas', { method: 'POST', body: JSON.stringify(payload) });
            toast('Persona created!', 'success');
        }
        document.getElementById('persona-modal').classList.add('hidden');
        setTimeout(() => location.reload(), 400);
    } catch (e) {
        toast('Failed: ' + e.message, 'error');
    } finally {
        btn.textContent = '💾 Save Persona Studio';
        btn.disabled = false;
    }
});

// DELETE PERSONA
document.querySelectorAll('.pfc-delete-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        deletePersonaId = btn.dataset.id;
        document.getElementById('delete-persona-modal').classList.remove('hidden');
    });
});

document.getElementById('delete-persona-cancel').addEventListener('click', () => {
    document.getElementById('delete-persona-modal').classList.add('hidden');
});

document.getElementById('delete-persona-confirm').addEventListener('click', async () => {
    if (!deletePersonaId) return;
    try {
        await api(`/personas/${deletePersonaId}`, { method: 'DELETE' });
        toast('Persona deleted', 'success');
        setTimeout(() => location.reload(), 400);
    } catch (e) {
        toast('Failed: ' + e.message, 'error');
    }
});

// TOGGLE ACTIVE
document.getElementById('p-active-toggle').addEventListener('click', () => setActiveToggle(!isPersonaActive));
function setActiveToggle(val) {
    isPersonaActive = val;
    document.getElementById('p-active-toggle').classList.toggle('on', val);
}

// PROMPT CHAR COUNT
document.getElementById('p-prompt').addEventListener('input', updatePromptCount);
function updatePromptCount() {
    const len = document.getElementById('p-prompt').value.length;
    document.getElementById('prompt-char-count').textContent = `${len.toLocaleString()} / 32,768`;
}

// PERSONA RAG DOCUMENTS
async function loadPersonaDocuments(personaId) {
    try {
        const res = await api(`/documents/persona/${personaId}`);
        const list = document.getElementById('p-doc-list');
        list.innerHTML = '';
        const docs = res.documents || [];
        document.getElementById('p-doc-count').textContent = docs.length;

        docs.forEach(doc => {
            const div = document.createElement('div');
            div.className = 'doc-card';
            div.innerHTML = `
                <div class="doc-card-icon">${doc.icon}</div>
                <div class="doc-card-body">
                    <div class="doc-card-name">${doc.name}</div>
                    <div class="doc-card-meta">${doc.size} · ${doc.chunk_count} chunks</div>
                </div>
                <button class="doc-card-del" title="Remove">✕</button>
            `;
            div.querySelector('.doc-card-del').onclick = async () => {
                if (!confirm(`Remove "${doc.name}"?`)) return;
                await api(`/documents/${doc.id}`, { method: 'DELETE' });
                div.remove();
                toast('Document removed', 'success');
            };
            list.appendChild(div);
        });
    } catch (e) {}
}

document.getElementById('p-doc-upload-zone').addEventListener('click', () => {
    if (!editingPersonaId) {
        toast('Please save the persona first before attaching documents.', 'error');
        return;
    }
    document.getElementById('p-file-input').click();
});

document.getElementById('p-file-input').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file || !editingPersonaId) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('persona_id', editingPersonaId);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        toast('Uploading and embedding document...', 'info');
        const res = await fetch('/documents/upload', { method: 'POST', body: formData });
        const data = await res.json();
        if (!data.success) throw new Error(data.error);
        toast(`✅ "${data.document.name}" attached to Persona!`, 'success');
        loadPersonaDocuments(editingPersonaId);
    } catch (err) {
        toast('Upload failed: ' + err.message, 'error');
    }
});

// TEST PROMPT BUTTON
document.getElementById('p-btn-test-run').addEventListener('click', async () => {
    const testMsg = document.getElementById('p-test-input').value.trim() || 'Hello, summarize your capabilities.';
    const prompt  = document.getElementById('p-prompt').value.trim();

    if (!prompt) { toast('System prompt is empty', 'error'); return; }

    const btn = document.getElementById('p-btn-test-run');
    btn.textContent = '⏳ Testing with AI...';
    btn.disabled = true;

    try {
        const res = await api('/settings/test', { method: 'POST' });
        document.getElementById('p-test-result').style.display = 'block';
        document.getElementById('p-test-meta').textContent = `AI Provider Response (${res.model} · ${res.time_ms}ms):`;
        document.getElementById('p-test-output').textContent = res.response;
        toast('Test completed!', 'success');
    } catch (e) {
        toast('Test failed: ' + e.message, 'error');
    } finally {
        btn.textContent = '🔬 Run Test Message';
        btn.disabled = false;
    }
});

// FULLSCREEN TOGGLE
document.getElementById('btn-fullscreen-toggle').addEventListener('click', () => {
    const modal = document.querySelector('#persona-modal .modal');
    modal.classList.toggle('fullscreen');
});

// PERSONA STUDIO LIVE SEARCH
document.getElementById('persona-studio-search')?.addEventListener('input', (e) => {
    const q = e.target.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.persona-full-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name   = card.dataset.name || '';
        const prompt = card.dataset.prompt || '';
        const match  = !q || name.includes(q) || prompt.includes(q);
        card.style.display = match ? 'flex' : 'none';
        if (match) visibleCount++;
    });

    const countLabel = document.getElementById('persona-count-label');
    if (countLabel) {
        countLabel.textContent = q 
            ? `Showing ${visibleCount} of ${cards.length} personas` 
            : `Showing ${cards.length} personas`;
    }
});

// ========== VIEW MODE TOGGLE (LIST / GRID) ==========
const btnViewList  = document.getElementById('btn-view-list');
const btnViewGrid  = document.getElementById('btn-view-grid');
const personasGrid = document.getElementById('personas-grid');

function setViewMode(mode) {
    if (mode === 'grid') {
        personasGrid?.classList.remove('view-list');
        personasGrid?.classList.add('view-grid');
        btnViewList?.classList.remove('active');
        btnViewGrid?.classList.add('active');
    } else {
        personasGrid?.classList.remove('view-grid');
        personasGrid?.classList.add('view-list');
        btnViewGrid?.classList.remove('active');
        btnViewList?.classList.add('active');
    }
    localStorage.setItem('persona_view_mode', mode);
}

btnViewList?.addEventListener('click', () => setViewMode('list'));
btnViewGrid?.addEventListener('click', () => setViewMode('grid'));

// Load preferred view mode on startup
const savedViewMode = localStorage.getItem('persona_view_mode') || 'list';
setViewMode(savedViewMode);

</script>
@endpush
@endsection
