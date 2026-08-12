(function () {
'use strict';
document.addEventListener('DOMContentLoaded', function () {
  var source = document.getElementById('setting_sub_menu_groups');
  if (!source) return;
  var style = document.createElement('style');
  style.textContent = '.sm-help{margin:6px 0 12px;color:#666}.sm-head,.sm-row{display:grid;grid-template-columns:minmax(110px,.8fr) minmax(150px,1fr) minmax(190px,1.5fr) 75px 75px;gap:8px;align-items:center}.sm-head{font-weight:bold;margin-bottom:5px}.sm-row{margin-bottom:8px}.sm-row input[type=text]{box-sizing:border-box;width:100%}.sm-enabled{text-align:center}@media(max-width:760px){.sm-head{display:none}.sm-row{display:block;padding:10px;border:1px solid #ccc}.sm-row label{display:block;margin-bottom:8px}.sm-row label span{display:block;font-weight:bold}.sm-enabled{text-align:left}}';
  document.head.appendChild(style);
  var editor = document.createElement('div');
  editor.innerHTML = '<div class="sm-help">Use a short, unique Tab ID such as <code>gaming</code>. Enter top-level forum category IDs separated by commas, such as <code>2,3,4</code>. Leave Forum IDs empty for a “show all” tab.</div><div class="sm-head"><span>Tab ID (internal)</span><span>Display name</span><span>Forum IDs</span><span>Enabled</span><span></span></div><div class="sm-rows"></div><button type="button" class="button sm-add">Add tab</button>';
  source.parentNode.insertBefore(editor, source); source.style.display = 'none';
  var rows = editor.querySelector('.sm-rows');
  function labeled(title, input, cls) { var l=document.createElement('label'),s=document.createElement('span'); s.textContent=title;l.className=cls||'';l.appendChild(s);l.appendChild(input);return l; }
  function text(cls, value, placeholder) { var i=document.createElement('input');i.type='text';i.className=cls;i.value=value||'';i.placeholder=placeholder;return i; }
  function add(values) {
    values=values||['','','','1']; var row=document.createElement('div');row.className='sm-row';
    var key=text('sm-key',values[0],'gaming'), label=text('sm-label',values[1],'Gaming'), forums=text('sm-forums',values[2],'2,3,4');
    var enabled=document.createElement('input');enabled.type='checkbox';enabled.className='sm-on';enabled.checked=values[3]!=='0';
    var remove=document.createElement('button');remove.type='button';remove.className='button';remove.textContent='Remove';remove.onclick=function(){row.remove();sync();};
    row.appendChild(labeled('Tab ID (internal)',key));row.appendChild(labeled('Display name',label));row.appendChild(labeled('Forum IDs',forums));row.appendChild(labeled('Enabled',enabled,'sm-enabled'));row.appendChild(remove);rows.appendChild(row);
  }
  function sync() { var lines=[];rows.querySelectorAll('.sm-row').forEach(function(r){var key=r.querySelector('.sm-key').value.trim().replace(/[^a-z0-9_-]/gi,''),label=r.querySelector('.sm-label').value.trim().replace(/\|/g,''),ids=r.querySelector('.sm-forums').value.split(',').map(function(x){x=parseInt(x.trim(),10);return x>0?x:null;}).filter(function(x){return x!==null;}).join(','),on=r.querySelector('.sm-on').checked?'1':'0';if(key||label||ids)lines.push([key,label,ids,on].join('|'));});source.value=lines.join('\n'); }
  source.value.split(/\r?\n/).forEach(function(line){line=line.trim();if(line&&line.charAt(0)!=='#')add(line.split('|').map(function(x){return x.trim();}));});
  if (!rows.children.length) add(); editor.querySelector('.sm-add').onclick=function(){add();};editor.addEventListener('input',sync);editor.addEventListener('change',sync);if(source.form)source.form.addEventListener('submit',sync);
});
}());
