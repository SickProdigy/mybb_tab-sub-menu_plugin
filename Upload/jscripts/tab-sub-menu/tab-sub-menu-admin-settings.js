var tabSubMenuLanguage = window.tabSubMenuLanguage || {};
function tabSubMenuPhrase(key) { return typeof tabSubMenuLanguage[key] === 'string' ? tabSubMenuLanguage[key] : key; }
function tabSubMenuFormat(key, value) { return tabSubMenuPhrase(key).replace('{1}', value); }

(function () {
'use strict';
document.addEventListener('DOMContentLoaded', function () {
  var source = document.getElementById('setting_tab_sub_menu_groups');
  if (!source) return;
  var style = document.createElement('style');
  style.textContent = '.sm-help{margin:6px 0 12px;color:#666}.sm-head,.sm-row{display:grid;grid-template-columns:minmax(110px,.8fr) minmax(150px,1fr) minmax(240px,1.6fr) 75px 75px;gap:8px;align-items:center}.sm-head{font-weight:bold;margin-bottom:5px}.sm-row{margin-bottom:8px}.sm-row label>span{display:none}.sm-row input[type=text]{box-sizing:border-box;width:100%;min-width:0}.sm-forum-field{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px;align-items:center}.sm-enabled{text-align:center}.sm-picker{width:min(680px,calc(100vw - 32px));max-height:80vh;padding:0;border:1px solid #888}.sm-picker::backdrop{background:rgba(0,0,0,.35)}.sm-picker-head,.sm-picker-actions{padding:12px 16px;background:#f1f1f1}.sm-picker-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.sm-picker-head h2{margin:0;font-size:16px}.sm-picker-body{padding:14px 16px}.sm-picker-search{box-sizing:border-box;width:100%;margin-bottom:10px}.sm-picker-tree{max-height:48vh;overflow:auto;border:1px solid #ccc;background:#fff}.sm-picker-item{display:flex;align-items:flex-start;gap:8px;padding:7px 10px;border-bottom:1px solid #eee}.sm-picker-item:last-child{border-bottom:0}.sm-picker-item small{display:block;color:#666}.sm-picker-actions{text-align:right}.sm-picker-actions .button{margin-left:6px}@media(max-width:760px){.sm-head{display:none}.sm-row{display:block;padding:10px;border:1px solid #ccc}.sm-row label{display:block;margin-bottom:8px}.sm-row label>span{display:block;font-weight:bold}.sm-enabled{text-align:left}.sm-picker{width:calc(100vw - 16px)}}';
  document.head.appendChild(style);
  var editor = document.createElement('div');
  editor.innerHTML = '<div class="sm-help">' + tabSubMenuPhrase('editorHelp') + '</div><div class="sm-head"><span>' + tabSubMenuPhrase('tabId') + '</span><span>' + tabSubMenuPhrase('displayName') + '</span><span>' + tabSubMenuPhrase('forumIds') + '</span><span>' + tabSubMenuPhrase('enabled') + '</span><span></span></div><div class="sm-rows"></div><button type="button" class="button sm-add">' + tabSubMenuPhrase('addTab') + '</button>';
  source.parentNode.insertBefore(editor, source); source.style.display = 'none';
  var rows = editor.querySelector('.sm-rows');
  var defaultTab = document.getElementById('setting_tab_sub_menu_default_tab');
  function labeled(title, input, cls) { var l=document.createElement('label'),s=document.createElement('span'); s.textContent=title;l.className=cls||'';l.appendChild(s);l.appendChild(input);return l; }
  function text(cls, value, placeholder) { var i=document.createElement('input');i.type='text';i.className=cls;i.value=value||'';i.placeholder=placeholder;return i; }
  function add(values) {
    values=values||['','','','1']; var row=document.createElement('div');row.className='sm-row';
    var key=text('sm-key',values[0],tabSubMenuPhrase('tabIdExample')), label=text('sm-label',values[1],tabSubMenuPhrase('displayNameExample')), forums=text('sm-forums',values[2],tabSubMenuPhrase('forumIdsExample'));
    var enabled=document.createElement('input');enabled.type='checkbox';enabled.className='sm-on';enabled.checked=values[3]!=='0';
    var remove=document.createElement('button');remove.type='button';remove.className='button';remove.textContent=tabSubMenuPhrase('remove');remove.onclick=function(){row.remove();sync();};
    row.appendChild(labeled(tabSubMenuPhrase('tabId'),key));row.appendChild(labeled(tabSubMenuPhrase('displayName'),label));row.appendChild(labeled(tabSubMenuPhrase('forumIds'),forums,'sm-forum-field'));row.appendChild(labeled(tabSubMenuPhrase('enabled'),enabled,'sm-enabled'));row.appendChild(remove);rows.appendChild(row);
  }
  function updateDefaultOptions() {
    if (!defaultTab) return;
    var selected = defaultTab.value;
    var seen = {};
    defaultTab.textContent = '';
    rows.querySelectorAll('.sm-row').forEach(function (row) {
      var key = row.querySelector('.sm-key').value.trim().replace(/[^a-z0-9_-]/gi, '');
      var label = row.querySelector('.sm-label').value.trim().replace(/\|/g, '');
      if (!key || !label || !row.querySelector('.sm-on').checked || seen[key]) return;
      seen[key] = true;
      var option = document.createElement('option');
      option.value = key;
      option.textContent = label;
      option.selected = key === selected;
      defaultTab.appendChild(option);
    });
  }
  function sync() { var lines=[];rows.querySelectorAll('.sm-row').forEach(function(r){var key=r.querySelector('.sm-key').value.trim().replace(/[^a-z0-9_-]/gi,''),label=r.querySelector('.sm-label').value.trim().replace(/\|/g,''),ids=r.querySelector('.sm-forums').value.split(',').map(function(x){x=parseInt(x.trim(),10);return x>0?x:null;}).filter(function(x){return x!==null;}).join(','),on=r.querySelector('.sm-on').checked?'1':'0';if(key||label||ids)lines.push([key,label,ids,on].join('|'));});source.value=lines.join('\n');updateDefaultOptions(); }
  source.value.split(/\r?\n/).forEach(function(line){line=line.trim();if(line&&line.charAt(0)!=='#')add(line.split('|').map(function(x){return x.trim();}));});
  if (!rows.children.length) add(); updateDefaultOptions();editor.querySelector('.sm-add').onclick=function(){add();sync();};editor.addEventListener('input',sync);editor.addEventListener('change',sync);if(source.form)source.form.addEventListener('submit',sync);
});
}());

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var src = document.getElementById('setting_tab_sub_menu_groups');
    var mode = document.getElementById('setting_tab_sub_menu_selection_mode');
    var forumTree = window.tabSubMenuForumTree || [];
    var editor = src && src.previousElementSibling;
    if (!editor) return;

    var rows = editor.querySelector('.sm-rows');
    var status = document.createElement('div');
    status.style.cssText = 'margin:0 0 12px;padding:9px;border-left:3px solid #888';
    editor.insertBefore(status, editor.querySelector('.sm-head'));

    function ids(value) {
      var seen = {};
      return String(value).split(',').map(Number).filter(function (id) {
        if (id < 1 || seen[id]) return false;
        seen[id] = true;
        return true;
      });
    }

    function used() {
      var result = {};
      rows.querySelectorAll('.sm-row').forEach(function (row) {
        var name = row.querySelector('.sm-label').value || tabSubMenuPhrase('unnamedTab');
        ids(row.querySelector('.sm-forums').value).forEach(function (id) {
          (result[id] || (result[id] = [])).push(name);
        });
      });
      return result;
    }

    function update() {
      var assigned = used();
      var available = forumTree.filter(function (item) { return !mode || mode.value === 'all' || item.topLevel; });
      var missing = available.filter(function (item) { return !assigned[item.id]; });
      status.textContent = available.length
        ? (missing.length
          ? tabSubMenuFormat('missing', missing.map(function (item) { return item.name + ' (' + item.id + ')'; }).join(', '))
          : tabSubMenuPhrase('allAssigned'))
        : tabSubMenuPhrase('categoriesUnavailable');
    }

    var dialog = document.createElement('dialog');
    dialog.className = 'sm-picker';
    dialog.innerHTML = '<div class="sm-picker-head"><h2>' + tabSubMenuPhrase('pickerTitle') + '</h2></div><div class="sm-picker-body"><input type="search" class="textbox sm-picker-search" aria-label="' + tabSubMenuPhrase('search') + '" placeholder="' + tabSubMenuPhrase('search') + '"><p class="sm-help">' + tabSubMenuPhrase('showAllHelp') + '</p><div class="sm-picker-tree"></div></div><div class="sm-picker-actions"><button type="button" class="button sm-picker-cancel">' + tabSubMenuPhrase('cancel') + '</button><button type="button" class="button sm-picker-apply">' + tabSubMenuPhrase('applySelection') + '</button></div>';
    document.body.appendChild(dialog);
    var tree = dialog.querySelector('.sm-picker-tree');
    var search = dialog.querySelector('.sm-picker-search');
    var activeInput = null;

    function closePicker() {
      if (typeof dialog.close === 'function') dialog.close();
      else dialog.removeAttribute('open');
      activeInput = null;
    }

    function openPicker(input) {
      activeInput = input;
      var selected = ids(input.value);
      var assigned = used();
      var allMode = mode && mode.value === 'all';
      tree.textContent = '';
      forumTree.filter(function (item) { return allMode || item.topLevel; }).forEach(function (item) {
        var row = document.createElement('label');
        row.className = 'sm-picker-item';
        row.style.paddingLeft = (10 + (allMode ? item.depth * 22 : 0)) + 'px';
        row.dataset.search = String((item.path || item.name) + ' ' + item.id).toLowerCase();
        var checkbox = document.createElement('input');
        checkbox.type = 'checkbox'; checkbox.value = item.id; checkbox.checked = selected.indexOf(item.id) !== -1;
        var description = document.createElement('span');
        description.textContent = item.name + ' (' + item.id + ')';
        var detail = document.createElement('small');
        var type = item.type === 'category' ? tabSubMenuPhrase('categoryType') : tabSubMenuPhrase('forumType');
        detail.textContent = type + (item.depth ? '; ' + item.path : '') + (assigned[item.id] ? '; ' + tabSubMenuFormat('usedBy', assigned[item.id].join(', ')) : '');
        description.appendChild(detail); row.appendChild(checkbox); row.appendChild(description); tree.appendChild(row);
      });
      search.value = '';
      if (typeof dialog.showModal === 'function') dialog.showModal(); else dialog.setAttribute('open', 'open');
      search.focus();
    }

    search.addEventListener('input', function () {
      var query = search.value.trim().toLowerCase();
      tree.querySelectorAll('.sm-picker-item').forEach(function (item) { item.hidden = query !== '' && item.dataset.search.indexOf(query) === -1; });
    });
    dialog.querySelector('.sm-picker-cancel').addEventListener('click', closePicker);
    dialog.querySelector('.sm-picker-apply').addEventListener('click', function () {
      if (!activeInput) return closePicker();
      activeInput.value = Array.prototype.map.call(tree.querySelectorAll('input:checked'), function (checkbox) { return checkbox.value; }).join(',');
      activeInput.dispatchEvent(new Event('input', { bubbles: true })); closePicker();
    });
    dialog.addEventListener('cancel', function (event) { event.preventDefault(); closePicker(); });

    function enhance() {
      rows.querySelectorAll('.sm-row').forEach(function (row) {
        var input = row.querySelector('.sm-forums');
        if (!input || row.querySelector('.sm-pick')) return;

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'button sm-pick';
        button.textContent = tabSubMenuPhrase('select');
        button.onclick = function () {
          openPicker(input);
        };
        input.parentNode.appendChild(button);
      });
    }

    new MutationObserver(function () { enhance(); update(); }).observe(rows, { childList: true, subtree: true });
    editor.addEventListener('input', update);
    editor.addEventListener('change', update);
    if (mode) mode.addEventListener('change', update);
    enhance();
    update();
  });
}());

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var customCss = document.getElementById('setting_tab_sub_menu_custom_css');
    var maintainedCss = window.tabSubMenuMaintainedCss || '';
    if (!customCss || !maintainedCss) return;

    var reference = document.createElement('details');
    reference.style.marginTop = '10px';

    var summary = document.createElement('summary');
    summary.textContent = tabSubMenuPhrase('viewCss');
    summary.style.cursor = 'pointer';
    reference.appendChild(summary);

    var warning = document.createElement('p');
    warning.textContent = tabSubMenuPhrase('cssWarning');
    reference.appendChild(warning);

    var copy = document.createElement('button');
    copy.type = 'button';
    copy.className = 'button';
    copy.textContent = tabSubMenuPhrase('copyCss');
    copy.addEventListener('click', function () {
      if (customCss.value.trim() !== '' && !window.confirm(tabSubMenuPhrase('replaceCss'))) return;
      customCss.value = maintainedCss;
      customCss.dispatchEvent(new Event('input', { bubbles: true }));
      customCss.dispatchEvent(new Event('change', { bubbles: true }));
    });
    reference.appendChild(copy);

    var preview = document.createElement('pre');
    preview.textContent = maintainedCss;
    preview.style.cssText = 'max-height:420px;overflow:auto;margin-top:10px;padding:12px;border:1px solid #ccc;background:#f7f7f7;white-space:pre;';
    reference.appendChild(preview);

    customCss.insertAdjacentElement('afterend', reference);
  });
}());
