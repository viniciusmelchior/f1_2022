# Characterization walk

Records how a packaged F1 Stats image behaves over HTTP, so two images can be compared. The walk knows nothing about the framework, so it works across upgrades. PHPUnit stays the place for tests of the code itself.

It needs Docker and images built from `images/f1-stats/` in daybreaker-stack. Each image runs close to production: uid 33, read-only root, separate writable volumes, MySQL 26.10 over TLS through the same `DATABASE_URL` format, and an HTTPS proxy in front. The Docker network has no route out, so nothing reaches a real database, mail server or the public hostname. All data is synthetic, and the database, credentials and application key are created for each run and removed by `stop`.

## Compare two images

```bash
cd tests/characterization
compare() {  # image, output directory
  ./run.sh stop
  ./run.sh start-db
  ./run.sh migrate "$1"
  ./run.sh seed-sql
  ./run.sh serve "$1"
  ./run.sh walk full "$2"
  ./run.sh logs "$2"
}
compare f1-stats:before /tmp/before
compare f1-stats:after /tmp/after
./run.sh stop
diff -r -x app.log /tmp/before /tmp/after
```

Each step writes one normalized response file, `summary.tsv` lists every status, and `unexpected.txt` lists steps that did not answer as expected. CSRF tokens, upload timestamps and dates are normalized, and error pages are reduced to their status.

## Check a rollback

Run the newer image's `compare` steps without the final `stop`, then put the older image on the same database and uploads:

```bash
./run.sh migrate f1-stats:before   # must report "Nothing to migrate"
./run.sh serve f1-stats:before
./run.sh walk read /tmp/rollback
```

## Expected differences from Laravel 9 to 13

- `csrf-same-origin-without-token` answers 302 instead of 419. Laravel 13 accepts a request whose `Sec-Fetch-Site` is `same-origin` without a token. Requests marked cross-site, or with no such header and no token, still get 419.
- `ajax-buscaResultadosCorrida` gains a `page` field in each entry of the paginator's `links`.

## Known 500 responses

These happen before and after the upgrade:

- `/teste` and `/visualizarVitoriasPiloto/{id}` point at controller methods that do not exist.
- `/send-email-pdf` renders the PDF and then fails on the mail host `mailhog`, which does not resolve here or in production.
