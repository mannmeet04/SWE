<?php
?>
<div id="accessibility-btn-container" class="accessibility-btn-container">
    <button id="accessibility-btn" class="accessibility-btn" title="Barrierefreiheit">
        <i class="fas fa-universal-access"></i>
    </button>
</div>

<div id="accessibility-menu" class="accessibility-menu">
    <div class="accessibility-header">
        <h3><i class="fas fa-universal-access"></i> Barrierefreiheit</h3>
        <button class="accessibility-close" onclick="document.getElementById('accessibility-menu').classList.remove('show');">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="accessibility-content">
        <div class="accessibility-item">
            <label for="font-size-slider">
                <i class="fas fa-text-height"></i> Schriftgröße: <span id="font-size-display">110%</span>
            </label>
            <input type="range" id="font-size-slider" min="0" max="7" value="3" class="font-size-slider">
            <div class="slider-labels">
                <span>80%</span>
                <span>150%</span>
            </div>
        </div>
        <div class="accessibility-item">
            <label for="dark-mode-toggle" class="toggle-label">
                <i class="fas fa-moon"></i> Dunkler Modus
            </label>
            <label class="toggle-switch">
                <input type="checkbox" id="dark-mode-toggle">
                <span class="slider"></span>
            </label>
        </div>
        <div class="accessibility-item">
            <button class="reset-btn" onclick="resetAccessibilitySettings()">
                <i class="fas fa-redo"></i> Zurücksetzen
            </button>
        </div>
    </div>
</div>

