# corona-info-reader
Small tool to fetch data about corona. Communicates with the <a href="https://github.com/Volmarg/notifier-proxy-logger">**Notifier Proxy Logger**</a> which is used 
to send messages to the discord (or per mail).

## Description
What does this small project does:
- crawls the RKI (Robert Koch Institute) risc areas list, checks if the article was updated or if the page content has changed,
then sends the request to <a href="https://github.com/Volmarg/notifier-proxy-logger">**NPL**</a> which later on must handle sending it via Discord webhook to receiver,
  
**How to scan RKI page for changes:**
- In CLI call `bin/console cron:robert-koch-institute-info-command`

## Tech stack
- PHP7.4,
- Mysql,
- Symfony 5.x,
- Doctrine,
- Ubuntu 20.x

### Info
*There is no home page, no action, nothing like this, this tool is used only to fetch data*

### Info 2
*Don't forget to create database, run migrations via Symfony CLI `bin/console`, also set the E-mail address for logs and url for the NPL*

**CLI**
- `bin/console doctrine:database:create`
- `bin/console doctrine:migrations:migrate`

**Config**
- `/var/www/corona-info-reader/config/packages/dev/monolog.yaml`
- `/var/www/corona-info-reader/config/packages/prod/monolog.yaml`
```yaml
            to_email:   'email@email.email'
```

- `/var/www/corona-info-reader/config/services.yaml`
```yaml
    App\NotifierProxyLoggerBridge:
        public: true
        arguments:
            $logFilePath: '%kernel.logs_dir%/notifier-proxy-logger-bridge.log'
            $loggerName: 'NotifierProxyLogger'
            $baseUrl: 'http://127.0.0.1:8004/'
```

### Info 3
*This project sends emails on a critical log so local smpt must be configured*

1. `sudo apt-get install sendmail`
2. `hostname` -> copy output
3. `sudo nano /etc/hosts`
4. Add entry `127.0.0.1 localhost <here output from 2>`
5. `sudo sendmailconfig` (Press Y on everything)
6. `sudo service apache2 restart`
7. Test if works:
- `sendmail -v someone@email.com`
- Type `From: you@yourdomain.com`
- Hit enter
- Type `Subject: This is the subject field of the email`
- Hit enter
- Type `.` (a dot), and **Hit enter** to send mail

See also original source: https://kenfavors.com/code/how-to-install-and-configure-sendmail-on-ubuntu/