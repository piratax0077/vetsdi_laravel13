document.querySelectorAll('[data-horario-form]').forEach(function(form){
 const lista=form.querySelector('[data-horario-tramos]');
 function numerar(){Array.from(lista.rows).forEach(function(row,i){row.querySelectorAll('[name]').forEach(function(input){input.name=input.name.replace(/tramos\[\d+\]/,'tramos['+i+']');});});}
 form.querySelector('[data-agregar-tramo]').addEventListener('click',function(){const row=lista.rows[0]?.cloneNode(true);if(!row)return;row.querySelectorAll('input').forEach(input=>input.value='');lista.append(row);numerar();row.querySelector('select').focus();});
 lista.addEventListener('click',function(event){const boton=event.target.closest('[data-quitar-tramo]');if(!boton)return;if(lista.rows.length===1){window.alert('Debe quedar al menos un horario de atención.');return;}boton.closest('tr').remove();numerar();});
});
