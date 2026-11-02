#!/usr/bin/env python3
"""Drives F1 Stats over HTTP as a browser would and records what it answers.

Black-box on purpose: the same walk runs against any framework version, so the
output of two images can be compared directly. Data is synthetic and created
through the application's own forms, which also exercises the write paths.

  walk.py full OUT   create data, read every page, then change and delete data
  walk.py read OUT   log in to existing data and only read

Standard library only. Each step writes one normalized file to OUT and one line
to OUT/summary.tsv; steps whose status differs from what they expect are also
listed in OUT/unexpected.txt.
"""

import hashlib
import http.cookiejar
import io
import os
import re
import ssl
import struct
import sys
import urllib.error
import urllib.parse
import urllib.request
import uuid
import zipfile
import zlib

BASE = os.environ["BASE_URL"]
ORIGIN = BASE
EMAIL = "walker@example.test"
PASSWORD = "synthetic-walk-password"


def png(red, green, blue):
    """A valid 2x2 PNG, distinct per colour, so served bytes can be compared."""

    def chunk(kind, data):
        body = kind + data
        return struct.pack(">I", len(data)) + body + struct.pack(">I", zlib.crc32(body))

    row = b"\x00" + bytes([red, green, blue]) * 2
    return (
        b"\x89PNG\r\n\x1a\n"
        + chunk(b"IHDR", struct.pack(">IIBBBBB", 2, 2, 8, 2, 0, 0, 0))
        + chunk(b"IDAT", zlib.compress(row * 2))
        + chunk(b"IEND", b"")
    )


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


# Values that legitimately change between runs and would hide real differences.
NORMALIZERS = [
    (re.compile(r'(name="_token"\s+value=")[^"]+'), r"\1TOKEN"),
    # Attribute Laravel 13 adds to @csrf; it carries no application behaviour.
    (re.compile(r'(name="_token" value="TOKEN") autocomplete="off"'), r"\1"),
    (re.compile(r'(name="csrf-token"\s+content=")[^"]+'), r"\1TOKEN"),
    (re.compile(r"(csrf[-_]?[Tt]oken['\"]?\s*[:=]\s*['\"])[^'\"]+"), r"\1TOKEN"),
    (re.compile(r"\b\d{10}([-_])"), r"EPOCH\1"),
    (re.compile(r"\b\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}\b"), "DATETIME"),
    (re.compile(r"\b\d{2}/\d{2}/\d{4} às \d{2}:\d{2}:\d{2}\b"), "DATETIME"),
]


def normalize(text):
    for pattern, replacement in NORMALIZERS:
        text = pattern.sub(replacement, text)
    return "\n".join(line.rstrip() for line in text.splitlines()) + "\n"


def describe_xlsx(data):
    """The workbook's content without zip timestamps or generated metadata."""
    out = []
    with zipfile.ZipFile(io.BytesIO(data)) as archive:
        for name in sorted(archive.namelist()):
            if re.search(r"worksheets/.*\.xml$|sharedStrings\.xml$", name):
                out.append(f"--- {name}\n{archive.read(name).decode()}")
    return "\n".join(out)


