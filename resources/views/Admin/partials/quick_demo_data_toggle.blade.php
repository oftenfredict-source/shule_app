@php
    $toggleId = $toggleId ?? 'qeWithDemoData';
@endphp
@once
<style>
    .qe-demo-data-toggle-wrap {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px;
        margin-bottom: 1rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0;
        overflow: hidden;
        max-width: 100%;
        box-sizing: border-box;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        min-width: 44px;
        height: 24px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        border-radius: 24px;
        transition: background-color 0.2s;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: #fff;
        border-radius: 50%;
        transition: transform 0.2s;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch input:checked + .qe-demo-switch-slider {
        background-color: #940000;
    }
    .qe-demo-data-toggle-wrap .qe-demo-switch input:checked + .qe-demo-switch-slider:before {
        transform: translateX(20px);
    }
    .qe-demo-data-toggle-wrap .qe-demo-toggle-copy {
        flex: 1;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
    }
    .qe-demo-data-toggle-wrap .qe-demo-toggle-copy .qe-demo-title {
        display: block;
        font-weight: 700;
        color: #212529;
        margin-bottom: 2px;
    }
    .qe-demo-data-toggle-wrap .qe-demo-toggle-copy .qe-demo-sub {
        display: block;
        font-size: 0.8125rem;
        color: #6c757d;
        font-weight: 400;
    }
    #quickTemplateDownloadModal .modal-dialog {
        max-width: 420px;
        width: calc(100% - 1.5rem);
        margin-left: auto;
        margin-right: auto;
    }
    #quickTemplateDownloadModal .modal-body {
        overflow-x: hidden;
    }
</style>
@endonce
<div class="qe-demo-data-toggle-wrap">
    <label class="qe-demo-switch" for="{{ $toggleId }}" title="With demo data">
        <input class="qe-demo-data-toggle" type="checkbox" id="{{ $toggleId }}" value="1">
        <span class="qe-demo-switch-slider" aria-hidden="true"></span>
    </label>
    <div class="qe-demo-toggle-copy">
        <label class="qe-demo-title mb-0" for="{{ $toggleId }}" style="cursor:pointer;">With demo data</label>
        <span class="qe-demo-sub">2,000 sample students (Tanzanian names, Male &amp; Female)</span>
    </div>
</div>
