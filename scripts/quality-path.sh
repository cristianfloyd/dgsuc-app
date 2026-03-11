#!/bin/bash
#
# Script para ejecutar quality checks en un path específico
# Uso: composer quality:path -- app/Models/
#      composer quality:path:check -- app/Models/

set -e

CHECK_MODE=false
PATH_ARG=""

# Parsear argumentos
while [[ $# -gt 0 ]]; do
    case $1 in
        --check)
            CHECK_MODE=true
            shift
            ;;
        *)
            PATH_ARG="$1"
            shift
            ;;
    esac
done

if [ -z "$PATH_ARG" ]; then
    echo "❌ Error: Debes especificar un path"
    echo ""
    echo "Uso:"
    echo "  composer quality:path -- app/Models/"
    echo "  composer quality:path -- app/Models/Dh01.php"
    echo "  composer quality:path:check -- app/Services/"
    exit 1
fi

if [ ! -e "$PATH_ARG" ]; then
    echo "❌ Error: El path '$PATH_ARG' no existe"
    exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📁 Path: $PATH_ARG"
if [ "$CHECK_MODE" = true ]; then
    echo "🔍 Modo: dry-run (solo verificación)"
else
    echo "🔧 Modo: aplicar correcciones"
fi
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# PHP CS Fixer
echo ""
echo "🎨 [1/4] PHP CS Fixer..."
if [ "$CHECK_MODE" = true ]; then
    ./vendor/bin/php-cs-fixer fix --dry-run --diff "$PATH_ARG" || true
else
    ./vendor/bin/php-cs-fixer fix "$PATH_ARG" || true
fi

# PHPCS
echo ""
echo "📋 [2/4] PHP CodeSniffer..."
./vendor/bin/phpcs "$PATH_ARG" || true

# Rector
echo ""
echo "🔄 [3/4] Rector..."
if [ "$CHECK_MODE" = true ]; then
    ./vendor/bin/rector process --dry-run "$PATH_ARG" || true
else
    ./vendor/bin/rector process "$PATH_ARG" || true
fi

# PHPStan
echo ""
echo "🔬 [4/4] PHPStan..."
./vendor/bin/phpstan analyse "$PATH_ARG" || true

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Quality checks completados para: $PATH_ARG"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
