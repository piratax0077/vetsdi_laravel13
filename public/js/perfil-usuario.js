(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var config = window.perfilUsuarioConfig;
        if (!config) return;
        var asistente = config.tipo === 'asistente';
        var pendientes = new Set();
        var grupos = {personales:['info_basica-1','info_basica-2'],contacto:['info_contacto_1','info_contacto_2'],direccion:['info_residencial_1','info_residencial_2'],contrasena:['pass_personal_1','pass_personal_2']};
        function valor(id) { var el=document.getElementById(id); return el ? el.value : ''; }
        function texto(id, contenido) { var el=document.getElementById(id); if(el) el.textContent=contenido == null ? '' : contenido; }
        function seleccionado(id) {var el=document.getElementById(id);return el && el.selectedOptions.length ? el.selectedOptions[0].textContent.trim() : '';}
        function cerrar(tipo) {var ids=grupos[tipo];document.getElementById(ids[0])?.classList.add('show');document.getElementById(ids[1])?.classList.remove('show');}
        function mensaje(tipo, contenido) {
            var contenedor=document.getElementById(grupos[tipo][1]);
            var aviso=contenedor.querySelector('[data-error-perfil]');
            if(!aviso){aviso=document.createElement('div');aviso.dataset.errorPerfil='1';aviso.className='alert alert-danger';aviso.setAttribute('role','alert');contenedor.prepend(aviso);}
            aviso.textContent=contenido; aviso.hidden=!contenido;
        }
        function datos(tipo) {
            if(tipo==='contacto') return {email:valor('editar_email'),telefono_uno:valor('editar_telefono_uno')};
            if(tipo==='direccion') return asistente ? {id_ciudad:valor('ciudad_agregar'),region:valor('region_agregar'),direccion:valor('direccion'),numero_dir:valor('numero_dir')} : {ciudad:valor('perfil_ciudad'),region:valor('perfil_region'),direccion:valor('perfil_dire'),numero_dir:valor('perfil_numero_dir')};
            if(tipo==='contrasena') return {contrasena_actual:valor('contrasena_actual'),password_registro:valor('password_registro'),password_confirmacion_registro:valor('password_confirmacion_registro')};
            var d={rut:valor('editar_rut'),apellido_uno:valor('editar_apellido_uno'),apellido_dos:valor('editar_apellido_dos'),sexo:valor('editar_sexo')};
            if(asistente){d.nombres=valor('editar_nombre');d.nacimiento=valor('editar_nacimiento') || null;}
            else {d.nombre=valor('editar_nombres');['id_especialidad','id_tipo_especialidad','id_sub_tipo_especialidad','id_tipo_atencion','num_colegio'].forEach(function(id){if(document.getElementById(id))d[id]=valor(id)||null;});}
            return d;
        }
        function recordar(form) {form?.querySelectorAll('input,select,textarea').forEach(function(el){if(el.tagName==='SELECT')Array.from(el.options).forEach(function(o){o.defaultSelected=o.selected;});else el.defaultValue=el.value;});}
        function actualizar(tipo,resultado) {
            var perfil=resultado[config.tipo] || {};
            if(tipo==='personales') {
                ['rut','apellido_uno','apellido_dos','num_colegio'].forEach(function(c){texto('ver_'+c,perfil[c]);});
                texto('ver_nombre',perfil[asistente?'nombres':'nombre']);
                texto('ver_sexo',({M:'Masculino',F:'Femenino',O:'Otro'})[perfil.sexo] || 'Sin registro');
                texto('ver_nacimiento',perfil.fecha_nac ? perfil.fecha_nac.slice(0,10).split('-').reverse().join('/') : 'Sin registro');
                ['id_especialidad','id_tipo_especialidad','id_sub_tipo_especialidad','id_tipo_atencion'].forEach(function(id){texto('ver_'+id,valor(id)?seleccionado(id):'Sin registro');});
            } else if(tipo==='contacto') {texto('ver_email',perfil.email);texto('ver_telefono_uno',perfil.telefono_uno);}
            else if(tipo==='direccion') {texto('ver_region',seleccionado(asistente?'region_agregar':'perfil_region'));texto('ver_ciudad',resultado.ciudad?.nombre);texto('ver_direccion',[resultado.direccion?.direccion,resultado.direccion?.numero_dir].filter(Boolean).join(' #'));}
        }
        async function guardar(tipo) {
            if(pendientes.has(tipo))return false;
            var panel=document.getElementById(grupos[tipo][1]),form=panel?.querySelector('form');
            if(!panel)return false;
            var controles=Array.from(panel.querySelectorAll('button,input,select,textarea'));
            var estados=controles.map(function(el){return el.disabled;});
            var cuerpo=datos(tipo);
            mensaje(tipo,'');pendientes.add(tipo);controles.forEach(function(el){el.disabled=true;});
            try {
                var respuesta=await fetch(config[tipo],{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':config.csrf},body:JSON.stringify(cuerpo)});
                var resultado=await respuesta.json().catch(function(){return {};});
                if(!respuesta.ok || !resultado.success)throw new Error(Object.values(resultado.errors||{}).flat().join('\n') || resultado.message || 'No fue posible guardar los cambios.');
                actualizar(tipo,resultado);
                if(tipo==='contrasena')form?.reset(); else recordar(form);
                cerrar(tipo);
                if(window.swal)window.swal({title:resultado.message,icon:'success',buttons:'Aceptar'});
                return true;
            } catch(error) {mensaje(tipo,error.message);return false;}
            finally {pendientes.delete(tipo);controles.forEach(function(el,i){el.disabled=estados[i];});}
        }
        window.editar_asistente_datos_personales=function(){return guardar('personales');};
        window.editar_profesional_datos_personales=window.editar_asistente_datos_personales;
        window.editar_asistente_datos_contacto=function(){return guardar('contacto');};
        window.editar_datos_contacto_profesional=window.editar_asistente_datos_contacto;
        window.editar_asistente_datos_residencia=function(){return guardar('direccion');};
        window.editar_datos_residencia_profesional=window.editar_asistente_datos_residencia;
        Object.keys(grupos).forEach(function(tipo){
            var panel=document.getElementById(grupos[tipo][1]),form=panel?.querySelector('form');
            if(!form)return;
            form.addEventListener('submit',function(event){event.preventDefault();event.stopImmediatePropagation();guardar(tipo);},true);
            form.querySelectorAll('button').forEach(function(btn){if(btn.textContent.includes('Cancelar'))btn.addEventListener('click',function(event){event.preventDefault();event.stopImmediatePropagation();form.reset();mensaje(tipo,'');cerrar(tipo);},true);});
        });
    });
})();
