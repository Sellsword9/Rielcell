# Rielcell 
Rielcell es una aplicación web que sirve repositorios Git y Riel a través de una conexión http. 
## Getting Started
Rielcell no tiene ninguna dependencia más allá de que la máquina anfitriona tenga instalado Docker, y el(los) cliente(s) tenga(n) instalado git. Para instalarlo, tan solo necesitas la infraestructura para hostear el servicio, y el servidor se encargará de todo. 
## Manual de Instalación
Clona el repositorio oficial de Rielcell:
```bash git clone https://sellsword9/rielcell```
Ejecuta el comando preparado en Makefile (opcional) o construye la imagen de Docker manualmente: //
```bash make sd```
o en su lugar:
```bash
  cd rielcell 
  docker build
  docker compose up -d
```
## Manual de usuario
Una vez el servicio esté funcionando, accede desde el navegador a la dirección IP del servidor, y podrás registrarte.
Una vez registrado, podrás crear un nuevo repositorio. Podrás seleccionar entre un repositorio Git o Riel, y una vez hecho,
podrás añadirlo como remoto a tu cliente Git/Riel.
Una vez hayas hecho esto, puedes trabajar con el repositorio como lo harías normalmente, y los cambios se sincronizarán automáticamente con el servidor a medida que vayas haciendo push y pull.
## manual de administrador
Para administrar el servicio, puedes acceder a la interfaz de administración desde la dirección IP del servidor. 
Si has iniciado sesión como un usuario administrador, podrás ver un panel de control que te permitirá gestionar los usuarios y repositorios del servicio. Para crear un nuevo administrador, deberás acceder a la base de datos del servicio y cambiar el rol de un usuario a administrador.
