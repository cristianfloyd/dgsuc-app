#Requires -Version 5.1

<#
.SYNOPSIS
    Script de análisis de calidad de código para proyectos Laravel

.DESCRIPTION
    Ejecuta el workflow completo de calidad de código usando PHP CS Fixer, PHP_CodeSniffer y PHPStan
    en un directorio específico con opción de aplicar correcciones automáticas.

.PARAMETER Path
    Directorio a analizar (default: app/)

.PARAMETER Mode
    Modo de ejecución: 'Check' (solo verificar) o 'Fix' (aplicar correcciones)

.PARAMETER Tools
    Herramientas a ejecutar: 'All', 'Fixer', 'Sniffer', 'Stan'

.PARAMETER Level
    Nivel de PHPStan (1-9, default: configuración actual)

.PARAMETER Verbose
    Mostrar output detallado

.EXAMPLE
    .\quality-check.ps1
    Analiza app/ en modo check con todas las herramientas

.EXAMPLE
    .\quality-check.ps1 -Path "app/Models/" -Mode Fix
    Analiza Models y aplica correcciones automáticas

.EXAMPLE
    .\quality-check.ps1 -Path "tests/" -Tools "Stan" -Level 5
    Solo ejecuta PHPStan en tests con nivel 5

.EXAMPLE
    .\quality-check.ps1 -Path "app/Http/Controllers/" -Verbose
    Analiza Controllers con output detallado
#>

[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [string]$Path = "app/",
    
    [Parameter(Position = 1)]
    [ValidateSet("Check", "Fix")]
    [string]$Mode = "Check",
    
    [Parameter()]
    [ValidateSet("All", "Fixer", "Sniffer", "Rector", "Stan")]
    [string]$Tools = "All",
    
    [Parameter()]
    [ValidateRange(1, 9)]
    [int]$Level,
    
    [Parameter()]
    [switch]$ShowVerbose
)

# ============================================
# CONFIGURACIÓN Y FUNCIONES
# ============================================

# Colores para output
$Colors = @{
    Green   = "Green"
    Blue    = "Cyan" 
    Yellow  = "Yellow"
    Red     = "Red"
    White   = "White"
    Gray    = "DarkGray"
}

# Configuración de herramientas
$Config = @{
    PhpCsFixer = "vendor/bin/php-cs-fixer"
    PhpCs      = "vendor/bin/phpcs"
    PhpCbf     = "vendor/bin/phpcbf"
    Rector     = "vendor/bin/rector"
    PhpStan    = "vendor/bin/phpstan"
}

# Función para escribir con colores y emojis
function Write-ColoredOutput {
    param(
        [string]$Message,
        [string]$Color = "White",
        [switch]$NoNewline
    )
    
    if ($NoNewline) {
        Write-Host $Message -ForegroundColor $Colors[$Color] -NoNewline
    } else {
        Write-Host $Message -ForegroundColor $Colors[$Color]
    }
}

# Función para mostrar separadores
function Write-Separator {
    param([string]$Title, [string]$Color = "Blue")
    
    $separator = "=" * 60
    Write-ColoredOutput ""
    Write-ColoredOutput $separator -Color $Color
    Write-ColoredOutput $Title -Color $Color
    Write-ColoredOutput $separator -Color $Color
}

# Función para ejecutar comandos y capturar resultado
function Invoke-QualityTool {
    param(
        [string]$Command,
        [string]$Arguments,
        [string]$ToolName,
        [string]$Description
    )
    
    Write-ColoredOutput "🔍 $Description..." -Color Yellow
    
    if ($ShowVerbose) {
        Write-ColoredOutput "Ejecutando: $Command $Arguments" -Color Gray
    }
    
    $startTime = Get-Date
    
    try {
        if (Test-Path $Command) {
            $result = & $Command $Arguments.Split(' ') 2>&1
            $exitCode = $LASTEXITCODE
        } elseif (Test-Path "$Command.bat") {
            $result = & "$Command.bat" $Arguments.Split(' ') 2>&1
            $exitCode = $LASTEXITCODE
        } else {
            throw "Comando no encontrado: $Command"
        }
        
        $duration = (Get-Date) - $startTime
        
        if ($ShowVerbose -or $exitCode -ne 0) {
            $result | ForEach-Object { Write-Host $_ }
        }
        
        return @{
            Success = ($exitCode -eq 0)
            ExitCode = $exitCode
            Output = $result
            Duration = $duration
            Tool = $ToolName
        }
    }
    catch {
        Write-ColoredOutput "❌ Error ejecutando $ToolName`: $_" -Color Red
        return @{
            Success = $false
            ExitCode = 1
            Error = $_.Exception.Message
            Tool = $ToolName
        }
    }
}

