(function() {
// Configurable forum category filtering for MyBB

function getCategoryGroups() {
  return window.tabSubMenuGroups && typeof window.tabSubMenuGroups === 'object' && Object.keys(window.tabSubMenuGroups).length > 0
    ? window.tabSubMenuGroups
    : null;
}

document.addEventListener('DOMContentLoaded', function() {
  const categoryGroups = getCategoryGroups();
  if (!categoryGroups) {
    // No config: show all forums, tabs only highlight
    document.querySelectorAll('.forum-tabs li').forEach(tab => {
      tab.addEventListener('click', function() {
        document.querySelectorAll('.forum-tabs li').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      });
    });
    return;
  }
  document.querySelectorAll('.forum-tabs li').forEach(tab => {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.forum-tabs li').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      let selected = this.getAttribute('data-tab');
      // Store selected tab in localStorage
      try { localStorage.setItem('tabSubMenuTab', selected); } catch(e) {}
      let ids = categoryGroups[selected] || [];
      let showAll = ids.length === 0;
      // Hide all categories and their following <br>
      document.querySelectorAll('tbody[id^="cat_"][id$="_e"]').forEach(cat => {
        if (cat.parentElement) cat.parentElement.style.display = showAll ? '' : 'none';
        // Hide next sibling <br> if present
        let next = cat.parentElement ? cat.parentElement.nextSibling : null;
        while (next && next.nodeType === 3 && !next.textContent.trim()) next = next.nextSibling; // skip whitespace
        if (next && next.nodeType === 1 && next.tagName === 'BR') next.style.display = showAll ? '' : 'none';
      });
      // Show selected categories and their following <br>
      ids.forEach(fid => {
        let el = document.getElementById(`cat_${fid}_e`);
        if(el && el.parentElement) el.parentElement.style.display = '';
        let next = el && el.parentElement ? el.parentElement.nextSibling : null;
        while (next && next.nodeType === 3 && !next.textContent.trim()) next = next.nextSibling;
        if (next && next.nodeType === 1 && next.tagName === 'BR') next.style.display = '';
      });
    });
  });
  // Auto-select the last tab, then Home, then the first configured tab
  let lastTab = null;
  try { lastTab = localStorage.getItem('tabSubMenuTab'); } catch(e) {}
  let tabToSelect = lastTab ? document.querySelector('.forum-tabs li[data-tab="' + lastTab + '"]') : null;
  if(tabToSelect) {
    tabToSelect.click();
  } else {
    const defaultTab = document.querySelector('.forum-tabs li[data-tab="home"]') || document.querySelector('.forum-tabs li');
    if(defaultTab) defaultTab.click();
  }
});
})();
