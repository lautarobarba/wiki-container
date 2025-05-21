# Wiki JS

## Wiki para homelab

Documentar: $ sudo chown -R www-data: bookstack/

## Actualizacion de APP_URL

En la tabla images se guarda la url de los archivos de manera estatica
Hay que actualizarla con el nuevo valor de APP_URL si se actualizo o dan error

```sql
-- Cambio el puerto 8000 por 8005
SELECT url, REPLACE(url, '8000', '8005') FROM bookstack.images;
UPDATE bookstack.images SET url = REPLACE(url, '8000', '8005');

SELECT * FROM bookstack.settings s ;
UPDATE bookstack.settings SET value = REPLACE(value, 'localhost:8000', 'localhost:8005');

COMMIT;
```
