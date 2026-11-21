from pathlib import Path
import subprocess, os
root=Path.cwd(); os.environ['PATH']=str(root/'node_modules/.bin')+os.pathsep+os.environ['PATH']; files=['public/js/app.js','public/css/app.css','public/mix-manifest.json','public/js/app.js.LICENSE.txt']
backup={f:(root/f).read_bytes() if (root/f).exists() else None for f in files}
try:
 with open('tmp/auditoria-cloud/build.log','w',encoding='utf-8') as out:
  result=subprocess.run(['node','node_modules/laravel-mix/bin/cli.js','--production'],stdout=out,stderr=subprocess.STDOUT)
 print('Resultado de compilacion',result.returncode)
finally:
 for f,data in backup.items():
  p=root/f
  if data is not None:p.write_bytes(data)
  elif p.exists():p.unlink()
