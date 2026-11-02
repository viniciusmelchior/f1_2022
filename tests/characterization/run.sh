#!/usr/bin/env bash
# Runs a packaged F1 Stats image under production-like conditions and records
# its HTTP behaviour, so two images can be compared with diff.
#
# Everything shares a Docker network with no route out, so no request can reach
# a real database, mail server or the public hostname, which on this network
# resolves to the local proxy.
#
#   run.sh start-db                  throwaway MySQL with TLS and an ordinary user
#   run.sh migrate IMAGE             the migration command production's Job runs
#   run.sh seed-sql                  placeholder users
#   run.sh serve IMAGE               web container and HTTPS proxy
#   run.sh walk full|read OUT_DIR    drive the app and write normalized responses
#   run.sh logs OUT_DIR              save the web container's output
#   run.sh stop                      remove everything
set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# Same image as the release workflow's migration rehearsal.
MYSQL_IMAGE=docker.io/library/mysql:26.10.0@sha256:7b16ec7caa94ca7e92f4c5ec287de69d2e8738db056ceb629022951a6f6d910b
PROXY_IMAGE=docker.io/library/nginx:1.29-alpine
WALK_IMAGE=docker.io/library/python:3.13-slim
HOSTNAME_PUBLIC=f1.vitorvasconcellos.com.br
NET=f1-char
DB=f1-char-db
APP=f1-char-app
PROXY=f1-char-proxy
WORK="${F1_CHAR_WORK:-${TMPDIR:-/tmp}/f1-char}"

db_root() {
  docker exec -i -e MYSQL_PWD="$(cat "$WORK/root-password")" "$DB" mysql --protocol=tcp -h 127.0.0.1 -u root "$@"
}

start_db() {
  mkdir -p "$WORK/ca"
  chmod 755 "$WORK" "$WORK/ca"
  (umask 077
    openssl rand -hex 24 > "$WORK/root-password"
    openssl rand -hex 24 > "$WORK/app-password"
  )
  docker network create --internal "$NET" > /dev/null
  docker run -d --name "$DB" --network "$NET" \
    -e MYSQL_ROOT_PASSWORD="$(cat "$WORK/root-password")" -e MYSQL_DATABASE=f1_2022 \
    "$MYSQL_IMAGE" > /dev/null
  for _ in $(seq 1 60); do
    if db_root -e 'SELECT 1' > /dev/null 2>&1; then break; fi
    sleep 3
  done
  db_root -e "CREATE USER 'f1_stats'@'%' IDENTIFIED BY '$(cat "$WORK/app-password")' REQUIRE SSL;
    GRANT ALL ON f1_2022.* TO 'f1_stats'@'%';"
  docker cp "$DB:/var/lib/mysql/ca.pem" "$WORK/ca/ca.pem" 2> /dev/null
  chmod 644 "$WORK/ca/ca.pem"
  # Production's non-secret settings, with the database host swapped. 1009 and
  # 1014 are the PDO TLS options carried in production's DATABASE_URL.
  (umask 077
    cat > "$WORK/app.env" <<EOF
APP_NAME=Laravel
APP_ENV=production
APP_DEBUG=false
APP_URL=https://$HOSTNAME_PUBLIC
APP_KEY=base64:$(openssl rand -base64 32)
LOG_CHANNEL=stderr
LOG_LEVEL=debug
BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_LIFETIME=120
MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=Laravel
AWS_DEFAULT_REGION=us-east-1
AWS_USE_PATH_STYLE_ENDPOINT=false
PUSHER_APP_CLUSTER=mt1
DB_CONNECTION=mysql
DATABASE_URL=mysql://$DB:3306/f1_2022?options%5B1009%5D=%2Fetc%2Ff1-stats%2Fmysql-ca%2Fca.pem&options%5B1014%5D=0
DB_USERNAME=f1_stats
DB_PASSWORD=$(cat "$WORK/app-password")
EOF
  )
}

migrate() {
  docker run --rm --network "$NET" --user 33:33 --read-only --tmpfs /tmp \
    --cap-drop ALL --security-opt no-new-privileges \
    --env-file "$WORK/app.env" -v "$WORK/ca:/etc/f1-stats/mysql-ca:ro" \
    "$1" sh -ec "php artisan migrate --pretend --force && php artisan migrate --force"
}

