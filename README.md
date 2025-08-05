# Wiki Bookstack

## Wiki para homelab

Documentar: $ sudo chown -R www-data: bookstack/

## Instalación

Hay que crear y cambiar permisos de uploads a www-data

```bash
$ sudo mkdir -p bookstack/public/uploads
$ sudo mkdir -p bookstack/storage/uploads
$ sudo mkdir -p bookstack/logs
$ sudo chown -R www-data:www-data bookstack
```

## Usuario por default

```bash
USER: admin@admin.com
PASSWD: password
```

## Actualizacion de APP_URL

La APP_URL se guarda estáticamente en las siguientes tablas:

    - bookstack.settings
    - bookstack.images

Por lo que hay que actualizar manualmente en caso de cambiarla.

```sql
-- Cambio localhost:8000 por nueva_url
SELECT url FROM bookstack.images;
UPDATE bookstack.images SET url = REPLACE(url, 'http://localhost:8000', 'http://nueva_url');

SELECT * FROM bookstack.settings s ;
UPDATE bookstack.settings SET value = REPLACE(value, 'http://localhost:8000', 'http://nueva_url');

COMMIT;
```
