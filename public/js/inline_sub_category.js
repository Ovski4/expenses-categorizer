/**
 * Inline sub category assignment on the transaction list.
 *
 * Progressive enhancement: every uncategorized cell already contains a working
 * form. This module hides its submit button and turns a change on the select
 * into a fetch, so a whole page of transactions can be categorized without a
 * reload. If this module fails to load or throws, the forms keep working.
 */

const SAVED_MARKER_DURATION = 2000;

const getLabels = () => {
    const table = document.getElementById('transaction-list');

    return {
        saved: (table && table.dataset.savedLabel) || 'saved',
        error: (table && table.dataset.errorLabel) || 'Could not save the sub category',
    };
};

/**
 * The selects of every row still waiting to be categorized, in document order.
 */
const remainingSelects = () => Array.from(
    document.querySelectorAll('form.inline-sub-category select')
);

/**
 * Removing the form removes the focused element, which would drop focus to
 * <body> and break the tab chain. Hand focus to the next row still needing work.
 */
const moveFocusToNextRow = (select) => {
    const selects = remainingSelects();
    const next = selects[selects.indexOf(select) + 1] || selects[selects.indexOf(select) - 1];

    if (next) {
        next.focus();
    }
};

const createMarker = (className, text) => {
    const marker = document.createElement('span');
    marker.className = className;
    // textContent, never innerHTML: labels and category names are external input.
    marker.textContent = text;
    // The marker is truncated to keep the column width stable, so the full
    // message has to stay reachable on hover.
    marker.title = text;

    return marker;
};

const showError = (form, select, message) => {
    clearMarkers(form.parentNode);
    select.disabled = false;
    form.parentNode.appendChild(createMarker('sub-category-marker text-danger', `⚠ ${message}`));
};

const clearMarkers = (cell) => {
    cell.querySelectorAll('.sub-category-marker').forEach((marker) => marker.remove());
};

/**
 * Replaces the form with the plain category name, so the cell becomes
 * indistinguishable from a row that was already categorized on page load.
 */
const settleRow = (form, select, subCategoryName, labels) => {
    const cell = form.parentNode;
    const row = form.closest('tr');

    moveFocusToNextRow(select);

    clearMarkers(cell);
    form.remove();
    cell.insertBefore(document.createTextNode(subCategoryName), cell.firstChild);

    if (row) {
        row.classList.remove('uncategorized-transaction');
    }

    const marker = createMarker('sub-category-marker text-success', `✓ ${labels.saved}`);
    cell.appendChild(marker);
    window.setTimeout(() => marker.remove(), SAVED_MARKER_DURATION);
};

const submit = async (form, select, labels) => {
    const cell = form.parentNode;

    // Serialized before disabling the select: FormData skips disabled controls,
    // which would drop the subCategory field and make the request a no-op.
    const body = new FormData(form);

    clearMarkers(cell);
    select.disabled = true;
    cell.appendChild(createMarker('sub-category-marker text-muted', '⋯'));

    let response;
    let payload;

    try {
        response = await fetch(form.action, {
            method: 'POST',
            body,
            headers: { Accept: 'application/json' },
        });
        payload = await response.json();
    } catch (error) {
        showError(form, select, labels.error);

        return;
    }

    if (!response.ok) {
        showError(form, select, (payload && payload.error) || labels.error);

        return;
    }

    settleRow(form, select, payload.subCategory.name, labels);
};

const init = () => {
    const labels = getLabels();

    document.querySelectorAll('form.inline-sub-category').forEach((form) => {
        const select = form.querySelector('select');
        const button = form.querySelector('button');

        // Only hidden once this module is known to run: a broken script must
        // leave a usable form behind, which <noscript> would not do.
        if (button) {
            button.hidden = true;
        }

        select.addEventListener('change', () => {
            if ('' === select.value) {
                return;
            }

            submit(form, select, labels);
        });
    });
};

export default { init };
