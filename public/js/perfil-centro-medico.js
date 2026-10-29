(function(){
    'use strict';
    document.addEventListener('DOMContentLoaded',function(){
        var config=window.perfilCentroConfig;
        if(!config)return;
        document.querySelectorAll('[data-centro-form]').forEach(function(form){
            var region=form.querySelector('[name="region"]'),ciudad=form.querySelector('[name="ciudad"]');
            function filtrar(){if(!region||!ciudad)return;Array.from(ciudad.options).forEach(function(o){o.hidden=!!o.value && !!region.value && o.dataset.region!==region.value;});if(ciudad.selectedOptions[0]?.hidden)ciudad.value='';}
            if(region){region.addEventListener('change',filtrar);filtrar();}
            form.addEventListener('reset',function(){setTimeout(filtrar,0);});
            var guardando=false;
            form.addEventListener('submit',async function(event){
                event.preventDefault();
                if(guardando)return;
                if(form.dataset.centroForm==='eliminar-cuenta' && !window.confirm('¿Eliminar esta cuenta institucional?'))return;
                var datos=new FormData(form),aviso=form.querySelector('[data-resultado]');
                // Una dirección aún no registrada puede completarse en otra edición.
                if(!datos.get('ciudad') && !datos.get('direccion') && !datos.get('numero_dir'))['region','ciudad','direccion','numero_dir'].forEach(function(c){datos.delete(c);});
                if(!datos.get('id_responsable'))datos.delete('id_responsable');
                var controles=Array.from(form.querySelectorAll('button,input,select,textarea'));
                var anteriores=controles.map(function(el){return el.disabled;});
                guardando=true;controles.forEach(function(el){el.disabled=true;});
                aviso.className='alert d-none';aviso.textContent='';
                try{
                    var respuesta=await fetch(form.action,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':config.csrf},body:datos});
                    var resultado=await respuesta.json().catch(function(){return {};});
                    if(!respuesta.ok || !(resultado.success || resultado.estado===1))throw new Error(Object.values(resultado.errors||{}).flat().join('\n') || resultado.message || resultado.msj || 'No fue posible guardar los cambios.');
                    aviso.className='alert alert-success';aviso.textContent=resultado.message || resultado.msj || resultado.mensaje || 'Cambios guardados.';
                    if(form.dataset.recargar==='1'){window.location.reload();return;}
                    if(form.dataset.centroForm==='foto')document.getElementById('foto-centro').src=resultado.foto_url;
                    if(form.dataset.centroForm==='eliminar-foto')document.getElementById('foto-centro').src=config.fotoPredeterminada;
                    if(resultado.responsable){var id=form.querySelector('[name="id_responsable"]');if(id)id.value=resultado.responsable.id;}
                    if(form.dataset.centroForm==='contrasena')form.reset();
                    form.querySelectorAll('input,select,textarea').forEach(function(el){
                        if(el.type==='file'){el.value='';return;}
                        if(el.type==='password'){el.value='';el.defaultValue='';return;}
                        if(el.tagName==='SELECT')Array.from(el.options).forEach(function(o){o.defaultSelected=o.selected;});
                        else el.defaultValue=el.value;
                    });
                }catch(error){aviso.className='alert alert-danger';aviso.textContent=error.message;}
                finally{guardando=false;controles.forEach(function(el,i){el.disabled=anteriores[i];});}
            });
        });
    });
})();
