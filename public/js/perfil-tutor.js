(function(){
    'use strict';
    document.addEventListener('DOMContentLoaded',function(){
        var config=window.perfilTutorConfig;
        if(!config)return;
        var grupos={todo:['info_basica-1','pinfo_basica_2'],contacto:['info_contacto_1','info_contacto_2'],direccion:['info_residencial_1','info_residencial_2'],contrasena:['pass_personal_1','pass_personal_2']};
        var ocupados=new Set();
        function cerrar(tipo){document.getElementById(grupos[tipo][0])?.classList.add('show');document.getElementById(grupos[tipo][1])?.classList.remove('show');}
        function recordar(form){form.querySelectorAll('input,select,textarea').forEach(function(el){if(el.type==='radio'||el.type==='checkbox')el.defaultChecked=el.checked;else if(el.tagName==='SELECT')Array.from(el.options).forEach(function(o){o.defaultSelected=o.selected;});else el.defaultValue=el.value;});}
        async function guardar(tipo){
            if(ocupados.has(tipo))return false;
            var panel=document.getElementById(grupos[tipo][1]),form=panel?.querySelector('form');if(!form)return false;
            var datos={};form.querySelectorAll('input,select,textarea').forEach(function(el){
                if(!el.name||el.disabled||(['radio','checkbox'].includes(el.type)&&!el.checked))return;
                var nombre=el.name==='Perfil_email'?'perfil_email':el.name==='Perfil_fono'?'perfil_fono':el.name;
                datos[nombre]=el.value || null;
            });
            if(tipo==='todo' && !datos.perfil_dire && !datos.perfil_ciudad && !datos.perfil_numero_dir)['perfil_dire','perfil_ciudad','perfil_region','perfil_numero_dir'].forEach(function(c){delete datos[c];});
            var aviso=panel.querySelector('[data-tutor-resultado]');if(!aviso){aviso=document.createElement('div');aviso.dataset.tutorResultado='1';aviso.setAttribute('role','status');panel.prepend(aviso);}
            aviso.className='alert d-none';aviso.textContent='';
            var controles=Array.from(form.querySelectorAll('button,input,select,textarea')),estados=controles.map(function(el){return el.disabled;});
            ocupados.add(tipo);controles.forEach(function(el){el.disabled=true;});
            try{
                var respuesta=await fetch(config[tipo],{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':config.csrf},body:JSON.stringify(datos)});
                var resultado=await respuesta.json().catch(function(){return {};});
                if(!respuesta.ok||!resultado.success)throw new Error(Object.values(resultado.errors||resultado.error||{}).flat().join('\n')||resultado.message||'No fue posible guardar el perfil.');
                var p=resultado.paciente,d=resultado.direccion;
                if(p){
                    var mostrar={nombres:p.nombres,apellido_uno:p.apellido_uno,apellido_dos:p.apellido_dos,email:p.email,telefono_uno:p.telefono_uno,sexo:({M:'Hombre',F:'Mujer',O:'Otro',N:'Sin registro'})[p.sexo]||'Sin registro',fecha_nac:p.fecha_nac?p.fecha_nac.slice(0,10).split('-').reverse().join('/'):'Sin registro'};
                    if(d){mostrar.direccion=[d.direccion,d.numero_dir].filter(Boolean).join(' #');mostrar.region=resultado.region?.nombre;mostrar.ciudad=resultado.ciudad?.nombre;}
                    document.querySelectorAll('[data-tutor-mostrar]').forEach(function(el){if(Object.hasOwn(mostrar,el.dataset.tutorMostrar))el.textContent=mostrar[el.dataset.tutorMostrar]??'';});
                    // Mantener sincronizados los otros editores sin descartar sus borradores.
                    var valores={Perfil_email:p.email,Perfil_fono:p.telefono_uno};if(d)Object.assign(valores,{perfil_dire:d.direccion,perfil_numero_dir:d.numero_dir,perfil_ciudad:d.id_ciudad});
                    Object.keys(grupos).forEach(function(otro){if(otro===tipo)return;var formulario=document.getElementById(grupos[otro][1])?.querySelector('form');formulario?.querySelectorAll('input,select,textarea').forEach(function(el){if(Object.hasOwn(valores,el.name)&&el.value===el.defaultValue){el.value=valores[el.name]??'';el.defaultValue=el.value;}});});
                }
                if(tipo==='contrasena')form.querySelectorAll('input[type="password"]').forEach(function(el){el.value='';el.defaultValue='';});
                recordar(form);cerrar(tipo);
                if(window.swal)window.swal({title:resultado.message||'Cambios guardados.',icon:'success',buttons:'Aceptar'});
                return true;
            }catch(error){aviso.className='alert alert-danger';aviso.textContent=error.message;return false;}
            finally{ocupados.delete(tipo);controles.forEach(function(el,i){el.disabled=estados[i];});}
        }
        window.guardar_perfil_paciente=function(){return guardar('todo');};
        window.editar_paciente_datos_personales=window.guardar_perfil_paciente;
        window.editar_paciente_datos_contacto=function(){return guardar('contacto');};
        window.editar_paciente_datos_residencia=function(){return guardar('direccion');};
        window.cambiar_contrasena_perfil=function(){return guardar('contrasena');};
        window.mostrar_error_contacto_tutor=function(respuesta){
            var datos=respuesta.responseJSON||{};
            var texto=Object.values(datos.errors||{}).flat().join('\n')||datos.message||'No fue posible guardar el contacto.';
            if(window.swal)window.swal({title:'Contacto de emergencia',text:texto,icon:'error',buttons:'Aceptar'});
        };
        window.recargar_contactos_tutor=async function(){
            try{
                var r=await fetch(config.contactos,{headers:{'Accept':'application/json'}});if(!r.ok)throw Error('No fue posible actualizar la lista de contactos.');
                var datos=await r.json(),tabla=document.getElementById('contactos_emergencia');if(!tabla)return;
                var filas=datos.registros.map(function(c){
                    var tr=document.createElement('tr');
                    [c.prioridad,[c.nombre,c.apellido_uno,c.apellido_dos].filter(Boolean).join(' '),c.pivot?.relacion||c.parentezco].forEach(function(v){var td=document.createElement('td');td.textContent=v??'';tr.appendChild(td);});
                    var acciones=document.createElement('td');
                    [['Información','info','#info_contacto_emergencia'],['Editar','warning','#editar_contacto_emergencia'],['Eliminar','danger',null]].forEach(function(a){
                        var btn=document.createElement('button');btn.type='button';btn.className='btn btn-'+a[1]+' btn-sm mr-1';btn.textContent=a[0];
                        if(a[2]){btn.setAttribute('data-toggle','modal');btn.setAttribute('data-target',a[2]);btn.addEventListener('click',function(){window.cargar_datos_contacto(c.id);});}
                        else btn.addEventListener('click',function(){window.eliminar_contacto_paciente(c.id,datos.id_paciente);});
                        acciones.appendChild(btn);
                    });tr.appendChild(acciones);return tr;
                });
                if(window.jQuery?.fn?.DataTable?.isDataTable(tabla))window.jQuery(tabla).DataTable().clear().rows.add(filas).draw(false);
                else{var tbody=tabla.querySelector('tbody')||tabla.createTBody();tbody.replaceChildren(...filas);}
            }catch(error){window.mostrar_error_contacto_tutor({responseJSON:{message:error.message}});}
        };
        window.buscar_ciudad_residencia=function(){
            var panel=document.getElementById('info_residencial_2'),region=panel.querySelector('[name="perfil_region"]'),ciudad=panel.querySelector('[name="perfil_ciudad"]');
            fetch(config.ciudades+'?'+new URLSearchParams({region:region.value}),{headers:{'Accept':'application/json'}}).then(function(r){if(!r.ok)throw Error();return r.json();}).then(function(data){ciudad.replaceChildren(new Option('Seleccione',''));(data.registros||data.ciudades||data).forEach(function(c){ciudad.add(new Option(c.nombre,c.id));});}).catch(function(){ciudad.replaceChildren(new Option('No fue posible cargar las comunas',''));});
        };
        Object.keys(grupos).forEach(function(tipo){var form=document.getElementById(grupos[tipo][1])?.querySelector('form');if(!form)return;form.addEventListener('submit',function(e){e.preventDefault();e.stopImmediatePropagation();guardar(tipo);},true);form.querySelectorAll('button').forEach(function(btn){if(btn.textContent.includes('Cancelar'))btn.addEventListener('click',function(e){e.preventDefault();e.stopImmediatePropagation();form.reset();cerrar(tipo);},true);});});
    });
})();
