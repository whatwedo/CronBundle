# Getting started
## cleanup command

If the `whatwedo:cron:cleanup` command ist not available, it can easily as follows:
```php
class CleanupCronJob extends AbstractCronJob
{
    public function getCommand(): string
    {
        return 'whatwedo:cron:cleanup';
    }
    public function getExpression(): string
    {
        return '15 2 * * *';
    }
}
```

## automatic timestamp with --last-run
When the argument `--last-run` is added like this:  
```php
public function getArguments(): array
{
    return ['--last-run'];
}
```  
The the timestamp is automatically added in the following format: `--last-run 1618997197`  
If the timestamp is passed manually, it won't be overwritten.  
