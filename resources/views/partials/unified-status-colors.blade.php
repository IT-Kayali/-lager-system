<style>
    .status-unified-offer { background:#f3f4f6 !important; color:#374151 !important; }
    .status-unified-progress { background:#dbeafe !important; color:#1d4ed8 !important; }
    .status-unified-ready { background:#fef3c7 !important; color:#b45309 !important; }
    .status-unified-completed { background:#dcfce7 !important; color:#166534 !important; }
    .status-unified-cancelled,
    .status-unified-expired { background:#fee2e2 !important; color:#991b1b !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusClasses = {
            'angebot': 'status-unified-offer',
            'in bearbeitung': 'status-unified-progress',
            'abholbereit': 'status-unified-ready',
            'erledigt': 'status-unified-completed',
            'storniert': 'status-unified-cancelled',
            'reservierung abgelaufen': 'status-unified-expired',
        };

        const applyStatusColors = (root = document) => {
            root.querySelectorAll('.offer-status-pill, .warehouse-status-badge, .premium-badge').forEach((badge) => {
                const text = badge.textContent.trim().toLocaleLowerCase('de-DE');
                const statusClass = statusClasses[text];

                if (!statusClass) {
                    return;
                }

                badge.classList.remove(
                    'status-unified-offer',
                    'status-unified-progress',
                    'status-unified-ready',
                    'status-unified-completed',
                    'status-unified-cancelled',
                    'status-unified-expired'
                );
                badge.classList.add(statusClass);
            });
        };

        applyStatusColors();

        new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        applyStatusColors(node);
                    }
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    });
</script>
