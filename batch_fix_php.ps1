$srcDir = "c:\xampp\htdocs\Integrated-Library-System\src"
$phpFiles = Get-ChildItem -Path $srcDir -Filter "*.php" -Recurse

foreach ($file in $phpFiles) {
    $path = $file.FullName
    $bytes = [System.IO.File]::ReadAllBytes($path)
    
    if ($bytes.Length -eq 0) { continue }
    
    $changed = $false
    
    # Check for BOM
    if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
        Write-Output "BOM detected in $path. Removing..."
        $bytes = $bytes[3..($bytes.Length - 1)]
        $changed = $true
    }
    
    # Convert to string to check for leading whitespace
    # Using UTF8 without BOM for decoding/encoding
    $utf8NoBOM = New-Object System.Text.UTF8Encoding($false)
    $content = [System.Text.Encoding]::UTF8.GetString($bytes)
    
    if ($content -match "^\s+") {
        Write-Output "Leading whitespace detected in $path. Trimming..."
        $content = $content.TrimStart()
        $changed = $true
    }
    
    if ($changed) {
        [System.IO.File]::WriteAllText($path, $content, $utf8NoBOM)
        Write-Output "Fixed $path"
    }
}
