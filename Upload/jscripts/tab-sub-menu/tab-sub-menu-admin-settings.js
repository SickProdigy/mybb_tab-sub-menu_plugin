(function () {
'use strict';
document.addEventListener('DOMContentLoaded', function () {
  var source = document.getElementById('setting_tab_sub_menu_groups');
  if (!source) return;
  var style = document.createElement('style');
  style.textContent = '.sm-help{margin:6px 0 12px;color:#666}.sm-head,.sm-row{display:grid;grid-template-columns:minmax(110px,.8fr) minmax(150px,1fr) minmax(190px,1.5fr) 75px 75px;gap:8px;align-items:center}.sm-head{font-weight:bold;margin-bottom:5px}.sm-row{margin-bottom:8px}.sm-row label>span{display:none}.sm-row input[type=text]{box-sizing:border-box;width:100%}.sm-enabled{text-align:center}@media(max-width:760px){.sm-head{display:none}.sm-row{display:block;padding:10px;border:1px solid #ccc}.sm-row label{display:block;margin-bottom:8px}.sm-row label>span{display:block;font-weight:bold}.sm-enabled{text-align:left}}';
  document.head.appendChild(style);
  var editor = document.createElement('div');
  editor.innerHTML = '<div class="sm-help">Use a short, unique Tab ID such as <code>gaming</code>. Enter Forum/Category IDs separated by commas, such as <code>2,3,4</code>. Leave the IDs empty for a “show all” tab.</div><div class="sm-head"><span>Tab ID (internal)</span><span>Display name</span><span>Forum/Category IDs</span><span>Enabled</span><span></span></div><div class="sm-rows"></div><button type="button" class="button sm-add">Add tab</button>';
  source.parentNode.insertBefore(editor, source); source.style.display = 'none';
  var rows = editor.querySelector('.sm-rows');
  var defaultTab = document.getElementById('setting_tab_sub_menu_default_tab');
  function labeled(title, input, cls) { var l=document.createElement('label'),s=document.createElement('span'); s.textContent=title;l.className=cls||'';l.appendChild(s);l.appendChild(input);return l; }
  function text(cls, value, placeholder) { var i=document.createElement('input');i.type='text';i.className=cls;i.value=value||'';i.placeholder=placeholder;return i; }
  function add(values) {
    values=values||['','','','1']; var row=document.createElement('div');row.className='sm-row';
    var key=text('sm-key',values[0],'gaming'), label=text('sm-label',values[1],'Gaming'), forums=text('sm-forums',values[2],'2,3,4');
    var enabled=document.createElement('input');enabled.type='checkbox';enabled.className='sm-on';enabled.checked=values[3]!=='0';
    var remove=document.createElement('button');remove.type='button';remove.className='button';remove.textContent='Remove';remove.onclick=function(){row.remove();sync();};
    row.appendChild(labeled('Tab ID (internal)',key));row.appendChild(labeled('Display name',label));row.appendChild(labeled('Forum/Category IDs',forums));row.appendChild(labeled('Enabled',enabled,'sm-enabled'));row.appendChild(remove);rows.appendChild(row);
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

(function(){document.addEventListener('DOMContentLoaded',function(){var src=document.getElementById('setting_tab_sub_menu_groups'),cs=window.tabSubMenuCategories||[],ed=src&&src.previousElementSibling;if(!ed)return;var rows=ed.querySelector('.sm-rows'),box=document.createElement('div');box.style.cssText='margin:0 0 12px;padding:9px;border-left:3px solid #888';ed.insertBefore(box,ed.querySelector('.sm-head'));function ids(v){var o={};return String(v).split(',').map(Number).filter(function(n){if(n<1||o[n])return false;o[n]=1;return true})}function used(){var u={};rows.querySelectorAll('.sm-row').forEach(function(r){var n=r.querySelector('.sm-label').value||'Unnamed tab';ids(r.querySelector('.sm-forums').value).forEach(function(i){(u[i]||(u[i]=[])).push(n)})});return u}function update(){var u=used(),m=cs.filter(function(c){return !u[c.id]});box.textContent=cs.length?(m.length?'Missing: '+m.map(function(c){return c.name+' ('+c.id+')'}).join(', '):'All top-level categories assigned.'):'Category list unavailable; enter IDs manually.'}function enhance(){rows.querySelectorAll('.sm-row').forEach(function(r){var i=r.querySelector('.sm-forums');if(!i||r.querySelector('.sm-pick'))return;var b=document.createElement('button');b.type='button';b.className='button sm-pick';b.textContent='Select';b.style.marginLeft='6px';b.onclick=function(){var u=used(),own=ids(i.value),text=cs.map(function(c){return c.id+': '+c.name+(u[c.id]?' [used by '+u[c.id].join(', ')+']':' [missing]')}).join('\n'),answer=prompt('Top-level categories:\n\n'+text+'\n\nEnter IDs to add:', '');if(answer===null)return;var all=own.concat(ids(answer)),seen={};i.value=all.filter(function(n){if(seen[n])return false;seen[n]=1;return true}).join(',');i.dispatchEvent(new Event('input',{bubbles:true}))};i.parentNode.appendChild(b)})}new MutationObserver(function(){enhance();update()}).observe(rows,{childList:true,subtree:true});ed.addEventListener('input',update);ed.addEventListener('change',update);enhance();update()})}());

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var customCss = document.getElementById('setting_tab_sub_menu_custom_css');
    var maintainedCss = window.tabSubMenuMaintainedCss || '';
    if (!customCss || !maintainedCss) return;

    var reference = document.createElement('details');
    reference.style.marginTop = '10px';

    var summary = document.createElement('summary');
    summary.textContent = 'View maintained default CSS';
    summary.style.cursor = 'pointer';
    reference.appendChild(summary);

    var warning = document.createElement('p');
    warning.textContent = 'Custom CSS loads after this maintained stylesheet. Copy only when you want a full editable starting point; copied rules can override future plugin style updates.';
    reference.appendChild(warning);

    var copy = document.createElement('button');
    copy.type = 'button';
    copy.className = 'button';
    copy.textContent = 'Copy defaults to Custom Menu CSS';
    copy.addEventListener('click', function () {
      if (customCss.value.trim() !== '' && !window.confirm('Replace your existing Custom Menu CSS with the maintained defaults?')) return;
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
