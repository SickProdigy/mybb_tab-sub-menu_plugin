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

        var forumRows = Object.create(null);
        container.querySelectorAll('[data-tab-sub-menu-forum], tr[id^="forum_"]').forEach(function (row) {
          var forumId = row.hasAttribute('data-tab-sub-menu-forum')
            ? row.getAttribute('data-tab-sub-menu-forum')
            : (String(row.id || '').match(/^forum_(\d+)$/) || [])[1];
          forumId = parseInt(forumId, 10);
          if (forumId && !forumRows[forumId] && row.style) forumRows[forumId] = { element: row, display: row.style.display };
        });

        categories[categoryId] = {
          container: container,
          containerDisplay: container.style.display,
          forumRows: forumRows,
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

  function setForumVisibility(forum, visible) {
    forum.element.style.display = visible ? forum.display : 'none';
  }

  function setCategoryVisibility(ids, categories, selectionMode) {
    var categoryIds = Object.keys(categories);
    var showAll = ids.length === 0;
    var allMode = selectionMode === 'all';
    var hasMatch = showAll || ids.some(function (forumId) {
      if (categories[forumId]) return true;
      return allMode && categoryIds.some(function (categoryId) { return categories[categoryId].forumRows[forumId]; });
    });

    // Unknown markup or a selection with no matching component must leave the index usable.
    if (!categoryIds.length || !hasMatch) return false;

    categoryIds.forEach(function (categoryId) {
      var component = categories[categoryId];
      var categorySelected = showAll || ids.indexOf(parseInt(categoryId, 10)) !== -1;
      var selectedForums = Object.keys(component.forumRows).filter(function (forumId) { return ids.indexOf(parseInt(forumId, 10)) !== -1; });
      setComponentVisibility(component, categorySelected || (allMode && selectedForums.length > 0));
      Object.keys(component.forumRows).forEach(function (forumId) {
        setForumVisibility(component.forumRows[forumId], categorySelected || !allMode || selectedForums.indexOf(forumId) !== -1);
      });
    });

    return true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    function finishInitialization() {
      document.documentElement.classList.remove('tab-sub-menu-initializing');
    }

    var categoryGroups = getCategoryGroups();
    var categoryComponents = discoverCategories();
    var canFilterCategories = Object.keys(categoryComponents).length > 0;
    var tablist = document.querySelector('.tab-sub-menu[role="tablist"]');
    if (!tablist) {
      finishInitialization();
      return;
    }

    var allTabs = Array.prototype.slice.call(tablist.querySelectorAll('button[role="tab"]'));
    if (!allTabs.length) {
      finishInitialization();
      return;
    }

    var hideEmptyTabs = window.tabSubMenuHideEmptyTabs === true;
    var selectionMode = window.tabSubMenuSelectionMode === 'all' ? 'all' : 'top';
    var storageKey = typeof window.tabSubMenuStorageKey === 'string'
      ? window.tabSubMenuStorageKey
      : 'tabSubMenuTab:/';
    var storageTimestampKey = storageKey + ':savedAt';
    var selectionMaxAge = 24 * 60 * 60 * 1000;
    var legacyStorageKey = 'tabSubMenuTab';
    var defaultTabKey = typeof window.tabSubMenuDefaultTab === 'string'
      ? window.tabSubMenuDefaultTab
      : 'home';
    var rememberSelection = window.tabSubMenuRememberSelection !== false;
    var urlStateEnabled = window.tabSubMenuUrlState !== false;
    var urlParameter = 'tsm_tab';
    var tabs = allTabs.filter(function (tab) {
      if (!hideEmptyTabs || !categoryGroups || !canFilterCategories) return true;

      var tabIds = categoryGroups[tab.getAttribute('data-tab')] || [];
      var available = tabIds.length === 0 || tabIds.some(function (forumId) {
        if (categoryComponents[forumId]) return true;
        return selectionMode === 'all' && Object.keys(categoryComponents).some(function (categoryId) {
          return Boolean(categoryComponents[categoryId].forumRows[forumId]);
        });
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
      finishInitialization();
      return;
    }

    function forgetSelection() {
      window.localStorage.removeItem(storageKey);
      window.localStorage.removeItem(storageTimestampKey);
    }

    function rememberTab(tabKey) {
      window.localStorage.setItem(storageKey, tabKey);
      window.localStorage.setItem(storageTimestampKey, String(Date.now()));
    }

    function readRememberedSelection() {
      var selection = window.localStorage.getItem(storageKey);
      if (selection === null) return null;

      var savedAt = parseInt(window.localStorage.getItem(storageTimestampKey), 10);
      if (!savedAt) {
        // Preserve pre-1.1.1 selections for one normal retention window.
        window.localStorage.setItem(storageTimestampKey, String(Date.now()));
        return selection;
      }

      if (Date.now() - savedAt >= selectionMaxAge) {
        forgetSelection();
        return null;
      }

      return selection;
    }

    function activate(tab, remember, updateHistory) {
      allTabs.forEach(function (candidate) {
        var selected = candidate === tab;
        candidate.setAttribute('aria-selected', selected ? 'true' : 'false');
        candidate.setAttribute('tabindex', selected ? '0' : '-1');
        if (candidate.parentElement) candidate.parentElement.classList.toggle('active', selected);
      });

      var selectedKey = tab.getAttribute('data-tab');
      if (remember && rememberSelection) {
        try { rememberTab(selectedKey); } catch (error) {}
      }

      if (categoryGroups) setCategoryVisibility(categoryGroups[selectedKey] || [], categoryComponents, selectionMode);
      if (updateHistory) writeUrlSelection(selectedKey, false);
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener('click', function () {
        activate(tab, true, true);
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
        activate(tabs[nextIndex], true, true);
      });
    });

    function findAvailableTab(tabKey) {
      return tabKey
        ? tabs.find(function (tab) { return tab.getAttribute('data-tab') === tabKey; })
        : null;
    }

    function readUrlSelection() {
      if (!urlStateEnabled || typeof window.URL !== 'function') return null;
      try {
        return new window.URL(window.location.href).searchParams.get(urlParameter);
      } catch (error) {
        return null;
      }
    }

    function writeUrlSelection(tabKey, replace) {
      if (!urlStateEnabled || typeof window.URL !== 'function' || !window.history) return;
      try {
        var url = new window.URL(window.location.href);
        if (url.searchParams.get(urlParameter) === tabKey) return;
        url.searchParams.set(urlParameter, tabKey);
        var method = replace ? 'replaceState' : 'pushState';
        if (typeof window.history[method] === 'function') {
          window.history[method](null, '', url.pathname + url.search + url.hash);
        }
      } catch (error) {}
    }

    var initialTab = findAvailableTab(readUrlSelection());
    try {
      if (!rememberSelection) {
        forgetSelection();
      }

      if (rememberSelection && !initialTab) {
        var scopedSelection = readRememberedSelection();
        initialTab = findAvailableTab(scopedSelection);
        if (scopedSelection !== null && !initialTab) forgetSelection();

        if (!initialTab) {
          var legacySelection = window.localStorage.getItem(legacyStorageKey);
          initialTab = findAvailableTab(legacySelection);

          if (legacySelection !== null) {
            if (initialTab) rememberTab(legacySelection);
            window.localStorage.removeItem(legacyStorageKey);
          }
        }
      }
    } catch (error) {}

    if (!initialTab) {
      initialTab = findAvailableTab(defaultTabKey) || tabs[0];
    }

    activate(initialTab, false, false);
    writeUrlSelection(initialTab.getAttribute('data-tab'), true);
    finishInitialization();

    if (urlStateEnabled) {
      window.addEventListener('popstate', function () {
        var requestedTab = findAvailableTab(readUrlSelection());
        var fallbackTab = requestedTab;

        if (!fallbackTab && rememberSelection) {
          try { fallbackTab = findAvailableTab(window.localStorage.getItem(storageKey)); } catch (error) {}
        }
        if (!fallbackTab) fallbackTab = findAvailableTab(defaultTabKey) || tabs[0];

        activate(fallbackTab, false, false);
      });
    }
  });
}());