class Walker:
    def __init__(self, out):
        self.out = out
        self.step = 0
        self.cookies = http.cookiejar.CookieJar()
        context = ssl.create_default_context(cafile=os.environ["PROXY_CERT"])
        self.opener = urllib.request.build_opener(
            NoRedirect,
            urllib.request.HTTPCookieProcessor(self.cookies),
            urllib.request.HTTPSHandler(context=context),
        )
        self.summary = open(os.path.join(out, "summary.tsv"), "w", encoding="utf-8")
        self.unexpected = open(os.path.join(out, "unexpected.txt"), "w", encoding="utf-8")

    def xsrf(self):
        for cookie in self.cookies:
            if cookie.name == "XSRF-TOKEN":
                return urllib.parse.unquote(cookie.value)
        return ""

    def send(self, method, path, body=None, headers=None):
        request = urllib.request.Request(BASE + path, data=body, method=method, headers=headers or {})
        try:
            with self.opener.open(request, timeout=120) as response:
                return response.status, dict(response.headers), response.read()
        except urllib.error.HTTPError as error:
            return error.code, dict(error.headers), error.read()

    def record(self, name, expect, result):
        status, headers, body = result
        self.step += 1
        ctype = headers.get("Content-Type", "").lower()
        location = normalize(headers.get("Location", "")).strip()
        if "spreadsheetml" in ctype:
            shown = describe_xlsx(body)
        elif ctype.startswith(("image/", "application/pdf")):
            shown = f"sha256 {hashlib.sha256(body).hexdigest()} ({len(body)} bytes)"
        elif status >= 400:
            # The generic error page is framework markup, not application output.
            shown = "(error page omitted)"
        else:
            shown = body.decode("utf-8", "replace")
        with open(os.path.join(self.out, f"{self.step:03d}-{name}.txt"), "w", encoding="utf-8") as file:
            file.write(f"status: {status}\nlocation: {location}\ncontent-type: {ctype}\n\n")
            file.write(normalize(shown))
        self.summary.write(f"{self.step}\t{name}\t{status}\t{location}\n")
        self.summary.flush()
        if expect is not None and status != expect:
            self.unexpected.write(f"{name}: expected {expect}, got {status} {location}\n")
            self.unexpected.flush()
        return result

    def get(self, name, path, expect=200):
        return self.record(name, expect, self.send("GET", path))

    def form_headers(self, content_type, token, fetch_site="same-origin"):
        headers = {"Content-Type": content_type, "Origin": ORIGIN, "Referer": BASE + "/"}
        if fetch_site:
            headers["Sec-Fetch-Site"] = fetch_site
        if token:
            headers["X-XSRF-TOKEN"] = self.xsrf()
        return headers

    def post(self, name, path, fields=(), expect=302, token=True, fetch_site="same-origin"):
        body = urllib.parse.urlencode(list(fields)).encode()
        headers = self.form_headers("application/x-www-form-urlencoded", token, fetch_site)
        return self.record(name, expect, self.send("POST", path, body, headers))

    def multipart(self, name, path, fields=(), files=(), expect=302):
        boundary = uuid.uuid4().hex
        parts = []
        for key, value in fields:
            parts.append(
                f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode()
            )
        for key, filename, data in files:
            parts.append(
                (
                    f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"; '
                    f'filename="{filename}"\r\nContent-Type: image/png\r\n\r\n'
                ).encode()
                + data
                + b"\r\n"
            )
        parts.append(f"--{boundary}--\r\n".encode())
        headers = self.form_headers(f"multipart/form-data; boundary={boundary}", True)
        return self.record(name, expect, self.send("POST", path, b"".join(parts), headers))

    def form_token(self, path):
        _, _, body = self.send("GET", path)
        match = re.search(rb'name="_token"\s+value="([^"]+)"', body)
        return match.group(1).decode() if match else ""


def anonymous(w):
    w.get("anon-landing-redirects-to-login", "/", 302)
    w.get("anon-login-page", "/users/login")
    w.get("anon-register-page", "/register")
    w.get("anon-api-user", "/api/user", None)
    w.post("csrf-cross-site-without-token", "/users/authenticate",
           [("email", EMAIL), ("password", "x")], expect=419, token=False, fetch_site="cross-site")
    w.post("csrf-no-fetch-metadata-without-token", "/users/authenticate",
           [("email", EMAIL), ("password", "x")], expect=419, token=False, fetch_site=None)
    # Laravel 13 accepts same-origin Sec-Fetch-Site without a token; browsers
    # still send one. Recorded without an expectation so the change stays visible.
    w.post("csrf-same-origin-without-token", "/users/authenticate",
           [("email", EMAIL), ("password", "x")], expect=None, token=False)


