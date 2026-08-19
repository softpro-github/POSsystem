<style>
    :root {
        /* Accent (amber) is identical in both themes — brand color doesn't flip. */
        --color-accent-50: 255 251 235;
        --color-accent-100: 254 243 199;
        --color-accent-200: 253 230 138;
        --color-accent-300: 252 211 77;
        --color-accent-400: 251 191 36;
        --color-accent-500: 245 158 11;
        --color-accent-600: 217 119 6;
        --color-accent-700: 180 83 9;
        --color-accent-800: 146 64 14;
        --color-accent-900: 120 53 15;
        --color-accent-950: 69 26 3;
    }

    [data-theme="dark"] {
        --color-surface: 9 9 11;
        --color-surface-raised: 24 24 27;
        --color-surface-hover: 39 39 42;
        --color-border: 39 39 42;
        --color-border-strong: 63 63 70;
        --color-ink: 244 244 245;
        --color-ink-muted: 161 161 170;
        --color-ink-subtle: 113 113 122;
        --shadow-card: inset 0 1px 0 0 rgba(255, 255, 255, 0.04), 0 1px 2px 0 rgba(0, 0, 0, 0.4);
    }

    [data-theme="light"] {
        --color-surface: 250 250 250;
        --color-surface-raised: 255 255 255;
        --color-surface-hover: 244 244 245;
        --color-border: 228 228 231;
        --color-border-strong: 212 212 216;
        --color-ink: 24 24 27;
        --color-ink-muted: 113 113 122;
        --color-ink-subtle: 161 161 170;
        --shadow-card: 0 1px 2px 0 rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.02);
    }
</style>
<script>
    (function () {
        var STORAGE_KEY = 'gadgetstore-theme';
        var LEGACY_KEY = 'gadgetstore-auth-theme';

        function systemPrefersDark() {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        function resolve(mode) {
            return mode === 'auto' ? (systemPrefersDark() ? 'dark' : 'light') : mode;
        }

        function apply(mode) {
            document.documentElement.setAttribute('data-theme', resolve(mode));
            document.documentElement.setAttribute('data-theme-mode', mode);
        }

        var stored = localStorage.getItem(STORAGE_KEY);
        if (!stored) {
            var legacy = localStorage.getItem(LEGACY_KEY);
            stored = legacy || 'dark';
            localStorage.setItem(STORAGE_KEY, stored);
            if (legacy) localStorage.removeItem(LEGACY_KEY);
        }

        apply(stored);
    })();
</script>
