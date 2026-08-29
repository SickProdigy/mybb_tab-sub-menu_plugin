var tabSubMenuLanguage = window.tabSubMenuLanguage || {};
function tabSubMenuPhrase(key) { return typeof tabSubMenuLanguage[key] === 'string' ? tabSubMenuLanguage[key] : key; }
function tabSubMenuFormat(key, value) { return tabSubMenuPhrase(key).replace('{1}', value); }

(function () {
'use strict';
document.addEventListener('DOMContentLoaded', function () {
  var source = document.getElementById('setting_tab_sub_menu_groups');
  if (!source) return;
  var style = document.createElement('style');
  style.textContent = '.sm-help{margin:6px 0 12px;color:#666}.sm-head,.sm-row{display:grid;grid-template-columns:minmax(110px,.8fr) minmax(150px,1fr) minmax(190px,1.5fr) 75px 75px;gap:8px;align-items:center}.sm-head{font-weight:bold;margin-bottom:5px}.sm-row{margin-bottom:8px}.sm-row label>span{display:none}.sm-row input[type=text]{box-sizing:border-box;width:100%}.sm-enabled{text-align:center}@media(max-width:760px){.sm-head{display:none}.sm-row{display:block;padding:10px;border:1px solid #ccc}.sm-row label{display:block;margin-bottom:8px}.sm-row label>span{display:block;font-weight:bold}.sm-enabled{text-align:left}}';
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
    row.appendChild(labeled(tabSubMenuPhrase('tabId'),key));row.appendChild(labeled(tabSubMenuPhrase('displayName'),label));row.appendChild(labeled(tabSubMenuPhrase('forumIds'),forums));row.appendChild(labeled(tabSubMenuPhrase('enabled'),enabled,'sm-enabled'));row.appendChild(remove);rows.appendChild(row);
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
    var categories = window.tabSubMenuCategories || [];
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
      var missing = categories.filter(function (category) { return !assigned[category.id]; });
      status.textContent = categories.length
        ? (missing.length
          ? tabSubMenuFormat('missing', missing.map(function (category) { return category.name + ' (' + category.id + ')'; }).join(', '))
          : tabSubMenuPhrase('allAssigned'))
        : tabSubMenuPhrase('categoriesUnavailable');
    }

    function enhance() {
      rows.querySelectorAll('.sm-row').forEach(function (row) {
        var input = row.querySelector('.sm-forums');
        if (!input || row.querySelector('.sm-pick')) return;

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'button sm-pick';
        button.textContent = tabSubMenuPhrase('select');
        button.style.marginLeft = '6px';
        button.onclick = function () {
          var assigned = used();
          var own = ids(input.value);
          var choices = categories.map(function (category) {
            var marker = assigned[category.id]
              ? tabSubMenuFormat('usedBy', assigned[category.id].join(', '))
              : tabSubMenuPhrase('missingMarker');
            return category.id + ': ' + category.name + ' [' + marker + ']';
          }).join('\n');
          var answer = prompt(tabSubMenuFormat('categoryPrompt', choices), '');
          if (answer === null) return;

          var seen = {};
          input.value = own.concat(ids(answer)).filter(function (id) {
            if (seen[id]) return false;
            seen[id] = true;
            return true;
          }).join(',');
          input.dispatchEvent(new Event('input', { bubbles: true }));
        };
        input.parentNode.appendChild(button);
      });
    }

    new MutationObserver(function () { enhance(); update(); }).observe(rows, { childList: true, subtree: true });
    editor.addEventListener('input', update);
    editor.addEventListener('change', update);
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