def register(w):
    token = w.form_token("/register")
    w.post("register", "/users", [("_token", token), ("name", "Walker"), ("email", EMAIL),
                                  ("password", PASSWORD)], token=False)
    w.post("logout", "/users/logout")


def login(w):
    token = w.form_token("/users/login")
    w.post("login-wrong-password", "/users/authenticate",
           [("_token", token), ("email", EMAIL), ("password", "wrong")], token=False)
    token = w.form_token("/users/login")
    w.post("login", "/users/authenticate",
           [("_token", token), ("email", EMAIL), ("password", PASSWORD)], token=False)


def create_data(w):
    for index, (name, continent) in enumerate(
        [("Brasil", 2), ("Italia", 1), ("Monaco", 1)], start=1
    ):
        w.multipart(f"pais-store-{index}", "/paises/store",
                    [("des_nome", name), ("continente_id", continent), ("nome", name)],
                    [("imagem", f"{name}.png", png(index * 40, 10, 10))])

    drivers = [("Ayrton", "Alfa", 1), ("Bruno", "Bravo", 2), ("Carlos", "Charlie", 1),
               ("Diego", "Delta", 3), ("Ernesto", "Eco", 2)]
    for index, (first, last, country) in enumerate(drivers, start=1):
        w.multipart(f"piloto-store-{index}", "/pilotos/store",
                    [("nome", first), ("sobrenome", last), ("pais_id", country)],
                    [("imagem", f"{first}.png", png(10, index * 40, 10))])

    for index, (name, colour, country) in enumerate(
        [("Rossa", "#ff0000", 2), ("Verde", "#00ff00", 1), ("Azul", "#0000ff", 3)], start=1
    ):
        w.multipart(f"equipe-store-{index}", "/equipes/store",
                    [("nome", name), ("des_cor", colour), ("pais_id", country)],
                    [("imagem", f"{name}.png", png(10, 10, index * 40))])

    w.post("skin-store", "/skin/store", [("skin", "Padrao"), ("equipe_id", 1)])
    for index, (name, country, length) in enumerate(
        [("Interlagos", 1, 4309), ("Imola", 2, 4909), ("Monaco", 3, 3337)], start=1
    ):
        w.post(f"pista-store-{index}", "/pistas/store",
               [("nome", name), ("pais_id", country), ("tamanho_km", length), ("qtd_carros", 20),
                ("drs", "S"), ("tipo", "Misto")])
    for index, year in enumerate(["2024", "2025"], start=1):
        w.post(f"ano-store-{index}", "/anos/store", [("ano", year)])
    for index, (label, icon) in enumerate([("Sol", "sun"), ("Chuva", "rain")], start=1):
        w.post(f"condicao-store-{index}", "/condicaoClimatica/store",
               [("descricao", label), ("des_icone", icon)])
    for index, name in enumerate(["GP do Brasil", "GP da Italia", "GP de Monaco"], start=1):
        w.post(f"evento-store-{index}", "/eventos/store", [("des_nome", name)])
    for index, (label, year) in enumerate([("Temporada 2024", 1), ("Temporada 2025", 2)], start=1):
        w.post(f"temporada-store-{index}", "/temporadas/store",
               [("des_temporada", label), ("ano_id", year), ("observacoes", "Sintetica"),
                ("referencia", "REF")])
    for index, (driver, team, year) in enumerate(
        [(1, 1, 1), (2, 1, 1), (3, 2, 1), (4, 2, 1), (1, 1, 2), (2, 2, 2), (3, 2, 2), (4, 1, 2)],
        start=1,
    ):
        w.post(f"pilotoequipe-store-{index}", "/pilotoEquipe/store",
               [("piloto_id", driver), ("equipe_id", team), ("ano_id", year), ("skin_id", 1),
                ("modelo_carro", "Carro")])

    # (season, track, order, event, sprint)
    races = [(1, 1, 1, 1, "N"), (1, 2, 2, 2, "N"), (1, 3, 3, 3, "S"), (2, 1, 1, 1, "N"), (2, 2, 2, 2, "N")]
    for index, (season, track, order, event, sprint) in enumerate(races, start=1):
        w.post(f"corrida-store-{index}", f"/corridas/store/{season}",
               [("temporada_id", season), ("pista_id", track), ("ordem", order), ("evento_id", event),
                ("flg_sprint", sprint), ("dificuldade_ia", 90), ("qtd_safety_car", 1)])

    # (race, category, entries, fastest lap, retired entry). Each entry is
    # (driver-team id, grid, finish). The categories cover distinct scoring rules.
    results = [
        (1, "F1", [(1, 1, 1), (2, 2, 3), (3, 3, 2), (4, 4, 4)], 1, None),
        (2, "FormulaE", [(1, 3, 2), (2, 1, 1), (3, 2, 3), (4, 4, 4)], 2, 4),
        (3, "F1Sprint", [(1, 2, 2), (2, 4, 4), (3, 1, 1), (4, 3, 3)], 3, None),
        (4, "Indy", [(5, 1, 3), (6, 2, 1), (7, 3, 2), (8, 4, 4)], 6, None),
        (5, "SC", [(5, 2, 1), (6, 1, 2), (7, 4, 3), (8, 3, 4)], 5, 7),
    ]
    for race, category, entries, fastest, retired in results:
        w.get(f"resultado-edit-before-{race}", f"/resultados/edit/{race}")
        fields = [("categoria", category), ("condicao_id", 1), ("qtd_safety_car", 1),
                  ("dificuldade_ia", 95), ("observacoes", f"Corrida {race}"), ("volta_rapida", fastest)]
        for entry, grid, finish in entries:
            fields += [("pilotoEquipe_id[]", entry), ("largada[]", grid), ("chegada[]", finish)]
        if retired:
            fields.append(("flg_abandono[]", retired))
        files = [("imagens_corrida[]", f"race {race}.png", png(race * 30, race * 30, 0))] if race == 1 else []
        w.multipart(f"resultado-update-{race}", f"/resultados/update/{race}", fields, files)

    # Finishing a season computes the champions shown on the home and team pages.
    w.post("temporada-finaliza", "/temporadas/update/1",
           [("_method", "PUT"), ("des_temporada", "Temporada 2024"), ("ano_id", 1), ("observacoes", "Sintetica"),
            ("referencia", "REF"), ("flg_finalizada", "S")])


