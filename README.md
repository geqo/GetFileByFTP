# GetFileByFTP
Get file through ftp
## Installation
```bash
composer require geqo/getfilebyftp
```
## Usage
```php
$client = new \Geqo\GetFileByFTP($host, $user, $pass, $port);
$client->setPassive(true);
$client->setEncoding('UTF8');
$client->getFile($addr, __DIR__ . '/' . $file);
```
