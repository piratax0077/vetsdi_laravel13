$ErrorActionPreference = 'Stop'
$origen = 'D:\laragon\www\vet-sdi_v13'
$destino = 'D:\laragon\www\centro-estetica'
if (Test-Path -LiteralPath $destino) { throw 'La carpeta destino ya existe; no se sobrescribe.' }
New-Item -ItemType Directory -Path $destino | Out-Null
foreach ($carpeta in @('app','config','vendor')) {
  & robocopy (Join-Path $origen $carpeta) (Join-Path $destino $carpeta) /E /R:1 /W:1 /NFL /NDL /NJH /NJS /NP | Out-Null
  if ($LASTEXITCODE -ge 8) { throw "Error al copiar $carpeta" }
}
foreach ($carpeta in @('css','js','fonts','images','build','sounds','json')) {
  & robocopy (Join-Path $origen "public\$carpeta") (Join-Path $destino "public\$carpeta") /E /R:1 /W:1 /NFL /NDL /NJH /NJS /NP | Out-Null
  if ($LASTEXITCODE -ge 8) { throw "Error al copiar recursos $carpeta" }
}
foreach ($carpeta in @('app\peluqueria','auth','perfil','template\include','mail','emails','errors','components')) {
  if (Test-Path (Join-Path $origen "resources\views\$carpeta")) {
    & robocopy (Join-Path $origen "resources\views\$carpeta") (Join-Path $destino "resources\views\$carpeta") /E /R:1 /W:1 /NFL /NDL /NJH /NJS /NP | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "Error al copiar vistas $carpeta" }
  }
}
foreach ($carpeta in @('bootstrap\cache','routes','database\migrations','database\schema','database\seeders','database\factories','storage\app\public','storage\framework\cache\data','storage\framework\sessions','storage\framework\views','storage\logs','scripts','tests\Feature')) { New-Item -ItemType Directory -Force -Path (Join-Path $destino $carpeta) | Out-Null }
foreach ($archivo in @('artisan','composer.json','composer.lock','.gitignore','public\index.php','public\.htaccess','routes\peluqueria.php','routes\console.php')) { Copy-Item -LiteralPath (Join-Path $origen $archivo) -Destination (Join-Path $destino $archivo) }
Write-Output 'Archivos y recursos copiados en una carpeta independiente.'
