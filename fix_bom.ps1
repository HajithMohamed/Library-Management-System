$path = "c:\xampp\htdocs\Integrated-Library-System\src\controllers\AdminController.php"
$bytes = [System.IO.File]::ReadAllBytes($path)
if ($bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF) {
    Write-Output "BOM detected. Removing..."
    $newBytes = $bytes[3..($bytes.Length - 1)]
    [System.IO.File]::WriteAllBytes($path, $newBytes)
} else {
    Write-Output "No BOM detected."
}

# Also trim leading whitespace
$content = [System.IO.File]::ReadAllText($path)
if ($content -match "^\s+") {
    Write-Output "Leading whitespace detected. Trimming..."
    $content = $content.TrimStart()
    [System.IO.File]::WriteAllText($path, $content, (New-Object System.Text.UTF8Encoding($false)))
} else {
    # Even if no whitespace, rewrite as UTF-8 without BOM to be safe
    [System.IO.File]::WriteAllText($path, $content, (New-Object System.Text.UTF8Encoding($false)))
}
