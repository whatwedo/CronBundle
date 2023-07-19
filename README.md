<!---
TODO: Change
[![Latest Stable Version](https://poser.pugx.org/whatwedo/cron-bundle/v/stable)](https://packagist.org/packages/whatwedo/cron-bundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/[ID]/mini.png)](https://insight.sensiolabs.com/projects/[ID])
--->

# whatwedoCronBundle

This bundle helps to run Symfony commands as cron job.

Features include:

- Define existing commands as cron job
- Schedule cron job with your system existing cron daemon or use integrated scheduler
- Set maximum runtime of cron jobs
- Allow/disallow parallel execution of a cron job
- Activate/disable cron execution
- Automated database cleanup

**Note:** this bundle is currently under heavy development

## Documentation

The source of the documentation is stored in the `Resources/doc` folder. [Jump to the master documentation](Resources/doc/index.md)

## Known bugs

- The first execution of new CronJobs does not check the Cron Expresion

## License

This bundle is under the MIT license. See the complete license in the bundle: [LICENSE](LICENSE)
