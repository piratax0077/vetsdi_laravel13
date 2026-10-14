const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const viewsRoot = path.join(root, 'resources/views');
function walk(dir) { return fs.readdirSync(dir, {withFileTypes:true}).flatMap(e => e.isDirectory() ? walk(path.join(dir,e.name)) : [path.join(dir,e.name)]); }
const views = new Map(walk(viewsRoot).filter(p => p.endsWith('.blade.php')).map(p => [path.relative(viewsRoot,p).replaceAll('\\','/').replace(/\.blade\.php$/, '').replaceAll('/','.'), p]));
const contents = new Map([...views].map(([name,file]) => [name, fs.readFileSync(file,'utf8')]));
const human = /(?:gine|obstetric|pediatr|neonato|siquiatr|psiquiatr|psico|sicolog|fono|kine|matrona|tens|terapia_ocup|octavo_par|oftalm|oftalmo|otorr|urolog|quiroprax|homeopat|dermat|broncopulmon|cardiolog|gastroenterolog|neurolog|traumatolog)/i;
const candidates = new Set([...views.keys()].filter(n => /^(atencion_gine_obstetricia|atencion_pediatrica|atencion_otros_prof|compin)\./.test(n) || (/^atencion_veterinaria\./.test(n) && human.test(n))));
// Keep any partial reached by a retained screen, including dependencies of dependencies.
const retained = new Set([...views.keys()].filter(n => !candidates.has(n)));
const queue = [...retained];
for (let i=0;i<queue.length;i++) {
  const text = (contents.get(queue[i]) || '').replace(/\{\{--[\s\S]*?--\}\}/g,'');
  for (const match of text.matchAll(/['"]([A-Za-z0-9_.\/-]+)['"]/g)) {
    const name = match[1].replaceAll('/','.');
    if (views.has(name) && !retained.has(name)) { retained.add(name);queue.push(name); }
  }
}
const deleted = [...candidates].filter(n => !retained.has(n));
const retainedText = [...retained].map(n => contents.get(n).replace(/\{\{--[\s\S]*?--\}\}/g,'')).join('\n');
const routeFile = path.join(root,'routes/web.php');
const routes = fs.readFileSync(routeFile,'utf8');
const removedRoutes = [];
const exclusiveControllers = /\b(?:FichaGinecoObstetricoController|FichaPediatria\w*Controller|FichaAtencionOtrosProfController|PsicoPsiquiatriaController|PlanTratamientoTerapiaSicologicaController|OctavoParController|CompinController)\b/;
const updated = routes.replace(/^[ \t]*Route::(?:get|post|put|patch|delete|match)\([^;]+;[^\S\r\n]*(?:\r?\n)?/gm, statement => {
  if (!exclusiveControllers.test(statement)) return statement;
  const name = statement.match(/->name\(['"]([^'"]+)['"]\)/)?.[1];
  if (!name || retainedText.includes("'"+name+"'") || retainedText.includes('"'+name+'"')) return statement;
  removedRoutes.push({name,statement:statement.trim()});return '';
});
const plan = {root, archivos:deleted.map(n => path.relative(root,views.get(n)).replaceAll('\\','/')), compartidosConservados:[...candidates].filter(n=>retained.has(n)), rutas:removedRoutes};
fs.mkdirSync(path.join(root,'storage/app/auditoria'),{recursive:true});
fs.writeFileSync(path.join(root,'storage/app/auditoria/retiro-humana-plan.json'), JSON.stringify(plan,null,2));
fs.writeFileSync(path.join(root,'storage/app/auditoria/web-veterinaria.php'),updated);
console.log(JSON.stringify({pantallas:deleted.length,compartidosConservados:plan.compartidosConservados.length,rutas:removedRoutes.length,ejemplos:plan.archivos.slice(0,12)},null,2));
