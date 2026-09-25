(function () {
    'use strict';

    var DATA = window.MPWCFG_DATA || { ajax_url: '', nonce: '', i18n: {} };
    var ALLOWED = ['pdf', 'png', 'jpg', 'jpeg', 'ai', 'eps'];
    var MAX_BYTES = 25 * 1024 * 1024;

    function clp(n) {
        try { return '$ ' + new Intl.NumberFormat('es-CL').format(Math.round(n)); }
        catch (e) { return '$ ' + Math.round(n); }
    }
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'})[c];
        });
    }
    function post(root, action, selections, extra) {
        var fd = new FormData();
        fd.append('action', action); fd.append('nonce', DATA.nonce || ''); fd.append('product_id', root.dataset.product || '');
        Object.keys(selections || {}).forEach(function (k) { fd.append('selections[' + k + ']', selections[k]); });
        Object.keys(extra || {}).forEach(function (k) {
            if (extra[k] !== '' && extra[k] != null) {
                if (extra[k] && typeof extra[k] === 'object') {
                    Object.keys(extra[k]).forEach(function (subKey) {
                        if (extra[k][subKey] !== '' && extra[k][subKey] != null) fd.append(k + '[' + subKey + ']', extra[k][subKey]);
                    });
                } else {
                    fd.append(k, extra[k]);
                }
            }
        });
        return fetch(DATA.ajax_url, {method:'POST', body:fd}).then(function(r){return r.json();});
    }

    function init(root) {
        var formatStep = root.querySelector('.mpwcfg-combo-step[data-level="0"]');
        var stepsWrap = root.querySelector('.mpwcfg-combo-levels-front');
        var progress = root.querySelectorAll('[data-progress]');
        var summary = root.querySelector('.mpwcfg-combo-summary');
        var lines = root.querySelector('.mpwcfg-combo-summary-lines');
        var fileSummary = root.querySelector('.mpwcfg-combo-summary-file');
        var total = root.querySelector('.mpwcfg-combo-total-value');
        var add = root.querySelector('.mpwcfg-combo-add');
        var addText = add ? add.querySelector('.mpwcfg-add__text') : null;
        var feedback = root.querySelector('.mpwcfg-combo-feedback');
        var cartLink = root.querySelector('.mpwcfg-combo-cart-link');
        var fileUrl = root.querySelector('.mpwcfg-file-url');
        var fileName = root.querySelector('.mpwcfg-file-name');
        var uploadName = root.querySelector('.mpwcfg-upload-name');
        var uploadBox = root.querySelector('.mpwcfg-upload-file');
        var uploadFileName = root.querySelector('.mpwcfg-upload-file-name');
        var uploadRemove = root.querySelector('.mpwcfg-upload-file__remove');
        var uploadInput = root.querySelector('.mpwcfg-upload-input');
        var dropzone = root.querySelector('.mpwcfg-combo-dropzone');
        var profiles = [];
        var level1Label = root.dataset.level1Label || 'Cantidad de hojas';
        var level2Label = root.dataset.level2Label || 'Cantidad';
        try { profiles = JSON.parse((root.querySelector('.mpwcfg-combo-profiles-data') || {}).textContent || '[]'); } catch(e) { profiles = []; }
        var picked = {};
        var busy = false, completedDestination = null;
        var idleLabel = addText ? addText.textContent : 'Agregar al carrito';
        var lastCalc = {total:0, complete:false, price_token:''};

        function feedbackMsg(t, err) { if (!feedback) return; feedback.textContent=t||''; feedback.classList.toggle('is-error',!!err); }
        function setAddEnabled(enabled) {
            if (!add || completedDestination || busy) return;
            add.disabled = !enabled;
            add.classList.toggle('is-ready', !!enabled);
        }
        function profileForFormat() {
            var idx = Number(picked.combo_level_0);
            for (var i=0;i<profiles.length;i++) if (Number(profiles[i].format_index) === idx) return profiles[i];
            return null;
        }
        function selectedText(step) {
            var b = step && step.querySelector('.mpwcfg-combo-option-btn.is-selected');
            return b ? (b.querySelector('span') ? b.querySelector('span').textContent.trim() : '') : '';
        }
        function makeStep(index, label, options) {
            var sec = document.createElement('section');
            sec.className='mpwcfg-combo-step'; sec.dataset.level=String(index); sec.setAttribute('aria-label',label);
            var html='<div class="mpwcfg-combo-step-head"><div><span>'+String(index+1).padStart(2,'0')+'</span><h3>'+esc(label)+'</h3></div><small class="mpwcfg-combo-step-current"></small></div><div class="mpwcfg-combo-options">';
            (options||[]).forEach(function(opt,i){ html+='<button type="button" class="mpwcfg-combo-option-btn" data-index="'+i+'"><span>'+esc(opt.label||'')+'</span>'+(opt.price != null ? '<small>'+clp(opt.price)+'</small>' : '')+'</button>'; });
            html+='</div></section>'; sec.innerHTML=html; return sec;
        }
        function rebuildDependentSteps() {
            if (!stepsWrap) return;
            Array.prototype.slice.call(stepsWrap.querySelectorAll('.mpwcfg-combo-step')).forEach(function(s){ if (s !== formatStep) s.remove(); });
            var profile=profileForFormat();
            if (!profile) return;
            var tiers=(profile.tiers||[]).map(function(t){return {label:t.label||''};});
            var tierStep=makeStep(1,level1Label,tiers);
            stepsWrap.appendChild(tierStep);
            var tierIdx=Number(picked.combo_level_1);
            if (picked.combo_level_1 != null && profile.tiers && profile.tiers[tierIdx]) {
                var rows=(profile.tiers[tierIdx].rows||[]).map(function(r){return {label:r.quantity||'',price:Number(r.price||0)};});
                var qtyStep=makeStep(2,level2Label,rows);
                stepsWrap.appendChild(qtyStep);
                if (picked.combo_level_2 != null && rows[picked.combo_level_2]) {
                    var qb=qtyStep.querySelectorAll('.mpwcfg-combo-option-btn')[picked.combo_level_2]; if(qb) qb.classList.add('is-selected');
                }
            }
            var tierButton=tierStep.querySelectorAll('.mpwcfg-combo-option-btn')[picked.combo_level_1];
            if(tierButton) { tierButton.classList.add('is-selected'); tierStep.querySelector('.mpwcfg-combo-step-current').textContent=selectedText(tierStep); }
            bindDynamicSteps(); updateStates();
        }
        function clearAfter(level) {
            if (level < 1) delete picked.combo_level_1;
            if (level < 2) delete picked.combo_level_2;
        }
        function bindDynamicSteps() {
            Array.prototype.slice.call(stepsWrap.querySelectorAll('.mpwcfg-combo-step')).forEach(function(step){
                if (step.dataset.bound) return;
                step.dataset.bound='1';
                step.querySelectorAll('.mpwcfg-combo-option-btn').forEach(function(btn){
                    btn.addEventListener('click',function(){
                        var i=Number(step.dataset.level); if(btn.disabled) return;
                        if(i===1){ delete picked.combo_level_2; }
                        step.querySelectorAll('.mpwcfg-combo-option-btn').forEach(function(b){b.classList.remove('is-selected');});
                        btn.classList.add('is-selected'); picked['combo_level_'+i]=Number(btn.dataset.index);
                        step.querySelector('.mpwcfg-combo-step-current').textContent=selectedText(step);
                        if(i===1) rebuildDependentSteps();
                        updateStates(); refresh();
                    });
                });
            });
        }
        function updateStates() {
            var steps=Array.prototype.slice.call(stepsWrap.querySelectorAll('.mpwcfg-combo-step'));
            steps.forEach(function(step,i){
                var enabled = i===0 || picked['combo_level_'+(i-1)] != null;
                step.classList.toggle('is-enabled',enabled); step.classList.toggle('is-complete',picked['combo_level_'+i] != null);
                step.querySelectorAll('.mpwcfg-combo-option-btn').forEach(function(b){b.disabled=!enabled;});
            });
            Array.prototype.forEach.call(progress,function(el,i){
                el.classList.toggle('done',picked['combo_level_'+i] != null); el.classList.toggle('active',picked['combo_level_'+i] == null && (i===0 || picked['combo_level_'+(i-1)] != null));
            });
            renderSummary(lastCalc);
        }
        function refresh() {
            post(root,'mpwcfg_price',picked).then(function(resp){
                lastCalc=resp&&resp.success ? (resp.data||{total:0,complete:false,price_token:''}) : {total:0,complete:false,price_token:''};
                renderSummary(lastCalc);
                feedbackMsg(lastCalc.complete?'':'Completa todas las opciones para continuar.',!lastCalc.complete&&resp&&!resp.success);
            }).catch(function(){lastCalc={total:0,complete:false,price_token:''};renderSummary(lastCalc);feedbackMsg('No se pudo calcular el precio. Inténtalo de nuevo.',true);});
        }
        function renderSummary(calc) {
            if(!summary||!lines) return;
            summary.hidden=false; lines.innerHTML='';
            var steps=Array.prototype.slice.call(stepsWrap.querySelectorAll('.mpwcfg-combo-step'));
            steps.forEach(function(step,i){
                var row=document.createElement('div'); row.className='mpwcfg-summary__config-row';
                row.innerHTML='<div class="mpwcfg-summary__config-main"><span>'+esc(step.querySelector('h3').textContent.trim())+'</span><b>'+esc(selectedText(step)||'Pendiente')+'</b></div><button type="button" class="mpwcfg-summary__edit" data-edit-step="'+i+'">Cambiar</button>'; 
                lines.appendChild(row);
            });
            if(fileSummary){fileSummary.hidden=false;fileSummary.innerHTML='<span>Diseño</span><b>'+esc(fileName&&fileName.value?fileName.value:'Sin archivo')+'</b>';}
            if(total) total.textContent=clp(calc&&calc.total||0);
            setAddEnabled(!!(calc&&calc.complete));
            updateContactLink();
        }
        function updateContactLink(){
            var link=root.querySelector('.mpwcfg-combo-contact'); if(!link) return;
            try{var url=new URL(link.getAttribute('href'),window.location.href), text='Hola! Quiero cotizar/pedir: '+(root.dataset.productName||'Producto')+'\n';
                Array.prototype.slice.call(stepsWrap.querySelectorAll('.mpwcfg-combo-step')).forEach(function(step){var v=selectedText(step);if(v)text+='• '+step.querySelector('h3').textContent+': '+v+'\n';});
                if(fileName&&fileName.value)text+='• Diseño: '+fileName.value+'\n'; if(total&&total.textContent)text+='Total: '+total.textContent; url.searchParams.set('text',text);link.href=url.toString();}catch(e){}
        }
        function bindFormat(){
            if(!formatStep)return;
            formatStep.querySelectorAll('.mpwcfg-combo-option-btn').forEach(function(btn){btn.addEventListener('click',function(){
                if(btn.disabled)return; formatStep.querySelectorAll('.mpwcfg-combo-option-btn').forEach(function(b){b.classList.remove('is-selected');}); btn.classList.add('is-selected');
                picked.combo_level_0=Number(btn.dataset.index); clearAfter(0); formatStep.querySelector('.mpwcfg-combo-step-current').textContent=selectedText(formatStep);
                rebuildDependentSteps(); updateStates(); refresh();
            });});
        }
        if(lines) lines.addEventListener('click',function(e){var b=e.target.closest('.mpwcfg-summary__edit');if(!b)return;var i=Number(b.dataset.editStep),step=stepsWrap.querySelector('.mpwcfg-combo-step[data-level="'+i+'"]');if(step){step.scrollIntoView({behavior:'smooth',block:'center'});feedbackMsg('Puedes corregir esta selección. Las demás opciones permanecen visibles.');}});

        function clearUpload(){if(fileUrl)fileUrl.value='';if(fileName)fileName.value='';if(uploadInput)uploadInput.value='';if(uploadName)uploadName.textContent='';if(uploadBox)uploadBox.hidden=true;if(dropzone)dropzone.classList.remove('is-done','is-loading');renderSummary(lastCalc);updateContactLink();}
        function upload(file){if(!file)return;var ext=String(file.name||'').split('.').pop().toLowerCase();if(ALLOWED.indexOf(ext)===-1||file.size>MAX_BYTES){feedbackMsg('Archivo no válido. Usa PDF, PNG, JPG, AI o EPS de hasta 25 MB.',true);return;}if(dropzone)dropzone.classList.add('is-loading');if(uploadName)uploadName.textContent='Subiendo…';var fd=new FormData();fd.append('action','mpwcfg_upload');fd.append('nonce',DATA.nonce||'');fd.append('file',file);fetch(DATA.ajax_url,{method:'POST',body:fd}).then(function(r){return r.json();}).then(function(j){if(!j||!j.success)throw new Error(j&&j.data&&j.data.message||'No se pudo subir el archivo.');if(fileUrl)fileUrl.value=j.data.url||'';if(fileName)fileName.value=j.data.name||file.name;if(uploadName)uploadName.textContent='Diseño cargado';if(uploadFileName)uploadFileName.textContent=j.data.name||file.name;if(uploadBox)uploadBox.hidden=false;if(dropzone){dropzone.classList.remove('is-loading');dropzone.classList.add('is-done');}renderSummary(lastCalc);updateContactLink();feedbackMsg('Diseño cargado correctamente.');}).catch(function(err){if(dropzone)dropzone.classList.remove('is-loading','is-done');if(uploadName)uploadName.textContent='';feedbackMsg(err.message||'No se pudo subir el archivo.',true);});}
        if(dropzone&&uploadInput){dropzone.addEventListener('click',function(){uploadInput.click();});dropzone.addEventListener('keydown',function(e){if(e.key==='Enter'||e.key===' '){e.preventDefault();uploadInput.click();}});uploadInput.addEventListener('change',function(){if(uploadInput.files&&uploadInput.files[0])upload(uploadInput.files[0]);});['dragenter','dragover'].forEach(function(ev){dropzone.addEventListener(ev,function(e){e.preventDefault();dropzone.classList.add('is-dragover');});});['dragleave','dragend'].forEach(function(ev){dropzone.addEventListener(ev,function(e){e.preventDefault();dropzone.classList.remove('is-dragover');});});dropzone.addEventListener('drop',function(e){e.preventDefault();dropzone.classList.remove('is-dragover');var f=e.dataTransfer&&e.dataTransfer.files&&e.dataTransfer.files[0];if(f)upload(f);});}
        if(uploadRemove)uploadRemove.addEventListener('click',clearUpload);
        if(add)add.addEventListener('click',function(){if(completedDestination)return;if(busy)return;busy=true;post(root,'mpwcfg_add_to_cart',picked,{file_url:fileUrl?fileUrl.value:'',file_name:fileName?fileName.value:'',price_token:(lastCalc&&lastCalc.price_token)||'',variation_id:(root.querySelector('input[name=variation_id]')||{}).value||'',variation:(function(){var v={};root.querySelectorAll('select[name^="attribute_"], input[name^="attribute_"]:checked').forEach(function(f){if(f.value)v[f.name]=f.value;});return v;})()}).then(function(resp){if(!resp||!resp.success)throw new Error(resp&&resp.data&&resp.data.message||'No se pudo agregar.');var destination=resp.data.cart_url||root.dataset.cartUrl||'';completedDestination=destination||null;if(cartLink&&destination){cartLink.href=destination;cartLink.hidden=false;}busy=false;feedbackMsg('');}).catch(function(err){busy=false;feedbackMsg(err.message||'No se pudo agregar.',true);});});

        bindFormat();
        if(formatStep && picked.combo_level_0 == null) formatStep.querySelector('.mpwcfg-combo-step-current').textContent='';
        summary.hidden=false; updateStates(); renderSummary(lastCalc);
    }
    document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.mpwcfg-configurator--combinations').forEach(init);});
})();
