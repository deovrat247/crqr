// Alpine component for the QR generator page: live server-rendered preview + contrast hint.

// The field that must be filled before a preview makes sense, per type.
const REQUIRED_FIELD = {
    url: 'url',
    text: 'text',
    wifi: 'wifi_ssid',
    email: 'email_to',
    phone: 'phone',
    sms: 'phone',
};

export default (initial) => ({
    ...initial,
    previewUrl: null,
    error: null,
    loading: false,
    request: null,

    init() {
        this.refresh();
    },

    get lowContrast() {
        const fg = luminance(this.fg);
        const bg = luminance(this.bg);

        if (fg === null || bg === null) {
            return false;
        }

        // Scanners expect dark modules on a light background.
        return fg > bg || (bg + 0.05) / (fg + 0.05) < 3;
    },

    hasContent() {
        const field = this.$refs.form.elements[REQUIRED_FIELD[this.type]];

        return field && field.value.trim() !== '';
    },

    async refresh() {
        this.request?.abort();

        if (!this.hasContent()) {
            this.setPreview(null);
            this.error = null;
            return;
        }

        const request = (this.request = new AbortController());
        this.loading = true;

        try {
            const response = await fetch(this.$refs.form.dataset.previewUrl, {
                method: 'POST',
                body: new FormData(this.$refs.form),
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });

            if (response.ok) {
                this.setPreview(URL.createObjectURL(await response.blob()));
                this.error = null;
            } else if (response.status === 422) {
                const { errors } = await response.json();
                this.error = Object.values(errors)[0][0];
            } else if (response.status === 419) {
                this.error = 'Your session expired. Reload the page.';
            } else if (response.status === 429) {
                this.error = 'Too many changes at once. Wait a moment and try again.';
            } else {
                this.error = 'Could not create the QR code. Try again.';
            }
        } catch (e) {
            if (e.name === 'AbortError') {
                return;
            }
            this.error = 'Could not reach the server. Check your connection.';
        }

        if (this.request === request) {
            this.loading = false;
        }
    },

    setPreview(url) {
        if (this.previewUrl) {
            URL.revokeObjectURL(this.previewUrl);
        }
        this.previewUrl = url;
    },
});

// WCAG relative luminance of a #rrggbb colour, or null if it is not valid yet.
function luminance(hex) {
    const match = /^#([0-9a-f]{6})$/i.exec(hex ?? '');

    if (!match) {
        return null;
    }

    const [r, g, b] = [0, 2, 4].map((i) => {
        const c = parseInt(match[1].slice(i, i + 2), 16) / 255;
        return c <= 0.03928 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
    });

    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}
