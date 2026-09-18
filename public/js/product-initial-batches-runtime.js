document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('initial-batches-wrapper');
            const template = document.getElementById('initial-batch-template');

            if (!wrapper || !template) return;

            const defaultReceived = wrapper.dataset.defaultReceived || '';

            function rows() {
                return Array.from(wrapper.querySelectorAll('.initial-batch-row'));
            }

            function hasUserData(row) {
                const quantity = row.querySelector('[data-name="quantity"]')?.value || '';
                const batchNumber = row.querySelector('[data-name="batch_number"]')?.value || '';
                const expiresAt = row.querySelector('[data-name="expires_at"]')?.value || '';

                return quantity.trim() !== '' || batchNumber.trim() !== '' || expiresAt.trim() !== '';
            }

            function reindex() {
                rows().forEach((row, index) => {
                    row.querySelectorAll('[data-name]').forEach((field) => {
                        field.name = `initial_batches[${index}][${field.dataset.name}]`;

                        if (field.dataset.name === 'received_at' && !field.value) {
                            field.value = defaultReceived;
                        }
                    });
                });
            }

            function bind(row) {
                row.querySelectorAll('.initial-batch-trigger').forEach((field) => {
                    if (field.dataset.bound === '1') return;
                    field.dataset.bound = '1';
                    field.addEventListener('input', ensureEmptyRow);
                    field.addEventListener('change', ensureEmptyRow);
                });

                const remove = row.querySelector('.initial-batch-remove');

                if (remove && remove.dataset.bound !== '1') {
                    remove.dataset.bound = '1';

                    remove.addEventListener('click', function () {
                        if (rows().length <= 1) {
                            row.querySelectorAll('[data-name]').forEach((field) => {
                                field.value = field.dataset.name === 'received_at' ? defaultReceived : '';
                            });
                        } else {
                            row.remove();
                        }

                        reindex();
                        ensureEmptyRow();
                    });
                }
            }

            function bindAll() {
                rows().forEach(bind);
            }

            function addRow() {
                const clone = template.content.cloneNode(true);
                const row = clone.querySelector('.initial-batch-row');
                row.querySelector('[data-name="received_at"]').value = defaultReceived;
                wrapper.appendChild(clone);
                reindex();
                bindAll();
            }

            function ensureEmptyRow() {
                const currentRows = rows();
                const last = currentRows[currentRows.length - 1];

                if (last && hasUserData(last)) {
                    addRow();
                }
            }

            bindAll();
            reindex();
            ensureEmptyRow();
        });
