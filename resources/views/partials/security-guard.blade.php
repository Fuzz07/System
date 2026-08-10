<script>
(function() {
    // Immediate Inline Security Guard: Block Right-Click and DevTools Shortcuts
    
    // 1. Block Context Menu (Right-Click)
    function disableContextMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        return false;
    }
    document.addEventListener('contextmenu', disableContextMenu, true);
    window.addEventListener('contextmenu', disableContextMenu, true);

    // 2. Block DevTools Keyboard Shortcuts
    function disableDevToolsShortcuts(e) {
        if (!e) return;

        var key = (e.key || '').toLowerCase();
        var code = (e.code || '').toLowerCase();
        var keyCode = e.keyCode || e.which;

        // F12 Key
        if (key === 'f12' || code === 'f12' || keyCode === 123) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl+Shift+I / J / C / K (Inspect / Console / Selector / Network)
        if (e.ctrlKey && e.shiftKey && (['i', 'j', 'c', 'k'].indexOf(key) !== -1 || ['keyi', 'keyj', 'keyc', 'keyk'].indexOf(code) !== -1)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Cmd+Option+I / J / C / K (macOS DevTools shortcuts)
        if (e.metaKey && e.altKey && (['i', 'j', 'c', 'k'].indexOf(key) !== -1 || ['keyi', 'keyj', 'keyc', 'keyk'].indexOf(code) !== -1)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl+U or Cmd+U (View Source)
        if ((e.ctrlKey || e.metaKey) && (key === 'u' || code === 'keyu')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        // Ctrl+S or Cmd+S (Save Page)
        if ((e.ctrlKey || e.metaKey) && (key === 's' || code === 'keys')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }

    document.addEventListener('keydown', disableDevToolsShortcuts, true);
    window.addEventListener('keydown', disableDevToolsShortcuts, true);
})();
</script>
