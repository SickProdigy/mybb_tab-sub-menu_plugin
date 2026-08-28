(function () {
  // Configurable, accessible forum category filtering for MyBB.
  function getCategoryGroups() {
    return window.tabSubMenuGroups
      && typeof window.tabSubMenuGroups === 'object'
      && Object.keys(window.tabSubMenuGroups).length > 0
      ? window.tabSubMenuGroups
      : null;
  }

  function discoverCategories() {
    var adapter = window.tabSubMenuCategoryAdapter || {};
    var selector = adapter.selector || '[data-tab-sub-menu-category], tbody[id^="cat_"][id$="_e"]';
    var categories = Object.create(null);

    try {
      document.querySelectorAll(selector).forEach(function (marker) {
        var categoryId;
        if (typeof adapter.getId === 'function') {
          categoryId = adapter.getId(marker);
        } else if (marker.hasAttribute('data-tab-sub-menu-category')) {
          categoryId = marker.getAttribute('data-tab-sub-menu-category');
        } else {
          var idMatch = String(marker.id || '').match(/^cat_(\d+)_e$/);
          categoryId = idMatch ? idMatch[1] : null;
        }

        categoryId = parseInt(categoryId, 10);
        if (!categoryId || categories[categoryId]) return;

        var container = typeof adapter.getContainer === 'function'
          ? adapter.getContainer(marker)
          : (marker.tagName === 'TBODY' && marker.parentElement ? marker.parentElement : marker);
        if (!container || !container.style) return;

        var separators;
        if (typeof adapter.getSeparators === 'function') {
          separators = adapter.getSeparators(marker, container) || [];
        } else {
          var next = container.nextElementSibling;
          separators = next && next.tagName === 'BR' ? [next] : [];
        }

        categories[categoryId] = {
          container: container,
          containerDisplay: container.style.display,
          separators: Array.prototype.slice.call(separators).filter(function (separator) {
            return separator && separator.style;
          }).map(function (separator) {
            return { element: separator, display: separator.style.display };
          })
        };
      });
    } catch (error) {
      return Object.create(null);
    }

    return categories;
  }

  function setComponentVisibility(component, visible) {
    component.container.style.display = visible ? component.containerDisplay : 'none';
    component.separators.forEach(function (separator) {
      separator.element.style.display = visible ? separator.display : 'none';
    });
  }

  function setCategoryVisibility(ids, categories) {
    var categoryIds = Object.keys(categories);
    var showAll = ids.length === 0;
    var hasMatch = showAll || ids.some(function (forumId) { return categories[forumId]; });

    // Unknown markup or a selection with no matching component must leave the index usable.
    if (!categoryIds.length || !hasMatch) return false;

    categoryIds.forEach(function (categoryId) {
      setComponentVisibility(categories[categoryId], showAll);
    });
    ids.forEach(function (forumId) {
      if (categories[forumId]) setComponentVisibility(categories[forumId], true);
    });

    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var categoryGroups = getCategoryGroups();
    var categoryComponents = discoverCategories();
    var canFilterCategories = Object.keys(categoryComponents).length > 0;
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
      if (!hideEmptyTabs || !categoryGroups || !canFilterCategories) return true;

      var tabIds = categoryGroups[tab.getAttribute('data-tab')] || [];
      var available = tabIds.length === 0 || tabIds.some(function (forumId) {
        return Boolean(categoryComponents[forumId]);
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

      if (categoryGroups) setCategoryVisibility(categoryGroups[selectedKey] || [], categoryComponents);
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
