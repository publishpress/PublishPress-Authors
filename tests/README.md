# Testing

## Installing

Install WP-CLI and install the test library.

```bash
$ bin/install-wp-tests.sh wordpress_test root root db latest 
```

If needed, double check the configuration stored in `/tmp/wordpress-tests-lib/wp-tests-config.php`


## Running Tests on Docker Container

If your database is inside a Docker container, make sure to create the link and use the same network.

```bash
$ docker run -v $(pwd):/app -v /tmp/wordpress:/tmp/wordpress -v /tmp/wordpress-tests-lib:/tmp/wordpress-tests-lib --link wordpressdev_db_1:db --network wordpressdev_default --rm phpunit/phpunit -c phpunit.xml

```
