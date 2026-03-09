(function () {
    const config = window.theLogicalThemeBlockAvailability || {};
    let initialized = false;

    function formatCount(activeCount, totalCount) {
        return activeCount + ' / ' + totalCount + ' ' + (config.activeLabel || 'active');
    }

    function getCards(root) {
        return Array.from(root.querySelectorAll('[data-role="block-card"]'));
    }

    function getElements(card) {
        return {
            card,
            list: card.querySelector('[data-role="block-list"]'),
            search: card.querySelector('[data-role="block-search"]'),
            toggle: card.querySelector('[data-role="category-toggle"]'),
            count: card.querySelector('[data-role="block-count"]'),
            emptyState: card.querySelector('[data-role="empty-state"]'),
        };
    }

    function getItems(list) {
        if (!list) {
            return [];
        }

        return Array.from(list.querySelectorAll('[data-role="block-item"]'));
    }

    function getItemInput(item) {
        return item.querySelector('input[type="checkbox"]');
    }

    function getSearchText(item) {
        const textNode = item.querySelector('[data-role="search-text"]');
        return (textNode ? textNode.textContent : item.textContent || '').trim().toLowerCase();
    }

    function sortItems(list) {
        const items = getItems(list);

        items.sort((leftItem, rightItem) => {
            const leftInput = getItemInput(leftItem);
            const rightInput = getItemInput(rightItem);
            const leftChecked = leftInput ? leftInput.checked : false;
            const rightChecked = rightInput ? rightInput.checked : false;

            if (leftChecked !== rightChecked) {
                return leftChecked ? -1 : 1;
            }

            return getSearchText(leftItem).localeCompare(getSearchText(rightItem));
        });

        items.forEach((item) => {
            list.appendChild(item);
        });
    }

    function updateListEnabledState(elements) {
        if (!elements.list) {
            return;
        }

        if (!elements.toggle || elements.toggle.checked) {
            elements.list.removeAttribute('disabled');
            return;
        }

        elements.list.setAttribute('disabled', 'disabled');
    }

    function updateCount(elements) {
        if (!elements.count || !elements.list) {
            return;
        }

        const items = getItems(elements.list);
        const activeCount = items.filter((item) => {
            const input = getItemInput(item);
            return input ? input.checked : false;
        }).length;

        elements.count.textContent = formatCount(activeCount, items.length);
    }

    function filterItems(elements) {
        if (!elements.list) {
            return;
        }

        const query = elements.search ? elements.search.value.trim().toLowerCase() : '';
        let visibleCount = 0;

        getItems(elements.list).forEach((item) => {
            const matches = query === '' || getSearchText(item).includes(query);
            item.hidden = !matches;

            if (matches) {
                visibleCount += 1;
            }
        });

        if (elements.emptyState) {
            elements.emptyState.hidden = visibleCount > 0;
        }
    }

    function refreshSearch(elements) {
        filterItems(elements);
        updateCount(elements);
        updateListEnabledState(elements);
    }

    function refreshOrder(elements) {
        if (!elements.list) {
            return;
        }

        sortItems(elements.list);
        refreshSearch(elements);
    }

    function bindSearch(elements) {
        if (!elements.search) {
            return;
        }

        elements.search.addEventListener('input', () => {
            refreshSearch(elements);
        });

        elements.search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    }

    function bindToggle(elements) {
        if (!elements.toggle) {
            return;
        }

        elements.toggle.addEventListener('change', () => {
            refreshSearch(elements);
        });
    }

    function bindInputs(elements) {
        if (!elements.list) {
            return;
        }

        getItems(elements.list).forEach((item) => {
            const input = getItemInput(item);

            if (!input) {
                return;
            }

            input.addEventListener('change', () => {
                refreshOrder(elements);
            });
        });
    }

    function initCard(card) {
        if (card.dataset.searchReady === 'true') {
            return;
        }

        const elements = getElements(card);

        bindSearch(elements);
        bindToggle(elements);
        bindInputs(elements);
        refreshOrder(elements);

        card.dataset.searchReady = 'true';
    }

    function init(root = document) {
        getCards(root).forEach((card) => {
            initCard(card);
        });
    }

    window.theLogicalThemeInitBlockAvailabilitySearch = function () {
        init(document);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!initialized) {
                init(document);
                initialized = true;
            }
        });

        return;
    }

    init(document);
    initialized = true;
})();