# ============================================
# VALIDACIONES INICIALES
# ============================================

Write-Separator "🚀 Laravel Code Quality Analyzer" "Blue"
Write-ColoredOutput "📁 Directorio: $Path" -Color Blue
Write-ColoredOutput "⚙️  Modo: $Mode" -Color Blue
Write-ColoredOutput "🛠️  Herramientas: $Tools" -Color Blue

# Validar directorio
if (-not (Test-Path $Path)) {
    Write-ColoredOutput "❌ Error: El directorio '$Path' no existe" -Color Red
    exit 1
}

# Validar vendor/bin
if (-not (Test-Path "vendor/bin")) {
    Write-ColoredOutput "❌ Error: vendor/bin no encontrado. ¿Ejecutaste 'composer install'?" -Color Red
    exit 1
}

# Validar herramientas necesarias
$missingTools = @()
foreach ($tool in $Config.GetEnumerator()) {
    if (-not (Test-Path $tool.Value) -and -not (Test-Path "$($tool.Value).bat")) {
        $missingTools += $tool.Key
    }
}

if ($missingTools.Count -gt 0) {
    Write-ColoredOutput "❌ Herramientas faltantes: $($missingTools -join ', ')" -Color Red
    Write-ColoredOutput "💡 Para instalar:" -Color Yellow
    Write-ColoredOutput "   composer require --dev friendsofphp/php-cs-fixer" -Color Yellow
    Write-ColoredOutput "   composer require --dev phpstan/phpstan squizlabs/php_codesniffer" -Color Yellow  
    Write-ColoredOutput "   composer require --dev rector/rector" -Color Yellow
    exit 1
}

# ============================================
# RESULTADOS
# ============================================
$results = @()

# ============================================
# 1. PHP CS FIXER
# ============================================
if ($Tools -eq "All" -or $Tools -eq "Fixer") {
    Write-Separator "🔧 PHP CS Fixer - Formato y Modernización"
    
    $fixerArgs = if ($Mode -eq "Fix") {
        "fix `"$Path`" --verbose"
    } else {
        "fix `"$Path`" --dry-run --diff"
    }
    
    if ($ShowVerbose) {
        $fixerArgs += " --verbose"
    }
    
    $result = Invoke-QualityTool -Command $Config.PhpCsFixer -Arguments $fixerArgs -ToolName "PHP CS Fixer" -Description $(if ($Mode -eq "Fix") { "Aplicando correcciones automáticas" } else { "Verificando formato y modernización" })
    $results += $result
    
    if ($result.Success) {
        Write-ColoredOutput "✅ PHP CS Fixer completado exitosamente" -Color Green
    } else {
        Write-ColoredOutput "❌ PHP CS Fixer encontró problemas" -Color Red
    }
}

# ============================================
# 2. PHP_CODESNIFFER
# ============================================
if ($Tools -eq "All" -or $Tools -eq "Sniffer") {
    Write-Separator "✅ PHP_CodeSniffer - Estándares PSR-12"
    
    $snifferArgs = "`"$Path`" --report=summary --colors"
    
    $result = Invoke-QualityTool -Command $Config.PhpCs -Arguments $snifferArgs -ToolName "PHP_CodeSniffer" -Description "Verificando cumplimiento de estándares"
    
    if (-not $result.Success -and $Mode -eq "Fix") {
        Write-ColoredOutput "🔧 Intentando corregir automáticamente..." -Color Yellow
        
        $cbfResult = Invoke-QualityTool -Command $Config.PhpCbf -Arguments "`"$Path`"" -ToolName "PHP_CodeBeautifier" -Description "Aplicando correcciones automáticas"
        
        if ($cbfResult.Success) {
            Write-ColoredOutput "🔍 Verificando nuevamente..." -Color Yellow
            $result = Invoke-QualityTool -Command $Config.PhpCs -Arguments $snifferArgs -ToolName "PHP_CodeSniffer" -Description "Re-verificando después de correcciones"
        }
    }
    
    $results += $result
    
    if ($result.Success) {
        Write-ColoredOutput "✅ PHP_CodeSniffer completado sin errores" -Color Green
    } else {
        Write-ColoredOutput "❌ PHP_CodeSniffer encontró violaciones de estándares" -Color Red
    }
}

