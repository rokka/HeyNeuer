import Chart from 'chart.js/auto';
import { Html5Qrcode } from 'html5-qrcode';

// Globale Verfügbarkeit für Alpine-Inline-Komponenten
window.Html5Qrcode = Html5Qrcode;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('dashboardChart', (labels, data) => ({
        chart: null,
        render() {
            this.chart = new Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Angelegt',
                            data,
                            backgroundColor: '#4f46e5',
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },
                },
            });
        },
    }));

    // QR-Scanner-Komponente für die Computernummer.
    // Verwendung im Blade:
    //   <div x-data="qrScanner" ...>
    //       <button @click="open()">Scannen</button>
    //       <div x-show="active" x-ref="reader" id="qr-reader" class="..."></div>
    //   </div>
    // Erfolgreicher Scan löst CustomEvent('qr-scanned', detail: { code }) aus.
    window.Alpine.data('qrScanner', () => ({
        scanner: null,
        active: false,
        error: null,
        async open() {
            this.error = null;
            this.active = true;
            await this.$nextTick();
            try {
                this.scanner = new window.Html5Qrcode(this.$refs.reader.id);
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => {
                        this.$dispatch('qr-scanned', { code: this.extractCode(decodedText) });
                        this.close();
                    },
                    () => { /* per-frame errors ignorieren */ },
                );
            } catch (e) {
                this.error = 'Kamera konnte nicht gestartet werden: ' + (e?.message || e);
                this.active = false;
            }
        },
        // Extrahiert die Computernummer aus dem gescannten Wert. Steckt hinter dem
        // Barcode ein Link (z. B. https://www.heyneuer.com/HA-E-3005), wird nur das
        // letzte Pfadsegment übernommen; ansonsten der Wert unverändert.
        extractCode(decodedText) {
            const value = (decodedText || '').trim();
            try {
                const url = new URL(value);
                const segment = url.pathname.split('/').filter(Boolean).pop();
                return segment || value;
            } catch (e) {
                return value;
            }
        },
        async close() {
            if (this.scanner) {
                try { await this.scanner.stop(); } catch (e) { /* ignore */ }
                try { this.scanner.clear(); } catch (e) { /* ignore */ }
                this.scanner = null;
            }
            this.active = false;
        },
    }));
});
