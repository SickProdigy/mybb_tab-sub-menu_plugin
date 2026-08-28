(function () {
  // Configurable, accessible forum category filtering for MyBB.
  function getCategoryGroups() {
    return window.tabSubMenuGroups
      && typeof window.tabSubMenuGroups === 'object'
      && Object.keys(window.tabSubMenuGroups).length > 0
      ? window.tabSubMenuGroups
      : null;
  }

  function setCategoryVisibility(ids) {
    var showAll = ids.length === 0;

    document.querySelectorAll('tbody[id^="cat_"][id$="_e"]').forEach(function (category) {
      var wrapper = category.parentElement;
      if (wrapper) wrapper.style.display = showAll ? '' : 'none';

      var next = wrapper ? wrapper.nextSibling : null;
      while (next && next.nodeType === 3 && !next.textContent.trim()) next = next.nextSibling;
      if (next && next.nodeType === 1 && next.tagName === 'BR') next.style.display = showAll ? '' : 'none';
    });

    ids.forEach(function (forumId) {
      var category = document.getElementById('cat_' + forumId + '_e');
      var wrapper = category && category.parentElement;
      if (wrapper) wrapper.style.display = '';

      var next = wrapper ? wrapper.nextSibling : null;
      while (next && next.nodeType === 3 && !next.textContent.trim()) next = next.nextSibling;
      if (next && next.nodeType === 1 && next.tagName === 'BR') next.style.display = '';
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var categoryGroups = getCategoryGroups();
    var tablist = document.querySelector('.tab-sub-menu[role="tablist"]');
    if (!tablist) return;

    var allTabs = Array.prototype.slice.call(tablist.querySelectorAll('button[role="tab"]'));
    if (!allTabs.length) return;

    var hideEmptyTabs = window.tabSubMenuHideEmptyTabs === true;
    var storageKey = typeof window.tabSubMenuStorageKey === 'string'
      ? window.tabSubMenuStorageKey
      : 'tabSubMenuTab:/';
    var legacyStorageKey = 'tabSubMenuTab';
    var tabs = allTabs.filter(function (tab) {
      if (!hideEmptyTabs || !categoryGroups) return true;

      var tabIds = categoryGroups[tab.getAttribute('data-tab')] || [];
      var available = tabIds.length === 0 || tabIds.some(function (forumId) {
        return document.getElementById('cat_' + forumId + '_e') !== null;
      });

      if (!available) {
        tab.setAttribute('aria-selected', 'false');
        tab.setAttribute('tabindex', '-1');
        if (tab.parentElement) {
          tab.parentElement.classList.remove('active');
          tab.parentElement.hidden = true;
        }
      }

      return available;
    });

    if (!tabs.length) {
      tablist.hidden = true;
      return;
    }

    function activate(tab, remember) {
      allTabs.forEach(function (candidate) {
        var selected = candidate === tab;
        candidate.setAttribute('aria-selected', selected ? 'true' : 'false');
        candidate.setAttribute('tabindex', selected ? '0' : '-1');
        if (candidate.parentElement) candidate.parentElement.classList.toggle('active', selected);
      });

      var selectedKey = tab.getAttribute('data-tab');
      if (remember) {
        try { window.localStorage.setItem(storageKey, selectedKey); } catch (error) {}
      }

      if (categoryGroups) setCategoryVisibility(categoryGroups[selectedKey] || []);
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        activate(tab, true);
      });

      tab.addEventListener('keydown', function (event) {
        var nextIndex = null;

        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') nextIndex = (index + 1) % tabs.length;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') nextIndex = (index - 1 + tabs.length) % tabs.length;
        if (event.key === 'Home') nextIndex = 0;
        if (event.key === 'End') nextIndex = tabs.length - 1;
        if (nextIndex === null) return;

        event.preventDefault();
        tabs[nextIndex].focus();
        activate(tabs[nextIndex], true);
      });
    });

    function findAvailableTab(tabKey) {
      return tabKey
        ? tabs.find(function (tab) { return tab.getAttribute('data-tab') === tabKey; })
        : null;
    }

    var initialTab = null;
    try {
      var scopedSelection = window.localStorage.getItem(storageKey);
      initialTab = findAvailableTab(scopedSelection);
      if (scopedSelection !== null && !initialTab) window.localStorage.removeItem(storageKey);

      if (!initialTab) {
        var legacySelection = window.localStorage.getItem(legacyStorageKey);
        initialTab = findAvailableTab(legacySelection);

        if (legacySelection !== null) {
          if (initialTab) window.localStorage.setItem(storageKey, legacySelection);
          window.localStorage.removeItem(legacyStorageKey);
        }
      }
    } catch (error) {}

    if (!initialTab) {
      initialTab = tabs.find(function (tab) { return tab.getAttribute('data-tab') === 'home'; }) || tabs[0];
    }

    activate(initialTab, false);
  });
}());
