# Crea quinto-lumiere-hostinger.zip listo para subir y extraer en public_html.
# Deja fuera: _dev, LEEME.md, la base de datos local y las fotos de prueba.
$ErrorActionPreference = 'Stop'
$root = Split-Path $PSScriptRoot -Parent
$out = Join-Path (Split-Path $root -Parent) 'quinto-lumiere-tienda.zip'

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
if (Test-Path $out) { Remove-Item $out -Force }
$zip = [IO.Compression.ZipFile]::Open($out, 'Create')
$n = 0
try {
    Get-ChildItem $root -Recurse -Force -File | ForEach-Object {
        $rel = $_.FullName.Substring($root.Length + 1)
        $skip = $rel -like '_dev\*' -or $rel -like '.git\*' -or $rel -like 'data\sessions\sess_*' -or $rel -in @('LEEME.md', 'README.md', '.gitignore', '.gitattributes') -or $rel -like 'data\*.sqlite*' -or ($rel -like 'uploads\*' -and $_.Name -ne '.htaccess')
        if (-not $skip) {
            # rutas con "/" para que Linux (Hostinger) las extraiga como carpetas
            [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, ($rel -replace '\\', '/'), 'Optimal') | Out-Null
            $n++
        }
    }
} finally { $zip.Dispose() }
Write-Output "Listo ($n archivos): $out"
