# Canvas Grading Analytics

[![Latest Version](https://img.shields.io/packagist/v/smtech/grading-analytics.svg)](https://packagist.org/packages/smtech/grading-analytics)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/smtech/grading-analytics/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/smtech/grading-analytics/?branch=master)

Generate some reporting on faculty grading practices. To start automated data-collection, run the private/data-collection.sh script once. It will harvest the first round of data and then create a crontab job to repeat that process every night at midnight.

A description of the information collected by is [available online](https://smtech.github.io/grading-analytics/definitions.html).

### Install

#### Requirements

  - Apache 2
  - MySQL 5.5 or better
  - PHP 5.6 or better
  - [Composer](https://getcomposer.org)

#### Step-by-step

This generically follows a standard PHP app install pattern, with some additional excitement thrown in for good measure.

  1. Clone the repository to your web root.
```bash
cd path/to/web/root
git clone https://github.com/smtech/grading-analytics.git
```

  2. Run the setup script (this will install dependencies, set file permissions, schedule Cron jobs, etc. -- it's good about telling you what it's doing as it does it, and it will require a sudo password periodically).
```bash
cd grading-analytics
./setup
```

  3. Edit the newly created `config.xml` file to include your credentials for the app (a MySQL login for data storage and credentials for Canvas to communicate with the API -- either a developer key and secret or an API access url and token).

  4. Point your browser at the install to finish the app setup (this will read in the configuration file, setup database tables and so forth).
```
https://server.com/path/to/grading-analytics
```
  If you need to re-run the install process later, point your browser at:
```
https://server.com/path/to/grading-analytics?action=install
```
  The end result of the install process is to drop you into the LTI consumers management page, where you will need to create a new consumer key and secret.

  5. Install the API in Canvas. It should be installed in the sub-account that contains all of the courses that will be monitored for example, we install into the Academics sub-account which contains all of our departmental sub-accounts. Use Canvas' "By Url" option to paste in the consumer key, shared secret and configuration URL from the consumers management page.

  6. Visit the account navigation placement of the LTI to complete installation (this will capture the account ID of that root account via LTI authentication, which allows the data collection script to know _which_ account to monitor).

#### Upgrading from v1.x to v2.0

The rationale for switching from the 1.x branch to 2.0 is largely framed around the change in underlying LTI authentication structure. This requires that existing users both re-run the app install process described above (steps 1-4) and then _also_ re-install the LTI in Canvas (steps 5-6).

Underlying data _is_ preserved between the 1.x and 2.0 branches, without change. However, the change in LTI structure also created a `tool_metadata` table in the MySQL database which replaces the `app_metadata` table, which can be deleted.

In the `course_statistics` table, I did opt to run a quick script to update all of the `analytics_page` fields to point to the new LTI install, but that's not really necessary, since, as soon as new data is collected, those old links won't be visible again.
