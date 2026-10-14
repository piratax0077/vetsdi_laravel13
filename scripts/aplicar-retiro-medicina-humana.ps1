$ErrorActionPreference = 'Stop'
$projectRoot = [IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..'))
$planPath = Join-Path $projectRoot 'storage/app/auditoria/retiro-humana-plan.json'
$plan = Get-Content -LiteralPath $planPath -Raw | ConvertFrom-Json
if ([IO.Path]::GetFullPath($plan.root) -ne $projectRoot) { throw 'El plan no corresponde a este proyecto.' }
$viewsPrefix = [IO.Path]::GetFullPath((Join-Path $projectRoot 'resources/views')) + [IO.Path]::DirectorySeparatorChar
$targets = foreach ($relative in $plan.archivos) {
    $resolved = [IO.Path]::GetFullPath((Join-Path $projectRoot $relative))
    if (!$resolved.StartsWith($viewsPrefix, [StringComparison]::OrdinalIgnoreCase) -or !$resolved.EndsWith('.blade.php')) {
        throw "Archivo fuera del alcance autorizado: $relative"
    }
    if (!(Test-Path -LiteralPath $resolved -PathType Leaf)) { throw "Falta el archivo: $relative" }
    [PSCustomObject]@{FullPath=$resolved; Relative=$relative}
}
$replacement = Join-Path $projectRoot 'storage/app/auditoria/web-veterinaria.php'
& php -l $replacement
if ($LASTEXITCODE -ne 0) { throw 'Las rutas propuestas no compilan.' }
$archivePath = Join-Path $projectRoot ('storage/app/auditoria/medicina_humana_resp_' + (Get-Date -Format 'yyyyMMdd_HHmmss') + '.zip')
Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [IO.Compression.ZipFile]::Open($archivePath, [IO.Compression.ZipArchiveMode]::Create)
try {
    foreach ($target in $targets) {
        [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $target.FullPath, $target.Relative) | Out-Null
    }
    [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, (Join-Path $projectRoot 'routes/web.php'), 'routes/web.php') | Out-Null
} finally { $archive.Dispose() }
$verification = [IO.Compression.ZipFile]::OpenRead($archivePath)
try { if ($verification.Entries.Count -ne ($targets.Count + 1)) { throw 'Respaldo incompleto.' } } finally { $verification.Dispose() }
foreach ($target in $targets) { Remove-Item -LiteralPath $target.FullPath }
Copy-Item -LiteralPath $replacement -Destination (Join-Path $projectRoot 'routes/web.php')
Write-Output "Retiradas $($targets.Count) plantillas y $($plan.rutas.Count) rutas. Respaldo: $archivePath"
