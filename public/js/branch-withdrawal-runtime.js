document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('branch-withdrawal-items');
        const template = document.getElementById('branch-item-template');

        if (!wrapper || !template) return;

        function rows() {
            return Array.from(wrapper.querySelectorAll('[data-item-row]'));
        }

        function fields(row) {
            return {
                product: row.querySelector('select[name*="[product_id]"], select[data-name="product_id"]'),
                quantity: row.querySelector('input[name*="[quantity]"], input[data-name="quantity"]'),
            };
        }

        function complete(row) {
            const current = fields(row);
            return Boolean(current.product?.value && current.quantity?.value);
        }

        function hasData(row) {
            const current = fields(row);
            return Boolean(current.product?.value || current.quantity?.value);
        }

        function reindex() {
            rows().forEach((row, index) => {
                const current = fields(row);
                const position = row.querySelector('[data-position]');

                if (current.product) {
                    current.product.name = `items[${index}][product_id]`;
                    current.product.setAttribute('aria-label', `Produkt Position ${index + 1}`);
                }

                if (current.quantity) {
                    current.quantity.name = `items[${index}][quantity]`;
                    current.quantity.setAttribute('aria-label', `Menge Position ${index + 1}`);
                }

                if (position) position.textContent = String(index + 1);
            });
        }

        function focusProduct(row) {
            window.setTimeout(function () {
                const product = fields(row).product;
                if (!product) return;

                if (product.tomselect) {
                    product.tomselect.focus();
                    product.tomselect.open();
                } else {
                    product.focus();
                }
            }, 80);
        }

        function addRow() {
            const fragment = template.content.cloneNode(true);
            const row = fragment.querySelector('[data-item-row]');
            wrapper.appendChild(fragment);
            reindex();
            bind();
            window.initSearchableSelects?.();
            return row;
        }

        function ensureTrailingRow() {
            const currentRows = rows();
            const last = currentRows[currentRows.length - 1];

            if (!last || complete(last)) {
                return addRow();
            }

            return last;
        }

        function removeExtraEmptyRows() {
            const currentRows = rows();

            currentRows.forEach((row, index) => {
                const isLast = index === currentRows.length - 1;

                if (!isLast && !hasData(row) && currentRows.length > 1) {
                    row.querySelector('select')?.tomselect?.destroy();
                    row.remove();
                }
            });

            reindex();
        }

        function prepareNextRow() {
            ensureTrailingRow();
            removeExtraEmptyRows();
        }

        function nextRowAfter(row) {
            const currentRows = rows();
            const index = currentRows.indexOf(row);
            return index >= 0 ? currentRows[index + 1] : null;
        }

        function bind() {
            rows().forEach((row) => {
                const current = fields(row);
                const removeButton = row.querySelector('.remove-branch-item');

                if (current.product && current.product.dataset.branchBound !== '1') {
                    current.product.dataset.branchBound = '1';
                    current.product.addEventListener('change', prepareNextRow);
                }

                if (current.quantity && current.quantity.dataset.branchBound !== '1') {
                    current.quantity.dataset.branchBound = '1';

                    current.quantity.addEventListener('input', function () {
                        prepareNextRow();
                    });

                    current.quantity.addEventListener('change', function () {
                        prepareNextRow();
                    });

                    current.quantity.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' || !complete(row)) return;

                        event.preventDefault();
                        prepareNextRow();

                        const next = nextRowAfter(row);
                        if (next) focusProduct(next);
                    });
                }

                if (removeButton && removeButton.dataset.branchBound !== '1') {
                    removeButton.dataset.branchBound = '1';

                    removeButton.addEventListener('click', function () {
                        if (rows().length === 1) {
                            current.product?.tomselect?.clear();
                            if (current.product) current.product.value = '';
                            if (current.quantity) current.quantity.value = '';
                            return;
                        }

                        current.product?.tomselect?.destroy();
                        row.remove();
                        reindex();
                        ensureTrailingRow();
                        removeExtraEmptyRows();
                    });
                }
            });
        }

        bind();
        reindex();
        ensureTrailingRow();
        removeExtraEmptyRows();
    });
