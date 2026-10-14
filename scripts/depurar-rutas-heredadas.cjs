const fs=require('fs'),path=require('path');
const root=process.cwd();
if(fs.existsSync('storage/app/auditoria/depuracion-rutas/resp')) throw Error('Depuración ya aplicada. Consulte el plan y el respaldo resp; no se sobrescriben.');
const report=JSON.parse(fs.readFileSync('storage/framework/system-audit.json','utf8').replace(/^\uFEFF/,''));
const keep=new Set(['ficha_medica.registrar_falla','consentimiento.enviar.revocacion','consentimiento.confirmar.revocacion','consentimiento.revocar.form']);
const removed=report.fallas.filter(r=>!keep.has(r.nombre));
const names=new Set(removed.map(r=>r.nombre).filter(Boolean));
const walk=d=>fs.readdirSync(d,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(d,e.name)):[path.join(d,e.name)]);
const changes=new Map(), details=[];
function mask(s){return s.replace(/\{\{--[\s\S]*?--\}\}|\/\*[\s\S]*?\*\/|\/\/[^\r\n]*|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|`(?:\\.|[^`\\])*`/g,m=>' '.repeat(m.length));}
function funcs(s){const out=[];for(const script of s.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)){const offset=script.index+script[0].indexOf('>')+1,m=mask(script[1]);for(const f of m.matchAll(/\bfunction\s+(\w+)\s*\([^)]*\)\s*\{/g)){let n=1,j=f.index+f[0].length;for(;j<m.length&&n;j++){if(m[j]==='{')n++;if(m[j]==='}')n--;}if(!n)out.push({start:offset+f.index,end:offset+j,name:f[1]});}}return out;}
const pending=[];
for(const file of walk('resources/views').filter(f=>f.endsWith('.blade.php')&&!f.includes('_resp'))){let s=fs.readFileSync(file,'utf8'), original=s;if(![...names].some(n=>s.includes(n)))continue;
// Remove commented code first: Blade still evaluates expressions inside JS/HTML comments.
s=s.replace(/\{\{--[\s\S]*?--\}\}|<!--[^]*?-->|^[ \t]*\/\*[^]*?\*\/|^[ \t]*\/\/[^\r\n]*/gm,m=>[...names].some(n=>m.includes(n))?'':m);
// Remove the obsolete campaign upload initializers, including their callbacks.
s=s.replace(/\w+\s*=\s*new Dropzone\([^]*?\n[ \t]{0,12}\}\);/g,m=>m.includes('laboratorio.cargar_archivo_campania')?'':m);
if(file.endsWith('hospitalizacion_enfermeria.blade.php')) s=s.replace(/\s*function eliminarTratamiento\(id\)[\s\S]*?(?=\s*function cambiarTextoLabelTratamiento)/,'\n');
let dropped=[];
for(const f of funcs(s).reverse()){let part=s.slice(f.start,f.end);if([...names].some(n=>part.includes(n))){dropped.push(f.name);s=s.slice(0,f.start)+s.slice(f.end);}}
for(const name of dropped){const re=new RegExp('<(button|a)\\b(?=[^>]*\\bonclick\\s*=\\s*["\\\'][^"\\\']*\\b'+name+'\\s*\\()[^>]*>[\\s\\S]*?<\\/\\1>','gi');s=s.replace(re,'');}
// Retired direct links, including the old provisorio entry point.
s=s.replace(/<(a|button)\b[^]*?<\/\1>/gi,m=>[...names].some(n=>m.includes(n))?'':m);
s=s.replace(/<form\b[^]*?<\/form>/gi,m=>[...names].some(n=>m.includes(n))?'':m);
const left=[...names].filter(n=>s.includes(n));if(left.length)pending.push({file,left});
if(s!==original){changes.set(file,s);details.push({file,funcionesRetiradas:dropped});}
}
for(const file of ['routes/web.php','routes/api.php']){let s=fs.readFileSync(file,'utf8');let count=0;s=s.replace(/^[ \t]*Route::(?:get|post|put|patch|delete|match)\([^;]+;[^\S\r\n]*(?:\r?\n)?/gm,statement=>{const r=removed.find(r=>{const [controller,method]=r.accion.split('@');return statement.includes("'"+method+"'")&&statement.includes(controller.split('\\').pop()+'::class');});if(r){count++;return '';}return statement;});changes.set(file,s);details.push({file,rutasRetiradas:count});}
fs.mkdirSync('storage/app/auditoria/depuracion-rutas',{recursive:true});
fs.writeFileSync('storage/app/auditoria/depuracion-rutas/plan.json',JSON.stringify({removed,details,pending},null,2));
for(const [file,s]of changes){const dest=path.join('storage/app/auditoria/depuracion-rutas/propuesta',file);fs.mkdirSync(path.dirname(dest),{recursive:true});fs.writeFileSync(dest,s);}
if(process.argv.includes('--apply')){if(pending.length)throw Error('Referencias pendientes: revisar el plan');const backup=path.resolve('storage/app/auditoria/depuracion-rutas/resp');if(fs.existsSync(backup))throw Error('Backup ya existe');for(const [file,s]of changes){const target=path.resolve(file);if(!target.startsWith(root+path.sep))throw Error('Fuera del proyecto');const dest=path.join(backup,file);fs.mkdirSync(path.dirname(dest),{recursive:true});fs.copyFileSync(target,dest);fs.writeFileSync(target,s);} }
console.log(JSON.stringify({removed:removed.length,details,pending},null,2));