# ============================================
# 3. RECTOR
# ============================================
if ($Tools -eq "All" -or $Tools -eq "Rector") {
    Write-Separator "🔄 Rector - Refactoring y Modernización"
    
    $rectorArgs = if ($Mode -eq "Fix") {
        "process `"$Path`""
    } else {
        "process `"$Path`" --dry-run"
    }
    
    if ($ShowVerbose) {
        $rectorArgs += " --debug"
    }
    
    $result = Invoke-QualityTool -Command $Config.Rector -Arguments $rectorArgs -ToolName "Rector" -Description $(if ($Mode -eq "Fix") { "Aplicando refactoring y modernización" } else { "Verificando refactoring y modernización" })
    $results += $result
    
    if ($result.Success) {
        Write-ColoredOutput "✅ Rector completado exitosamente" -Color Green
    } else {
        Write-ColoredOutput "❌ Rector encontró problemas o cambios necesarios" -Color Red
    }
}

# ============================================
# 4. PHPSTAN
# ============================================
if ($Tools -eq "All" -or $Tools -eq "Stan") {
    Write-Separator "🔍 PHPStan - Análisis Estático Final"
    
    $stanArgs = "analyse `"$Path`" --no-progress"
    
    if ($Level) {
        $stanArgs += " --level=$Level"
    }
    
    if ($ShowVerbose) {
        $stanArgs += " --verbose"
    }
    
    $result = Invoke-QualityTool -Command $Config.PhpStan -Arguments $stanArgs -ToolName "PHPStan" -Description "Analizando tipos y lógica"
    $results += $result
    
    if ($result.Success) {
        Write-ColoredOutput "✅ PHPStan completado sin errores" -Color Green
    } else {
        Write-ColoredOutput "❌ PHPStan encontró problemas de tipos o lógica" -Color Red
    }
}

# ============================================
# RESUMEN FINAL
# ============================================
Write-Separator "📊 Resumen de Análisis"

$successCount = ($results | Where-Object { $_.Success }).Count
$totalCount = $results.Count
$hasErrors = $results | Where-Object { -not $_.Success }

foreach ($result in $results) {
    $status = if ($result.Success) { "✅" } else { "❌" }
    $color = if ($result.Success) { "Green" } else { "Red" }
    $duration = if ($result.Duration) { " ($($result.Duration.TotalSeconds.ToString('F1'))s)" } else { "" }
    
    Write-ColoredOutput "$status $($result.Tool): $(if ($result.Success) { 'OK' } else { 'ERRORES' })$duration" -Color $color
}

Write-ColoredOutput ""

if ($hasErrors.Count -eq 0) {
    Write-ColoredOutput "🎉 ¡ANÁLISIS COMPLETADO SIN ERRORES!" -Color Green
    Write-ColoredOutput "   El código en '$Path' cumple todos los estándares" -Color Green
    exit 0
} else {
    Write-ColoredOutput "⚠️  ANÁLISIS COMPLETADO CON $($hasErrors.Count) HERRAMIENTA(S) CON PROBLEMAS" -Color Red
    
    Write-ColoredOutput ""
    Write-ColoredOutput "💡 Sugerencias:" -Color Yellow
    
    if ($Mode -eq "Check") {
        Write-ColoredOutput "   - Ejecuta: .\quality-check.ps1 -Path '$Path' -Mode Fix" -Color Yellow
    }
    
    if ($hasErrors | Where-Object { $_.Tool -eq "PHP_CodeSniffer" }) {
        Write-ColoredOutput "   - Revisa violaciones de PSR-12 manualmente" -Color Yellow
    }
    
    if ($hasErrors | Where-Object { $_.Tool -eq "Rector" }) {
        Write-ColoredOutput "   - Revisa refactoring sugerido por Rector" -Color Yellow
        Write-ColoredOutput "   - Considera usar: .\quality-check.ps1 -Tools Rector -Mode Fix" -Color Yellow
    }
    
    if ($hasErrors | Where-Object { $_.Tool -eq "PHPStan" }) {
        Write-ColoredOutput "   - Revisa problemas de tipos y lógica reportados" -Color Yellow
        Write-ColoredOutput "   - Considera usar: .\quality-check.ps1 -Tools Stan -Level 3" -Color Yellow
    }
    
    exit 1
}