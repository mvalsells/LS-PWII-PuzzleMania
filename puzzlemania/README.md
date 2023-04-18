# Summary

This is a base template to develop the assignment. It contains the following:

* `docker-compose.yaml` file with a
    * php-fpm image
    * mysql-image
    * nginx image
    * adminer image
    * barcode image template
* `docker-compose.intel.yaml` file with a
  * barcode image for Intel CPU's
* `docker-compose.arm.yaml` file with a
  * barcode image for ARM CPU's
* `docker-entrypoint-initdb.d/schema.sql` so when you run `docker compose up` a database is created following such
  schema
* The PHP code to start your application with
    * `composer.json` file
    * `templates` folder
    * `src` folder
    * `config` folder
    * `public` folder
* `.env` file
* `cypress` folder

## How to create and destroy the services

### INTEL Architecture (most Windows and Mac Operating Systems)
```bash
docker compose up
```

### ARM Architecture (Apple Silicon Mac)
```bash
docker compose -f docker-compose.yaml -f docker-compose.arm.yaml up
```
### Destroy

Use `docker compose down` to destroy them.

## How to run tests

We are going t use `cypress` to execute end-to-end tests. Before anything else, and only the first time you download the project, go to the `cypress` folder and install all npm dependencies using `docker run -it --rm -v ${PWD}:/usr/src/app -w /usr/src/app -p 3000:3000 node:16-alpine npm install`.

Every time you run the tests, you must do it from the project folder, where the file `cypress.json` is located.

You may have noticed that the `docker-compose.yaml` file does not specify any cypress service. That is because you are
going to use a separate container to run `cypress`. Every time you want to run a test suite / spec (you will find them
inside `cypress/e2e`) you will need to execute a command such as:

```bash
# inside the project-template directory

docker run --rm --env CYPRESS_baseUrl=http://nginx:80 -v ${PWD}:/cypress -w /cypress/cypress --network="puzzlemania-template_default" -it vcaballerosalle/cypress-mysql:3.0 --browser electron --spec "e2e/sign-up.cy.js"
```

Notice two things in this command:

* First, you have to specify the spec at the end of the command. `cypress/integration/sign-in.cy.js` is the spec run
  by the previous command. If you remove the `--spec` flag and the corresponding argument, cypress will run all specs
  found inside the `integration` folder.
* Second, the services specified by the `docker-compose.yaml` file must be running. If you read the command again, you
  will see the flag `--network`. The right hand of the equals sign is the name of the network `docker compose up`
  created for us, therefore, what we are doing is connecting the standalone cypress container to the network with all
  the services. Notice that the first part of the network name is the name of the folder where `docker-compose.yaml` is
  located, so if you change the name of the folder, you will need to change the name of the network you want to connect
  to.

Finally, if you run the command and you signal to cancel the execution (Ctrl+C), your `.env` file may be left in an
unstable state. You can find the original file in `cypress/tmp/env.prod`.