{{-- Reusable model selector: dropdown + manual override input + fetch button --}}
{{-- Variables: $fieldId, $currentVal, $staticOpts (array), $provider --}}

<div class="form-group">
    <label class="form-label">Model</label>
    <div class="model-field-wrap">
        {{-- Dropdown with static / fetched options --}}
        <div class="model-row">
            <select id="{{ $fieldId }}" class="form-select" style="flex:1">
                @foreach($staticOpts as $opt)
                    <option value="{{ $opt }}" {{ $currentVal === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                @endforeach
                {{-- If current value is not in the list, add it --}}
                @if(!in_array($currentVal, $staticOpts) && $currentVal)
                    <option value="{{ $currentVal }}" selected>{{ $currentVal }}</option>
                @endif
            </select>
            <button type="button"
                    class="btn btn-ghost btn-fetch-models"
                    data-provider="{{ $provider }}"
                    data-select="{{ $fieldId }}"
                    style="font-size:12px;padding:8px 12px;white-space:nowrap"
                    title="Fetch available models from {{ $provider }}">
                🔄 Fetch
            </button>
        </div>

        {{-- Manual override --}}
        <div class="model-manual-row">
            <span class="model-manual-label">✏️ Custom model:</span>
            <input type="text"
                   id="{{ $fieldId }}_manual"
                   class="model-manual-input"
                   placeholder="e.g. gpt-4o-2024-11-20 — overrides dropdown"
                   autocomplete="off"
                   spellcheck="false">
        </div>
        <div class="field-hint" style="margin-top:0">
            Leave custom field empty to use the dropdown selection.
        </div>
    </div>
</div>
