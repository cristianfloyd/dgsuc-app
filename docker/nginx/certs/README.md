# Certificados HTTPS para informes-app.test

Para servir la aplicación por **https://informes-app.test** en local, Nginx necesita un certificado y su clave privada.

## Opción recomendada: mkcert

[mkcert](https://github.com/FiloSottile/mkcert) crea certificados confiables en tu sistema (el navegador no mostrará advertencias).

1. **Instalar mkcert** (en el host):

   ```bash
   # Fedora/RHEL
   sudo dnf install mkcert
   # o con go
   go install filippo.io/mkcert@latest
   ```

2. **Instalar la CA local** (una vez por máquina):

   ```bash
   mkcert -install
   ```

3. **Generar certificado para informes-app.test** (dentro de esta carpeta):

   ```bash
   cd docker/nginx/certs
   mkcert informes-app.test
   ```

   Se generan:
   - `informes-app.test.pem` (certificado)
   - `informes-app.test-key.pem` (clave privada)

4. **Levantar de nuevo los contenedores**:

   ```bash
   docker compose up -d
   ```

## Sin mkcert (certificado autofirmado)

Si no usas mkcert, puedes generar un certificado autofirmado. El navegador mostrará una advertencia de seguridad que tendrás que aceptar:

```bash
cd docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout informes-app.test-key.pem \
  -out informes-app.test.pem \
  -subj "/CN=informes-app.test"
```

## Resolución del dominio

Añade en **/etc/hosts** (o equivalente en Windows/macOS):

```
127.0.0.1 informes-app.test
```

Así el navegador resuelve `informes-app.test` a tu máquina, donde Nginx escucha en los puertos 80 y 443.
