(function () {
    'use strict';
    document.addEventListener('DOMContentLoaded', function () {
        var config=window.otrosTrabajadoresConfig;
        if(!config)return;
        var categorias=new Map(config.categorias.map(function(c){return [String(c.id),c];})),volverTrabajador=false;
        function form(id){return document.getElementById(id);}
        function modal(id){window.jQuery('#'+id).appendTo(document.body).modal('show');}
        function aviso(el,texto,ok){el.className='alert '+(ok?'alert-success':'alert-danger');el.textContent=texto;}
        function uuid(){var b=crypto.getRandomValues(new Uint8Array(16));b[6]=(b[6]&15)|64;b[8]=(b[8]&63)|128;var s=Array.from(b,function(x){return x.toString(16).padStart(2,'0');}).join('');return s.slice(0,8)+'-'+s.slice(8,12)+'-'+s.slice(12,16)+'-'+s.slice(16,20)+'-'+s.slice(20);}
        function campo(f,n,v){if(f.elements[n])f.elements[n].value=v==null?'':String(v);}
        function preparar(f,datos){f.reset();f.querySelector('[data-resultado]').className='alert d-none';Array.from(f.elements).forEach(function(e){if(e.name && e.name!=='_token' && e.name!=='lugares[]')campo(f,e.name,datos[e.name]);});}
        function recargar(){var url=new URL(window.location.href);url.searchParams.set('tab',config.tab);url.searchParams.set('id_institucion',config.institucion);window.location.assign(url.href);}
        async function peticion(url,datos){
            var opciones={headers:{Accept:'application/json','X-CSRF-TOKEN':config.csrf}};
            if(datos){datos.set('id_institucion',config.institucion);opciones.method='POST';opciones.body=datos;}
            else {var destino=new URL(url,window.location.href);destino.searchParams.set('id_institucion',config.institucion);url=destino.href;}
            var respuesta=await fetch(url,opciones),resultado=await respuesta.json().catch(function(){return {};});
            if(!respuesta.ok || resultado.estado!==1)throw new Error(Object.values(resultado.errors||{}).flat().join('\n')||resultado.msj||resultado.message||'No fue posible completar la operación.');
            return resultado;
        }
        function detalle(id){return peticion(config.detalle.replace('__ID__',id));}
        function opcionesCategorias(){
            var select=form('otro-categoria'),actual=select.value,edicion=form('otra-categoria-id'),elegida=edicion.value;
            select.replaceChildren(new Option('Seleccione',''));edicion.replaceChildren(new Option('Crear nueva categoría',''));
            categorias.forEach(function(c){if(Number(c.activa))select.add(new Option(c.nombre,c.id));edicion.add(new Option(c.nombre,c.id));});
            select.value=actual;edicion.value=elegida;
        }
        function abrirTrabajador(t){
            var f=form('formOtroTrabajador');preparar(f,t||{activo:1});
            Array.from(f.elements['lugares[]'].options).forEach(function(o){o.selected=t? t.lugares.map(String).includes(o.value):config.lugares.length===1;});
            form('otro-trabajador-titulo').textContent=t?'Editar trabajador':'Agregar trabajador';modal('modalOtroTrabajador');
        }
        function abrirContrato(trabajador,contrato){
            var f=form('formOtroContrato');preparar(f,contrato||{trabajador_id:trabajador||'',tipo:'indefinido',fecha_inicio:config.hoy,remuneracion:0});modal('modalOtroContrato');
        }
        function textoNodo(tipo,texto,clase){var el=document.createElement(tipo);el.textContent=texto==null||texto===''?'—':String(texto);if(clase)el.className=clase;return el;}
        function mostrarInfo(d){
            var t=d.trabajador,contenedor=form('otro-info-datos');contenedor.replaceChildren();
            var sucursales=config.lugares.filter(function(l){return t.lugares.map(String).includes(String(l.id));}).map(function(l){return l.nombre;}).join(', ');
            Object.entries({'Nombre':t.nombres+' '+t.apellido_uno+' '+(t.apellido_dos||''),'RUT':t.rut,'Categoría':categorias.get(String(t.categoria_id))?.nombre,'Estado':Number(t.activo)?'Activo':'Desvinculado','Sucursales':sucursales,'Correo':t.email,'Teléfono':t.telefono,'Dirección':t.direccion,'Banco':t.banco,'Tipo de cuenta':t.tipo_cuenta,'Número de cuenta':t.numero_cuenta,'Observaciones':t.observaciones}).forEach(function(par){contenedor.append(textoNodo('dt',par[0],'col-sm-4'),textoNodo('dd',par[1],'col-sm-8 text-wrap'));});
            var contratos=form('otro-info-contratos');contratos.replaceChildren();
            d.contratos.forEach(function(c){var p=textoNodo('p',(config.tiposContrato[c.tipo]||c.tipo)+' · '+c.fecha_inicio+(c.fecha_fin?' a '+c.fecha_fin:'')+' · '+c.estado+' · $'+Number(c.remuneracion).toLocaleString('es-CL'));contratos.append(p);});
            if(!d.contratos.length)contratos.append(textoNodo('p','Sin contratos registrados.','text-muted'));
            var mensajes=form('otro-info-mensajes');mensajes.replaceChildren();
            d.mensajes.forEach(function(m){var caja=document.createElement('div');caja.className='border rounded p-2 mb-2';caja.append(textoNodo('strong',m.asunto),textoNodo('p',m.created_at+' · '+m.destinatario+' · '+m.estado,'small text-muted mb-1'),textoNodo('p',m.mensaje,'text-wrap mb-0'));mensajes.append(caja);});
            if(!d.mensajes.length)mensajes.append(textoNodo('p','Sin mensajes registrados.','text-muted'));
            modal('modalOtroInfo');
        }
        document.addEventListener('click',async function(event){
            var boton=event.target.closest('[data-otro-accion]');if(!boton || boton.disabled)return;
            var accion=boton.dataset.otroAccion,id=boton.dataset.id;
            try {
                if(accion==='nuevo'){abrirTrabajador(null);return;}
                if(accion==='categorias'){
                    volverTrabajador=window.jQuery('#modalOtroTrabajador').hasClass('show');
                    if(volverTrabajador)window.jQuery('#modalOtroTrabajador').modal('hide');
                    preparar(form('formOtroCategoria'),{activa:1});modal('modalOtroCategoria');return;
                }
                if(accion==='contrato-nuevo'){abrirContrato(null,null);return;}
                boton.disabled=true;
                if(accion==='estado'){
                    if(!window.confirm(boton.dataset.activo==='1'?'¿Reactivar al trabajador?':'¿Desvincular al trabajador? Se conservará su información.'))return;
                    var datos=new FormData();datos.set('activo',boton.dataset.activo);await peticion(config.estado.replace('__ID__',id),datos);recargar();return;
                }
                var d=await detalle(id);
                if(accion==='editar')abrirTrabajador(d.trabajador);
                if(accion==='info')mostrarInfo(d);
                if(accion==='contrato'){
                    var contrato=boton.dataset.contrato?d.contratos.find(function(c){return String(c.id)===boton.dataset.contrato;}):d.contratos.find(function(c){return c.estado==='vigente';});abrirContrato(id,contrato);
                }
                if(accion==='terminar'){preparar(form('formOtroTermino'),{id:boton.dataset.contrato,fecha_termino:config.hoy});modal('modalOtroTermino');}
                if(accion==='mensaje'){
                    preparar(form('formOtroMensaje'),{trabajador_id:id,token:uuid()});form('otro-mensaje-destinatario').textContent=d.trabajador.email||'Sin correo';modal('modalOtroMensaje');
                }
            }catch(error){aviso(form('otros-trabajadores-resultado'),error.message,false);}
            finally{boton.disabled=false;}
        });
        form('otra-categoria-id').addEventListener('change',function(){var c=categorias.get(this.value);campo(form('formOtroCategoria'),'nombre',c?.nombre||'');campo(form('formOtroCategoria'),'activa',c?Number(c.activa):1);});
        document.querySelectorAll('[data-otro-form]').forEach(function(f){
            var enviando=false;
            f.addEventListener('submit',async function(event){
                event.preventDefault();if(enviando)return;
                var datos=new FormData(f),accion=f.dataset.otroForm,resultado=f.querySelector('[data-resultado]'),boton=f.querySelector('[type="submit"]');
                enviando=true;boton.disabled=true;
                try {
                    var url=config[accion];if(accion==='mensaje')url=url.replace('__ID__',datos.get('trabajador_id'));if(accion==='terminar')url=url.replace('__ID__',datos.get('id'));
                    var respuesta=await peticion(url,datos);
                    if(accion==='categoria'){
                        categorias.set(String(respuesta.categoria.id),respuesta.categoria);opcionesCategorias();
                        if(volverTrabajador){form('otro-categoria').value=respuesta.categoria.activa?respuesta.categoria.id:'';window.jQuery('#modalOtroCategoria').modal('hide');}
                        else recargar();
                    }else if(accion==='mensaje'){
                        aviso(resultado,respuesta.msj,true);var trabajador=datos.get('trabajador_id');f.reset();campo(f,'trabajador_id',trabajador);campo(f,'token',uuid());
                    }else recargar();
                }catch(error){aviso(resultado,error.message,false);}
                finally{enviando=false;boton.disabled=false;}
            });
        });
        window.jQuery('#modalOtroCategoria').on('hidden.bs.modal',function(){if(volverTrabajador){volverTrabajador=false;modal('modalOtroTrabajador');}});
        function tablas(){
            if(!window.jQuery.fn.dataTable)return;
            window.jQuery('#otros_trabajadores_tabla, #tab_otros_contratos').each(function(){
                if(this.querySelector('[data-vacio]'))return;
                if(!window.jQuery.fn.dataTable.isDataTable(this))window.jQuery(this).DataTable({responsive:true});
                var tabla=window.jQuery(this).DataTable();tabla.columns.adjust();if(tabla.responsive)tabla.responsive.recalc();
            });
        }
        tablas();window.jQuery(document).on('shown.bs.tab',tablas);
    });
}());