# Several controllers hardcode user_id 3, so two placeholder users come first
# and the walk registers as user 3.
seed_sql() {
  db_root f1_2022 <<'SQL'
INSERT INTO users (id, name, email, password, created_at, updated_at) VALUES
  (1, 'Placeholder One', 'one@example.test', 'not-a-usable-hash', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
  (2, 'Placeholder Two', 'two@example.test', 'not-a-usable-hash', '2024-01-01 00:00:00', '2024-01-01 00:00:00');
SQL
}

serve() {
  docker rm -f "$APP" "$PROXY" > /dev/null 2>&1 || true
  # Per-pod emptyDirs in production, so a new image gets fresh ones. The uploads
  # volume stands in for the File Storage share and survives between images.
  docker volume rm f1-char-storage f1-char-cache f1-char-tmp > /dev/null 2>&1 || true
  local mounts=(
    -v f1-char-storage:/var/www/html/storage
    -v f1-char-cache:/var/www/html/bootstrap/cache
    -v f1-char-tmp:/tmp
    -v f1-char-uploads:/var/www/html/public/images
  )
  # What fsGroup and the prepare init container do in production.
  docker run --rm --user 0 "${mounts[@]}" "$1" sh -ec \
    'mkdir -p /tmp/nginx && chown -R 33:33 storage bootstrap/cache /tmp public/images'
  docker run -d --name "$APP" --network "$NET" --user 33:33 --read-only \
    --cap-drop ALL --security-opt no-new-privileges \
    --env-file "$WORK/app.env" -v "$WORK/ca:/etc/f1-stats/mysql-ca:ro" "${mounts[@]}" \
    "$1" > /dev/null

  if [ ! -f "$WORK/proxy/tls.crt" ]; then
    mkdir -p "$WORK/proxy"
    chmod 755 "$WORK/proxy"
    openssl req -x509 -newkey rsa:2048 -nodes -days 2 -subj "/CN=$HOSTNAME_PUBLIC" \
      -addext "subjectAltName=DNS:$HOSTNAME_PUBLIC" -keyout "$WORK/proxy/tls.key" -out "$WORK/proxy/tls.crt" 2> /dev/null
    chmod 644 "$WORK/proxy/tls.key"
    cat > "$WORK/proxy/default.conf" <<EOF
server {
  listen 443 ssl;
  server_name $HOSTNAME_PUBLIC;
  ssl_certificate /etc/nginx/conf.d/tls.crt;
  ssl_certificate_key /etc/nginx/conf.d/tls.key;
  client_max_body_size 16M;
  location / {
    proxy_pass http://$APP:8080;
    proxy_set_header Host \$host;
    proxy_set_header X-Forwarded-Proto https;
    proxy_set_header X-Forwarded-For \$remote_addr;
  }
}
EOF
  fi
  docker run -d --name "$PROXY" --network "$NET" --network-alias "$HOSTNAME_PUBLIC" \
    -v "$WORK/proxy:/etc/nginx/conf.d:ro" "$PROXY_IMAGE" > /dev/null

  for _ in $(seq 1 30); do
    if docker exec "$APP" curl -fsS http://127.0.0.1:8080/healthz > /dev/null 2>&1; then
      return 0
    fi
    sleep 2
  done
  docker logs "$APP" >&2
  echo "the web container did not become healthy" >&2
  return 1
}

walk() {
  mkdir -p "$2"
  docker run --rm --network "$NET" -v "$HERE/walk.py:/walk.py:ro" -v "$(cd "$2" && pwd):/out" \
    -v "$WORK/proxy/tls.crt:/proxy.crt:ro" -e PROXY_CERT=/proxy.crt \
    -e BASE_URL="https://$HOSTNAME_PUBLIC" "$WALK_IMAGE" python -I /walk.py "$1" /out
}

save_logs() {
  mkdir -p "$1"
  docker logs "$APP" > "$1/app.log" 2>&1
}

stop() {
  docker rm -f "$APP" "$PROXY" "$DB" > /dev/null 2>&1 || true
  docker volume rm f1-char-storage f1-char-cache f1-char-tmp f1-char-uploads > /dev/null 2>&1 || true
  docker network rm "$NET" > /dev/null 2>&1 || true
  rm -rf "$WORK"
}

case "${1:-}" in
  start-db) start_db ;;
  migrate) migrate "$2" ;;
  seed-sql) seed_sql ;;
  serve) serve "$2" ;;
  walk) walk "$2" "$3" ;;
  logs) save_logs "$2" ;;
  stop) stop ;;
  *) sed -n '2,15p' "${BASH_SOURCE[0]}" >&2; exit 2 ;;
esac
