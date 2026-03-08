(function () {
    const config = window.theLogicalThemeBlockAvailability || {};
    let initialized = false;
    const debugStore = {
        startedAt: new Date().toISOString(),
        logs: [],
        cards: {},
        errors: [],
    };

    function pushDebug(type, message, details = {}) {
        const entry = {
            timestamp: new Date().toISOString(),
            type,
            message,
            details,
        };

        debugStore.logs.push(entry);

        if (config.debug) {
            console.log('[TLT Block Availability]', message, details);
        }

        return entry;
    }

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

    function getCardKey(card) {
        return card.getAttribute('data-category') || 'unknown';
    }

    function snapshotCard(elements) {
        if (!elements.card || !elements.list) {
            return null;
        }

        const cardKey = getCardKey(elements.card);
        const items = getItems(elements.list);
        const snapshot = {
            cardKey,
            searchValue: elements.search ? elements.search.value : '',
            listDisabled: elements.list.hasAttribute('disabled'),
            totalItems: items.length,
            visibleItems: items.filter((item) => !item.hidden).length,
            checkedItems: items.filter((item) => {
                const input = getItemInput(item);
                return input ? input.checked : false;
            }).length,
            emptyStateHidden: elements.emptyState ? elements.emptyState.hidden : null,
            toggleChecked: elements.toggle ? elements.toggle.checked : null,
            items: items.map((item) => {
                const input = getItemInput(item);

                return {
                    text: getSearchText(item),
                    hidden: item.hidden,
                    checked: input ? input.checked : null,
                };
            }),
        };

        debugStore.cards[cardKey] = snapshot;

        return snapshot;
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
        pushDebug('refreshSearch', 'Search refreshed', snapshotCard(elements) || {});
    }

    function refreshOrder(elements) {
        if (!elements.list) {
            return;
        }

        sortItems(elements.list);
        refreshSearch(elements);
        pushDebug('refreshOrder', 'Items reordered', snapshotCard(elements) || {});
    }

    function bindSearch(elements) {
        if (!elements.search) {
            pushDebug('bindSearch', 'Search input missing for card', {
                cardKey: elements.card ? getCardKey(elements.card) : 'unknown',
            });
            return;
        }

        elements.search.addEventListener('input', () => {
            pushDebug('input', 'Search input event', {
                cardKey: getCardKey(elements.card),
                value: elements.search.value,
            });
            refreshSearch(elements);
        });

        elements.search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                pushDebug('keydown', 'Prevented Enter on search input', {
                    cardKey: getCardKey(elements.card),
                });
            }
        });
    }

    function bindToggle(elements) {
        if (!elements.toggle) {
            return;
        }

        elements.toggle.addEventListener('change', () => {
            pushDebug('toggle', 'Category toggle changed', {
                cardKey: getCardKey(elements.card),
                checked: elements.toggle.checked,
            });
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
                pushDebug('checkbox', 'Block checkbox changed', {
                    cardKey: getCardKey(elements.card),
                    block: getSearchText(item),
                    checked: input.checked,
                });
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
        pushDebug('initCard', 'Card initialized', snapshotCard(elements) || {});

        card.dataset.searchReady = 'true';
    }

    function init(root = document) {
        getCards(root).forEach((card) => {
            initCard(card);
        });
    }

    function buildReport() {
        return {
            startedAt: debugStore.startedAt,
            initialized,
            config,
            cardCount: getCards(document).length,
            cards: debugStore.cards,
            errors: debugStore.errors,
            logs: debugStore.logs,
        };
    }

    function installGlobalDebugApi() {
        window.theLogicalThemeBlockAvailabilityDebug = {
            report() {
                const report = buildReport();
                console.groupCollapsed('[TLT Block Availability] Debug report');
                console.log(report);
                console.groupEnd();
                return report;
            },
            cards() {
                return debugStore.cards;
            },
            logs() {
                return debugStore.logs;
            },
            errors() {
                return debugStore.errors;
            },
            rerun() {
                pushDebug('manual', 'Manual reinitialization requested');
                init(document);
                return buildReport();
            },
        };

        window.addEventListener('error', (event) => {
            debugStore.errors.push({
                type: 'error',
                message: event.message,
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno,
            });
        });

        window.addEventListener('unhandledrejection', (event) => {
            debugStore.errors.push({
                type: 'unhandledrejection',
                reason: String(event.reason),
            });
        });

        pushDebug('debug', 'Debug API installed', {
            api: 'window.theLogicalThemeBlockAvailabilityDebug',
        });
    }

    window.theLogicalThemeInitBlockAvailabilitySearch = function () {
        pushDebug('bootstrap', 'Inline bootstrap requested');
        init(document);
    };

    installGlobalDebugApi();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!initialized) {
                init(document);
                initialized = true;
                pushDebug('dom', 'Initialized on DOMContentLoaded', buildReport());
            }
        });

        return;
    }

    init(document);
    initialized = true;
    pushDebug('dom', 'Initialized immediately', buildReport());
})();
