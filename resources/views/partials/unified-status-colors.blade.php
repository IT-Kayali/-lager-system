<style>
    .status-unified-open,
    .status-unified-offer { background:#f3f4f6 !important; color:#374151 !important; }
    .status-unified-progress { background:#dbeafe !important; color:#1d4ed8 !important; }
    .status-unified-ready { background:#fef3c7 !important; color:#b45309 !important; }
    .status-unified-issued,
    .status-unified-completed { background:#dcfce7 !important; color:#166534 !important; }
    .status-unified-cancelled,
    .status-unified-expired { background:#fee2e2 !important; color:#991b1b !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusClasses = {
            'offen': 'status-unified-open',
            'angebot': 'status-unified-offer',
            'in bearbeitung': 'status-unified-progress',
            'abholbereit': 'status-unified-ready',
            'ausgegeben': 'status-unified-issued',
            'erledigt': 'status-unified-completed',
            'storniert': 'status-unified-cancelled',
            'reservierung abgelaufen': 'status-unified-expired',
        };

        const unifiedClasses = [
            'status-unified-open',
            'status-unified-offer',
            'status-unified-progress',
            'status-unified-ready',
            'status-unified-issued',
            'status-unified-completed',
            'status-unified-cancelled',
            'status-unified-expired',
        ];

        const applyStatusColors = (root = document) => {
            const elements = root.matches?.('.offer-status-pill, .warehouse-status-badge, .branch-status-badge, .premium-badge')
                ? [root]
                : root.querySelectorAll?.('.offer-status-pill, .warehouse-status-badge, .branch-status-badge, .premium-badge') ?? [];

            elements.forEach((badge) => {
                const text = badge.textContent
                    .trim()
                    .toLocaleLowerCase('de-DE')
                    .replace(/^status:\s*/, '');
                const statusClass = statusClasses[text];

                if (!statusClass) {
                    return;
                }

                badge.classList.remove(...unifiedClasses);
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
