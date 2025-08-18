#!/bin/bash

echo "🔧 Arreglando problemas de .env en Linux..."

# 1. Instalar dos2unix si no está disponible
if ! command -v dos2unix &> /dev/null; then
    echo "📦 Instalando dos2unix..."
    if command -v apt-get &> /dev/null; then
        apt-get update && apt-get install -y dos2unix
    elif command -v yum &> /dev/null; then
        yum install -y dos2unix
    elif command -v apk &> /dev/null; then
        apk add dos2unix
    else
        echo "⚠️  No se pudo instalar dos2unix automáticamente"
        echo "   Instálalo manualmente: apt-get install dos2unix"
    fi
fi

# 2. Convertir finales de línea
echo "🔄 Convirtiendo finales de línea..."
find . -name "*.php" -o -name "*.env*" -o -name "*.json" -o -name "*.yml" -o -name "*.yaml" | xargs dos2unix

# 3. Crear .env si no existe
if [ ! -f .env ]; then
    echo "📋 Copiando .env.example a .env..."
    cp .env.example .env
    dos2unix .env
fi

# 4. Configurar permisos
echo "📁 Configurando permisos..."
chmod 644 .env
chmod -R 775 storage bootstrap/cache

# 5. Generar APP_KEY
echo "🔑 Generando APP_KEY..."
php artisan key:generate --force

# 6. Limpiar cache
echo "🧹 Limpiando cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 7. Verificar configuración
echo "🔍 Verificando configuración..."
if grep -q "APP_KEY=base64:" .env; then
    echo "✅ APP_KEY configurado correctamente"
else
    echo "❌ Error: APP_KEY no se configuró correctamente"
    exit 1
fi

echo "🎉 Problema resuelto!"
