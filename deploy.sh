#!/bin/bash

echo "🔥 Iniciando despliegue de Sazón Córdoba..."

# 1. Cargar NVM (Asegura que el script encuentre la versión correcta de Node en EC2)
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Usar tu versión actual de Node
nvm use 24.7.0

# 2. Descargar los cambios más recientes de Git para todo el proyecto (API y Frontend)
echo "📥 Descargando cambios de Git..."
git pull origin main

# 3. Entrar a la carpeta del frontend
echo "📂 Entrando a la carpeta frontend..."
cd frontend || { echo "❌ Error: No se encontró la carpeta frontend"; exit 1; }

# 4. Instalar nuevas dependencias (si hay alguna)
echo "📦 Instalando dependencias..."
npm install

# 5. Compilar Next.js
echo "🏗️ Compilando Next.js..."
npm run build

# 6. Reiniciar el servidor en RAM
echo "🔄 Reiniciando PM2..."
pm2 restart sazon-frontend

echo "✅ ¡Despliegue completado con éxito!"
