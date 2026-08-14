{{-- Reusable model selector: clean segmented mode (Preset list vs Custom ID) + fetch button --}}
{{-- Variables: $fieldId, $currentVal, $staticOpts (array), $provider --}}

@php
    $isCustomValue = !empty($currentVal) && !in_array($currentVal, $staticOpts);
@endphp

<div class="form-group model-selector-component" id="component-{{ $fieldId }}" data-field="{{ $fieldId }}">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
        <label class="form-label" style="margin-bottom:0">Model Selection</label>
        <div class="model-mode-toggle-pills">
            <button type="button" class="model-toggle-pill {{ !$isCustomValue ? 'active' : '' }}" data-target="{{ $fieldId }}" data-mode="preset">
                📋 Verified Presets
            </button>
            <button type="button" class="model-toggle-pill {{ $isCustomValue ? 'active' : '' }}" data-target="{{ $fieldId }}" data-mode="custom">
                ✏️ Custom Model ID
            </button>
        </div>
    </div>

    <!-- Mode 1: Preset Dropdown -->
    <div class="model-mode-view model-mode-preset {{ $isCustomValue ? 'hidden' : '' }}" id="mode-preset-{{ $fieldId }}">
        <div class="model-row">
            <select id="{{ $fieldId }}" class="form-select" style="flex:1;font-family:var(--font-mono);font-size:13px">
                @foreach($staticOpts as $opt)
                    <option value="{{ $opt }}" {{ $currentVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
                @if($isCustomValue)
                    <option value="{{ $currentVal }}" selected>{{ $currentVal }} (Custom)</option>
                @endif
            </select>
            <button type="button"
                    class="btn btn-ghost btn-fetch-models"
                    data-provider="{{ $provider }}"
                    data-select="{{ $fieldId }}"
                    style="font-size:12px;padding:8px 14px;white-space:nowrap;font-weight:600"
                    title="Fetch live models directly from {{ $provider }}">
                🔄 Fetch Live
            </button>
        </div>
        <div class="field-hint" style="margin-top:6px">Select from verified models or click Fetch Live to query your provider.</div>
    </div>

    <!-- Mode 2: Custom Text Input -->
    <div class="model-mode-view model-mode-custom {{ !$isCustomValue ? 'hidden' : '' }}" id="mode-custom-{{ $fieldId }}">
        <div class="custom-model-input-wrap">
            <span class="custom-model-prefix-icon">🎯</span>
            <input type="text"
                   id="{{ $fieldId }}_manual"
                   class="form-input custom-model-styled-input"
                   value="{{ $isCustomValue ? $currentVal : '' }}"
                   placeholder="e.g. {{ $provider === 'openrouter' ? 'openrouter/free or meta-llama/llama-3.3-70b-instruct:free' : ($provider === 'openai' ? 'gpt-4o-2024-11-20' : 'model-identifier') }}"
                   autocomplete="off"
                   spellcheck="false">
        </div>
        <div class="field-hint" style="margin-top:6px">Type any exact model string supported by {{ ucfirst($provider) }} (e.g. fine-tunes or beta models).</div>
    </div>
</div>
