Para crear la base de datos, ejecutar las migraciones con el siguiente comando:

```bash
vendor/bin/phinx migrate
```

Para poblar la base de datos con datos de prueba, ejecutar los siguientes comandos:

```bash
vendor/bin/phinx seed:run