def read_pages(w):
    pages = [
        "/", "/dashboard", "/home", "/estatisticas", "/teste", "/estudos", "/tempos",
        "/estatisticasPilotosEquipes/index",
        "/paises/index", "/paises/create", "/paises/edit/1", "/paises/show/1",
        "/pilotos/index", "/pilotos/create", "/pilotos/edit/1", "/pilotos/show/1", "/pilotos/show/2",
        "/pilotos/comparativo", "/pilotos/performance-por-pista",
        "/pilotos/relacaoForcas/index", "/pilotos/relacaoForcas/listagemPilotos/1",
        "/pilotos/relacaoForcas/edit/1/1",
        "/equipes/index", "/equipes/create", "/equipes/edit/1", "/equipes/show/1",
        "/equipes/relacaoForcas/index", "/equipes/relacaoForcas/listagemEquipes/1",
        "/equipes/relacaoForcas/edit/1/1",
        "/skin/index", "/skin/create", "/skin/edit/1",
        "/pistas/index", "/pistas/create", "/pistas/edit/1", "/pistas/show/1", "/pistas/adicionarAutor",
        "/eventos/index", "/eventos/create", "/eventos/edit/1", "/eventos/show/1",
        "/pilotoEquipe/index", "/pilotoEquipe/create", "/pilotoEquipe/edit/1",
        "/condicaoClimatica/index", "/condicaoClimatica/create", "/condicaoClimatica/edit/1",
        "/temporadas/index", "/temporadas/create", "/temporadas/edit/1",
        "/temporadas/classificacao/1", "/temporadas/classificacao/2",
        "/temporadas/resultados/1", "/temporadas/resultados/2", "/temporadas/resultados/1/1",
        "/corridas/index/1", "/corridas/index/2", "/corridas/adicionar/1", "/corridas/edit/1/1",
        "/resultados/edit/1", "/resultados/show/1", "/resultados/show/3", "/resultados/show/5",
        "/anos/index", "/anos/create", "/anos/edit/1",
        "/visualizarVitoriasPiloto/1", "/visualizarVitoriasPiloto/2",
        "/api/testes", "/api/classificacao",
    ]
    for path in pages:
        w.get("page" + re.sub(r"[^a-zA-Z0-9]+", "-", path).rstrip("-"), path, None)

    period = [("inicio", 1), ("fim", 20), ("pais_id", 1), ("temporada_id", 1)]
    calls = [
        ("/paises/ajaxGetChegadasPilotos", period),
        ("/paises/ajaxGetLargadasPilotos", period),
        ("/paises/ajaxGetChegadasEquipes", period),
        ("/paises/ajaxGetLargadasEquipes", period),
        ("/pilotos/busca-performance-por-pista", [("idPiloto", 1), ("idPista", 1)]),
        ("/ajax/ajaxGetStatsEquipePorTemporada", [("equipe_id", 1), ("temporada_id", 1)]),
        ("/ajax/ajaxGetStatsPilotoPorTemporada", [("piloto_id", 1), ("temporada_id", 1)]),
        ("/temporadas/classificacao", [("id", 1), ("idCorrida", 2)]),
        ("/ajax/classificacaoGeralPorTemporada", [("temporada_id", 1)]),
        ("/ajax/comparativos", [("temporada_id", 1), ("pilotosId[]", 1), ("pilotosId[]", 2), ("flg_sprint", "N")]),
        ("/ajax/getPilotosPorTemporada", [("temporada_id", 1)]),
        ("/ajax/ajaxGetVitoriasPilotoPorTemporada", [("vitoriasPilotosTemporadaId", 1)]),
        ("/ajax/ajaxGetVitoriasEquipePorTemporada", [("vitoriasEquipesTemporadaId", 1)]),
        ("/ajax/ajaxGetPolesPilotosPorTemporada", [("polesPilotosTemporadaId", 1)]),
        ("/ajax/ajaxGetPolesEquipesPorTemporada", [("polesEquipesTemporadaId", 1)]),
        ("/ajax/ajaxGetPodiosPilotoPorTemporada", [("podiosPilotosTemporadaId", 1)]),
        ("/ajax/ajaxGetPodiosEquipesPorTemporada", [("podiosEquipesTemporadaId", 1)]),
        ("/ajax/url_chegada_pilotos", period + [("anoInicioChegadaPilotos", 2024), ("anoFimChegadaPilotos", 2025)]),
        ("/ajax/url_chegada_equipes", period + [("anoInicioChegadaEquipes", 2024), ("anoFimChegadaEquipes", 2025)]),
        ("/ajax/url_largada_pilotos", period + [("anoInicioLargadaPilotos", 2024), ("anoFimLargadaPilotos", 2025)]),
        ("/ajax/url_largada_equipes", period + [("anoInicioLargadaEquipes", 2024), ("anoFimLargadaEquipes", 2025)]),
        ("/fetch/getClassificacaoAposCorrida", [("corrida_id", 2), ("temporada_id", 1)]),
        ("/ajax/ajaxGetChegadasPilotosPorTemporada", [("inicio", 1), ("fim", 20), ("temporada", 1), ("tipoCorrida", "N")]),
        ("/ajax/ajaxGetChegadasEquipesPorTemporada", [("inicio", 1), ("fim", 20), ("temporada", 1)]),
        ("/buscaResultadosCorrida", [("busca", ""), ("flg_sprint", "N"), ("qtdResultados", 10), ("temporada", "todas")]),
        ("/estatisticasPilotosEquipes/buscar", [("temporada[]", 1), ("flg_sprint", "N"), ("limite", 3), ("tipo", "chegada"),
                                                ("pilotos_ignorados[]", 0), ("equipes_ignoradas[]", 0)]),
    ]
    for path, fields in calls:
        w.post("ajax" + re.sub(r"[^a-zA-Z0-9]+", "-", path).rstrip("-"), path, fields, expect=None)

    w.get("excel-export-piloto-1", "/pilotos/export/1")
    # Renders a PDF and then fails on the unresolvable mail host, as in production.
    w.get("pdf-mail-route", "/send-email-pdf", None)


