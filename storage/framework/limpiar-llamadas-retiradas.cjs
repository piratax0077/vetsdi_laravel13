const fs=require('fs');const plan=JSON.parse(fs.readFileSync('storage/app/auditoria/depuracion-rutas/plan.json'));
const helpers=fs.readFileSync('scripts/depurar-rutas-heredadas.cjs','utf8');eval(helpers.slice(helpers.indexOf('function mask('),helpers.indexOf('const pending=[];')));
for(const d of plan.details.filter(x=>x.funcionesRetiradas?.length)){let s=fs.readFileSync(d.file,'utf8');const wrappers=new Set();if(d.funcionesRetiradas.includes('agregarMedicamentoManualReceta'))wrappers.add('indicar_medicamento_manual_sdi');if(d.funcionesRetiradas.includes('finalizar_atencion_ajax'))wrappers.add('finalizar_atencion');if(d.funcionesRetiradas.includes('registrar_certificado_defuncion'))wrappers.add('confirmar_registrar_certificado_defuncion');for(const f of funcs(s).reverse()){if(wrappers.has(f.name))s=s.slice(0,f.start)+s.slice(f.end);}
for(const name of wrappers){const re=new RegExp('<(button|a)\\b(?=[^>]*\\bonclick\\s*=\\s*["\\\'][^"\\\']*\\b'+name+'\\s*\\()[^>]*>[\\s\\S]*?<\\/\\1>','gi');s=s.replace(re,'');}
if(d.funcionesRetiradas.includes('cambiarTextoLabel'))s=s.replace(/onchange="cambiarTextoLabel\([^]*?\)"/g,'disabled');
fs.writeFileSync(d.file,s);
}
const f='resources/views/general/secciones_ficha/hospitalizados/hospitalizacion_enfermeria.blade.php';let s=fs.readFileSync(f,'utf8');for(const x of funcs(s).reverse())if(x.name==='eliminar_medicamento_sdi_enf')s=s.slice(0,x.start)+s.slice(x.end);s=s.replace(/<button\b(?=[^>]*eliminar_medicamento_sdi_enf)[^>]*>[\s\S]*?<\/button>/g,'');fs.writeFileSync(f,s);
