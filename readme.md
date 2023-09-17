# Dockerizor Composer
## Know issues
oci8
https://github.com/ApOgEE/php-oci8-alpine

pdo_oci
https://github.com/merorafael/docker-php

odbc & pdo_odbc
```
#0 3.347 checking for Adabas support... cp: can't stat '/usr/local/lib/odbclib.a': No such file or directory
#0 3.359 configure: error: ODBC header file '/usr/local/incl/sqlext.h' not found!
```


pdo_firebird
```
#0 3.247 checking for fb_config... no
#0 3.248 checking for isc_detach_database in -lfbclient... no
#0 3.267 checking for isc_detach_database in -lgds... no
#0 3.286 checking for isc_detach_database in -lib_util... no
#0 3.304 configure: error: libfbclient, libgds or libib_util not found! Check config.log for more information.
```
