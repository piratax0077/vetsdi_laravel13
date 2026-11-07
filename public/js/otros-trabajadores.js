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
        function campo(f,n,v){var e=f.elements[n];if(!e)return;v=v==null?'':String(v);if(e.tagName==='SELECT' && v!=='' && (n==='banco'||n==='tipo_cuenta') && !Array.from(e.options).some(function(o){return o.value===v;}))e.add(new Option(v,v));e.value=v;}
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
            select.add(new Option('Otro','otro'));select.value=actual;edicion.value=elegida;
        }
        function llenarComunasTrabajador(region, seleccion){
            var select=form('otro-comuna');
            select.replaceChildren(new Option('Seleccione comuna',''));
            (config.comunas||[]).filter(function(c){return String(c.id_region)===String(region);}).forEach(function(c){select.add(new Option(c.nombre,c.id));});
            select.disabled=!region;select.required=!!region;select.value=seleccion==null?'':String(seleccion);
        }
        form('otro-telefono').addEventListener('focus',function(){if(!this.value)this.value='+569';});
        form('otro-telefono').addEventListener('blur',function(){if(this.value.replace(/\s/g,'')==='+569')this.value='';});
        function alternarCargoOtro(){var otro=form('otro-categoria').value==='otro';form('otro-cargo-caja').classList.toggle('d-none',!otro);form('otro-cargo').required=otro;if(!otro)form('otro-cargo').value='';}
        form('otro-categoria').addEventListener('change',alternarCargoOtro);
        form('otro-region').addEventListener('change',function(){llenarComunasTrabajador(this.value,'');});
        function abrirTrabajador(t){
            var f=form('formOtroTrabajador');preparar(f,t||{activo:1});
            llenarComunasTrabajador(t?.region_id||'',t?.comuna_id||'');
            var lugarElegido=t? String((t.lugares||[])[0]||''):(config.lugares.length===1?String(config.lugares[0].id):'');f.elements['lugares[]'].value=lugarElegido;
            alternarCargoOtro();form('otro-trabajador-titulo').textContent=t?'Editar trabajador':'Agregar trabajador';modal('modalOtroTrabajador');
        }
        function abrirContrato(trabajador,contrato){
            var f=form('formOtroContrato');preparar(f,contrato||{trabajador_id:trabajador||'',tipo:'indefinido',fecha_inicio:config.hoy,remuneracion:0});modal('modalOtroContrato');
        }
        function textoNodo(tipo,texto,clase){var el=document.createElement(tipo);el.textContent=texto==null||texto===''?'—':String(texto);if(clase)el.className=clase;return el;}
        function mostrarInfo(d){
            var t=d.trabajador,contenedor=form('otro-info-datos');contenedor.replaceChildren();
            var sucursales=config.lugares.filter(function(l){return t.lugares.map(String).includes(String(l.id));}).map(function(l){return l.nombre;}).join(', ');
            var region=(config.regiones||[]).find(function(r){return String(r.id)===String(t.region_id);})?.nombre,comuna=(config.comunas||[]).find(function(c){return String(c.id)===String(t.comuna_id);})?.nombre;
            var grupos=[
                ['user','Datos personales',[['Nombre',t.nombres+' '+t.apellido_uno+' '+(t.apellido_dos||'')],['RUT',t.rut],['Categoría',categorias.get(String(t.categoria_id))?.nombre],['Estado',Number(t.activo)?'Activo':'Desvinculado'],['Lugar de atención',sucursales]]],
                ['phone','Contacto y residencia',[['Correo',t.email],['Teléfono',t.telefono],['Dirección',t.direccion],['Región',region],['Comuna',comuna]]],
                ['credit-card','Datos bancarios',[['Banco',t.banco],['Tipo de cuenta',t.tipo_cuenta],['Número de cuenta',t.numero_cuenta]]],
                ['message-square','Observaciones',[['Observaciones',t.observaciones,true]]]
            ];
            grupos.forEach(function(g){
                var caja=document.createElement('div');caja.className='otro-info-bloque';
                var titulo=document.createElement('h6');titulo.className='otro-info-titulo';var icono=document.createElement('i');icono.className='feather icon-'+g[0];titulo.append(icono,document.createTextNode(g[1]));
                var fila=document.createElement('div');fila.className='row';
                g[2].forEach(function(par){var col=document.createElement('div');col.className=par[2]?'col-12':'col-md-6';var dl=document.createElement('dl');dl.className='otro-info-dato';dl.append(textoNodo('dt',par[0]),textoNodo('dd',par[1],'text-wrap'));col.append(dl);fila.append(col);});
                caja.append(titulo,fila);contenedor.append(caja);
            });
            var contratos=form('otro-info-contratos');contratos.replaceChildren();
            d.contratos.forEach(function(c){var p=textoNodo('p',(config.tiposContrato[c.tipo]||c.tipo)+' · '+c.fecha_inicio+(c.fecha_fin?' a '+c.fecha_fin:'')+' · '+c.estado+' · $'+Number(c.remuneracion).toLocaleString('es-CL'));p.className='otro-info-item mb-2';contratos.append(p);});
            if(!d.contratos.length)contratos.append(textoNodo('p','Sin contratos registrados.','text-muted'));
            var mensajes=form('otro-info-mensajes');mensajes.replaceChildren();
            d.mensajes.forEach(function(m){var caja=document.createElement('div');caja.className='otro-info-item';caja.append(textoNodo('strong',m.asunto),textoNodo('p',m.created_at+' · '+m.destinatario+' · '+m.estado,'small text-muted mb-1'),textoNodo('p',m.mensaje,'text-wrap mb-0'));mensajes.append(caja);});
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
                    if(accion==='guardar' && datos.get('categoria_id')==='otro'){
                        var cargo=String(datos.get('cargo_otro')||'').trim().replace(/\s+/g,' ');
                        if(!cargo)throw new Error('Escriba el cargo.');
                        var existente=Array.from(categorias.values()).find(function(c){return String(c.nombre).toLowerCase()===cargo.toLowerCase();});
                        if(existente){datos.set('categoria_id',existente.id);}
                        else{var nueva=new FormData();nueva.set('nombre',cargo);nueva.set('activa','1');nueva.set('_token',config.csrf);var creada=await peticion(config.categoria,nueva);categorias.set(String(creada.categoria.id),creada.categoria);datos.set('categoria_id',creada.categoria.id);}
                        datos.delete('cargo_otro');
                    }
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
