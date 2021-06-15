# GAM Query Tool

Application allows communication with Google Ad Manager through API and serves Tableau Web Data Connector. It can be used to mass create Prebid.js lines and for other big changes within GAM inventory. 

Project developed during the Wikia Summer Hackathon 2016.

## Installation

GAM (previously DFP) Query Tool is a project written in PHP and maintained using [Composer](https://getcomposer.org/). In order to install all dependencies and generate autoload files simply run:

```bash
composer install
```

## Configuration

Duplicate [auth.sample.ini](./config/auth.sample.ini) file, rename it to remove `.sample`. Fill it with Google Ad Manager OAuth2 connection credentials (by visiting https://console.cloud.google.com/console and running `php ./GetRefreshToken.php` or ask other team member to use shared, GAM Tableau credentials. Remember to set `networkCode` to `5441`:

```ini
[AD_MANAGER]
networkCode = "5441"

applicationName = "GAM Tableau"

[OAUTH2]
clientId = "clientId.apps.googleusercontent.com"
clientSecret = "clientSecretHash"
refreshToken = "refreshTokenHash"
```

## Browser usage

Project can be hosted and used via internet browser, but this approach is inefective: web forms are synchronous and they report timeouts during more complex tasks. Also lack of progress and error log makes it harder to use.

## CLI usage

Run `app/console` from the root project directory to list all available commands.

```ini
adunits
  adunits:archive                              Archive ad units.
creatives
  creatives:associate                          Associates existing creative to all line items in order
  creatives:deactivate                         Deactivates associated creatives in all line items in order
  creatives:find                               Finds all creatives with given text in snippet code.
key-values
  key-values:get                               Get GAM ids of given key and its values
line-items
  line-items:child-content-eligibility:update  Update Child Content Eligibility field in given order
  line-items:create                            Creates line items in the order (with associated creative)
  line-items:find-by-key                       Find line items by used keys in the targeting
  line-items:key-values:add                    Add key-values pair to line item custom targeting
  line-items:key-values:remove                 Remove key-values pair from line item custom targeting
  line-items:key-values:update                 Update key-values targeting pair in all lines in given order
lint
  lint:yaml                                    Lints a file and outputs encountered errors
order
  order:key-values:add                         Add key-values pair to all line items custom targeting in order
  order:key-values:remove                      Remove key-values pair from all line item custom targeting in order
  order:add-creatives                          Add new creatives to all line items in the order
reports
  reports:fetch                                Downloads data to database.
suggested-adunits
  suggested-adunits:approve                    Approve all suggested ad units in queue.
```

### Create line items

Prepare JSON based on [prebid20.sample.json](./line-item-presets/prebid20.sample.json), [prebid50.sample.json](./line-item-presets/prebid50.sample.json) or [amazonDisplay.sample.json](./line-item-presets/amazonDisplay.sample.json) and execute command:

```bash
app/console line-item:create ./line-item-presets/<your-configuration>.json
``` 

It will create multiple line items in the provided order with associated creative.

WARNING 1. [prebid20.sample.json](./line-item-presets/prebid20.sample.json) is the default file, [prebid50.sample.json](./line-item-presets/prebid50.sample.json) is for bidders with `maxCpm` set to `EXTENDED_MAX_CPM`.

WARNING 2. While creating video line items add `"isVideo": true,`

### Update Child Content Eligibility

Child Content Eligibility is an option in Google Ad Manager lines introduced and switched to "Disallow" for all line items late 2019 / early 2020. This query can automatically switch it back to "Allow" for given orders:

```bash
app/console line-items:child-content-eligibility:update comma,separated,order,ids
```

### Update key-values targeting pair

This command allows you to update values for given key in all lines in given orders. You can use key previously not assigned to the line or use already existing one just to add new values.

First parameter is a comma-separated list of orders that needs to be updated.

Second parameter is a targeting key that should be changed

Third parameter is a comma-separated values that key should have after running the command. Be advised: this command is not clearing up old key values before adding new ones. If all values existed in some line - the update of this line will be skipped.

Fourth parameter (optional) is a key-val operator (IS or IS NOT, default IS).

```bash
app/console line-items:key-values:update comma,separated,order,ids key comma,separated,values operator
```

### Add creatives to line items in an order

If you have an order with many line items which you want to reuse the same creative template you need to execute:
```bash
app/console order:add-creatives --order ORDER_ID --creative-template CREATIVE_TEMPLATE_ID
```
or its simpler version:
```bash
app/console order:add-creatives -o ORDER_ID -c CREATIVE_TEMPLATE_ID
```
It will get all line items in the given order (`ORDER_ID`), create a new creative based on the given creative template (`CREATIVE_TEMPLATE_ID`) and assign it to all the lines.

The new creative's names will be built based on the line-item name and its first creative placeholder size.

If your creative requires string variables you can use `--creative-variables` option:
```
app/console order:add-creatives --order ORDER_ID --creative-template CREATIVE_TEMPLATE_ID --creative-variables VARIABLES_WITH_VALUES_PAIRS
```
or its shortcut `-r`:
```
app/console order:add-creatives -o 2666092254 -c 11899731 -r VARIABLES_WITH_VALUES_PAIRS
```
The `VARIABLES_WITH_VALUES_PAIRS` is a string of pairs separated by `;`, each pair is a combination of two strings separated by `:`, for example:
* `creativeVariable:creativeVariableValue` - passed to the script will set one variable and its value in creative,
* `var1:val1;var2:val2;var3:val3` - passed to the script will set three variables and their values to in creative. 

You can additionally add a suffix to creative template's name by passing optional option:
```bash
app/console order:add-creatives --order ORDER_ID --creative-template CREATIVE_TEMPLATE_ID --creative-suffix "SUFFIX"
```
or the simpler version:
```bash
app/console order:add-creatives -o ORDER_ID -c CREATIVE_TEMPLATE_ID -s "SUFFIX"
```

This way the new creative's names will be built based on the line-item name, its first creative placeholder size and given suffix, for example new creative's name can look like:
`ztest MR 300x250 - 300x250 (test)`
where:
* `ztest MR 300x250 0.01` was the line item name (the price part was removed),
* `300x250` is the first creative placeholder size,
* `(test)` is the given suffix
The command which created such creative could have looked like this:
```bash
app/console order:add-creatives -o 123456 -c 1234567890 -s "(test)"
```

If you want to create new creative per each line in order add `--force-new-creative=1` option or its shorter version: `-f1`

Examples:
* `app/console order:add-creatives --order=2666092254 --creative-template=11899731 --creative-variables="bidderName:indexExchange;test1:test2"`
* `app/console order:add-creatives --order 2666092254 --creative-template 11899731 --creative-variables "var1:val1;bidderName:indexExchange;test1:test2"`
* `app/console order:add-creatives -o 2666092254 -c 11899731 -r "bidderName:indexExchange" -s "(send all-bids)"`
* `app/console order:add-creatives -o 01234567890 -c 01234567890 -s "(send all-bids)" -f1`

## Cron jobs

A cron job is defined in k8s-cron-jobs directory:
* It is designed to periodically (4 times a day) approve suggested ad units
* It runs in k8s in the dev env
* It uses manually created credentials `adeng-query-tool-credentials`
* Its main job is to run `app/console suggested-adunits:approve`

To see logs, go to https://dashboard.poz-dev.k8s.wikia.net:30080/#!/job?namespace=dev, select job with name starting from `dfp-query-tool-` and click on "Logs" icon.

### How to build and deploy new version for cron jobs?

1. Bump the version in the TAG variable in `Makefile` and the `k8s-cron-jobs/dfp-query-tool-poz-dev.yaml` file
2. Run `make build` in order to build a dfp-query-tool image
3. Run `make push` in order to push the image to artifactory
4. Verify in [artifactory](https://artifactory.wikia-inc.com/ui/repos/tree/General/dockerv2-local%2Faden%2Fdfp-query-tool) if the image with correct tag (version) has been created
5. Run `make delete` in order to remove existing cronjob from k8s
6. Run `make deploy` in order to create new cronjob in k8s
7. Verify in [k8s dashboard](https://dashboard.poz-dev.k8s.wikia.net:30080/#/search?namespace=dev&q=dfp-query) if the container has been created

### Troubleshooting

If the built image somehow does not get pushed to the k8s check the latest version of [`k8s-deployer`](https://artifactory.wikia-inc.com/ui/repos/tree/General/dockerv2-local%2Fops%2Fk8s-deployer) and update it in the `dfp-query-tool-poz-dev.yaml` file.

In order to get to the k8s dashboard you need to use [Valut](https://wikia-inc.atlassian.net/wiki/spaces/OPS/pages/132317429/Vault%2BFor%2BEngineers) to get the dashboard-user token.

## Development

Useful resources for future development:
* https://developers.google.com/ad-manager/api/rel_notes
* https://github.com/googleads/googleads-php-lib/tree/master/examples/AdManager
