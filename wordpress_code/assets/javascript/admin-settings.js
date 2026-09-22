// assets/js/admin-settings.js

(function() {
    'use strict';

    /* ============================================
       REPEATER GÉNÉRIQUE
       ============================================ */

    function initRepeaterAddRow(e) {
        if (!e.target.classList.contains('mps-tools-add-row')) return;

        const key = e.target.dataset.key;
        const table = document.querySelector(`.mps-tools-repeater[data-key="${key}"] tbody`);
        const template = document.getElementById(`tpl-${key}`);

        const newIndex = table.children.length;
        const html = template.innerHTML.replaceAll('__INDEX__', newIndex);

        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        table.appendChild(wrapper.firstElementChild);
    }

    function initRemoveRow(e) {
        if (!e.target.classList.contains('mps-tools-remove-row')) return;
        e.target.closest('tr').remove();
    }

    /* ============================================
       CATEGORIES
       ============================================ */

    function initAddCategory(e) {
        if (!e.target.classList.contains('mps-tools-add-category')) return;

        const key = e.target.dataset.key;
        const container = document.querySelector(`.mps-tools-categories[data-key="${key}"] .mps-tools-categories-list`);
        const template = document.getElementById(`tpl-${key}-category`);

        const newIndex = container.children.length;
        const html = template.innerHTML.replaceAll('__CAT_INDEX__', newIndex);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        container.appendChild(wrapper.firstElementChild);
    }

    function initRemoveCategory(e) {
        if (!e.target.classList.contains('mps-tools-remove-category')) return;
        e.target.closest('.mps-tools-category-card').remove();
    }

    function initAddSpecialField(e) {
        if (!e.target.classList.contains('mps-tools-add-special-field')) return;

        const card = e.target.closest('.mps-tools-category-card');
        const table = card.querySelector('.mps-tools-sub-repeater tbody');
        const template = card.querySelector('template.tpl-special-field');

        const newIndex = table.children.length;
        const html = template.innerHTML.replaceAll('__SUB_INDEX__', newIndex);

        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        table.appendChild(wrapper.firstElementChild);
    }

    /* ============================================
       TEMPLATE MANAGER
       ============================================ */

    function showTemplateBlock(manager, index) {
        manager.querySelectorAll('.mps-tools-template-block').forEach(function(block) {
            block.style.display = (block.dataset.index === String(index)) ? 'block' : 'none';
        });
    }

    function initTemplateManagers() {
        document.querySelectorAll('.mps-tools-template-manager').forEach(function(manager) {
            const select = manager.querySelector('.mps-tools-template-select');
            if (select && select.value !== '') showTemplateBlock(manager, select.value);
        });
    }

    function onTemplateSelectChange(e) {
        if (!e.target.classList.contains('mps-tools-template-select')) return;
        const manager = e.target.closest('.mps-tools-template-manager');
        showTemplateBlock(manager, e.target.value);
    }

    function onTemplateFieldInput(e) {
        if (!e.target.classList.contains('mps-tools-template-field')) return;

        const block = e.target.closest('.mps-tools-template-block');
        const manager = e.target.closest('.mps-tools-template-manager');
        const nameCol = manager.dataset.nameCol;
        const fieldName = e.target.name;

        if (fieldName.includes('[' + nameCol + ']')) {
            const select = manager.querySelector('.mps-tools-template-select');
            const option = select.querySelector('option[value="' + block.dataset.index + '"]');
            if (option) option.textContent = e.target.value || ('Template ' + block.dataset.index);
        }
    }

    function initAddTemplate(e) {
        if (!e.target.classList.contains('mps-tools-add-template')) return;

        const key = e.target.dataset.key;
        const manager = e.target.closest('.mps-tools-template-manager');
        const select = manager.querySelector('.mps-tools-template-select');
        const blocksContainer = manager.querySelector('.mps-tools-template-blocks');
        const template = document.getElementById(`tpl-${key}-template`);

        const newIndex = blocksContainer.children.length;
        const html = template.innerHTML.replaceAll('__TPL_INDEX__', newIndex);

        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        blocksContainer.appendChild(wrapper.firstElementChild);

        const option = document.createElement('option');
        option.value = newIndex;
        option.textContent = 'Nouveau template ' + newIndex;
        select.appendChild(option);
        select.value = newIndex;
        showTemplateBlock(manager, newIndex);
    }

    function initRemoveTemplate(e) {
        if (!e.target.classList.contains('mps-tools-remove-template')) return;

        const manager = e.target.closest('.mps-tools-template-manager');
        const select = manager.querySelector('.mps-tools-template-select');
        const index = select.value;
        if (index === '') return;
        if (!confirm('Supprimer ce template ?')) return;

        manager.querySelector(`.mps-tools-template-block[data-index="${index}"]`)?.remove();
        select.querySelector(`option[value="${index}"]`)?.remove();

        if (select.options.length > 0) {
            select.selectedIndex = 0;
            showTemplateBlock(manager, select.value);
        }
    }

    /* ============================================
       INITIALISATION
       ============================================ */

    document.addEventListener('DOMContentLoaded', function() {
        initTemplateManagers();
    });

    document.addEventListener('click', function(e) {
        initRepeaterAddRow(e);
        initRemoveRow(e);
        initAddCategory(e);
        initRemoveCategory(e);
        initAddSpecialField(e);
        initAddTemplate(e);
        initRemoveTemplate(e);
    });

    document.addEventListener('change', onTemplateSelectChange);
    document.addEventListener('input', onTemplateFieldInput);

})();