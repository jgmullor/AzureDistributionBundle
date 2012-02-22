# Session

Cloud providers require distributed storage mechanisms for the session. You can configure your Symfony application to run with SQL Azure (currently):

    windows_azure_distribution:
        session:
            type: pdo
            database:
                host: mydb.database.windows.net
                username: xyz
                password: xzy
                database: some_db_name

During instance startup it is checked if the required database table exists in the given database and if necessary it is created. The default table name is 'azure_sessions'. It contains three fields 'sess_id', 'sess_data' and 'sess_time'.

To configure the lifetime of sessions and the probability of garbage collection see the [PHP documentation](http://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability).

The PDO based database session works with an independent connection. This is done to ensure that a transaction in the other code never affects the write operation into the session storage.

A session mechanism based on Windows Azure Tables is planed.