def uploaded_name(w, page, prefix):
    _, _, body = w.send("GET", page)
    match = re.search(r"images/(%s[^\"' )]+)" % prefix, body.decode("utf-8", "replace"))
    return urllib.parse.unquote(match.group(1)) if match else None


def images(w):
    name = uploaded_name(w, "/pilotos/index", r"\d{10}-Ayrton")
    if name:
        w.get("image-served-piloto-1", "/images/" + urllib.parse.quote(name))
    else:
        w.unexpected.write("image-served-piloto-1: no image reference on the edit page\n")


def change_and_delete(w):
    old = uploaded_name(w, "/pilotos/index", r"\d{10}-Ernesto")
    w.multipart("piloto-update-replaces-image", "/pilotos/update/5",
                [("_method", "PUT"), ("nome", "Ernesto"), ("sobrenome", "Eco Novo"), ("pais_id", 2)],
                [("imagem", "Ernesto2.png", png(200, 200, 200))])
    new = uploaded_name(w, "/pilotos/index", r"\d{10}-Ernesto")
    if new:
        w.get("image-replacement-served", "/images/" + urllib.parse.quote(new))
    if old and old != new:
        w.get("image-replaced-is-gone", "/images/" + urllib.parse.quote(old), 404)
    w.post("ano-update", "/anos/update/2", [("_method", "PUT"), ("ano", "2025")])
    w.post("relacao-forca-piloto", "/pilotos/relacaoForcas/update",
           [("ano_id", 1), ("piloto_id", 1), ("forca", 90)])
    w.post("relacao-forca-equipe", "/equipes/relacaoForcas/update",
           [("ano_id", 1), ("equipe_id", 1), ("forca", 85)])

    current = uploaded_name(w, "/pilotos/index", r"\d{10}-Ernesto")
    w.get("piloto-delete", "/pilotos/delete?piloto_id=5", 302)
    if current:
        w.get("image-deleted-is-gone", "/images/" + urllib.parse.quote(current), 404)
    w.get("equipe-delete", "/equipes/delete/3", None)
    w.post("logout-end", "/users/logout")
    w.get("after-logout-redirects", "/home", 302)


def main():
    mode, out = sys.argv[1], sys.argv[2]
    walker = Walker(out)
    if mode == "full":
        anonymous(walker)
        register(walker)
        login(walker)
        create_data(walker)
        read_pages(walker)
        images(walker)
        change_and_delete(walker)
    elif mode == "read":
        anonymous(walker)
        login(walker)
        read_pages(walker)
        images(walker)
    else:
        sys.exit(f"unknown mode {mode}")
    statuses = {}
    walker.summary.flush()
    with open(os.path.join(out, "summary.tsv"), encoding="utf-8") as file:
        for line in file:
            status = line.split("\t")[2]
            statuses[status] = statuses.get(status, 0) + 1
    print(f"{walker.step} steps; statuses {dict(sorted(statuses.items()))}")


if __name__ == "__main__":
    main()
