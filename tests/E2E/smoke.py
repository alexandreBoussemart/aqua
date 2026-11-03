"""Test bout en bout de l'appli Symfony : login, pages, formulaires (avec CSRF), AJAX, sécurité."""
import http.cookiejar
import re
import sys
import urllib.parse
import urllib.request
from html.parser import HTMLParser

BASE = sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8080/aqua"
USER, PWD = (sys.argv[2], sys.argv[3]) if len(sys.argv) > 3 else ("admin", "admin")
PAGES = ["/", "/configuration", "/analyse", "/schema", "/cablage-gpio", "/logs", "/monitoring", "/relais", "/crons"]
ERR = re.compile(r"(Fatal error|Warning:|Deprecated:|Notice:|Uncaught|Exception|Twig\\Error|An exception has been thrown)[^\n<]{0,200}")


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect)
fails = 0


def fetch(path, data=None, headers=None):
    url = path if path.startswith("http") else BASE + path
    req = urllib.request.Request(url, data=urllib.parse.urlencode(data).encode() if data is not None else None, headers=headers or {})
    try:
        r = opener.open(req, timeout=180)
        return r.status, r.read().decode("utf-8", "replace"), r.headers
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace"), e.headers


def check(label, ok, detail=""):
    global fails
    fails += not ok
    print(f"[{'ok' if ok else 'ÉCHEC'}] {label} {detail}")


def errors(body):
    return sorted(set(m.group(0)[:160] for m in ERR.finditer(body)))


# --- non connecté : redirection vers /login
st, _, h = fetch("/configuration")
check("accès anonyme redirigé vers login", st == 302 and "/login" in (h.get("Location") or ""), f"{st} {h.get('Location')}")

# --- login
st, body, _ = fetch("/login")
token = re.search(r'name="_csrf_token" value="([^"]+)"', body).group(1)
st, _, h = fetch("/login", {"_username": "e2e-intrus", "_password": "mauvais", "_csrf_token": token})
st2, body2, _ = fetch("/login")
check("mauvais mot de passe refusé", "Identifiants invalides" in body2)
token = re.search(r'name="_csrf_token" value="([^"]+)"', body2).group(1)
st, _, h = fetch("/login", {"_username": USER, "_password": PWD, "_csrf_token": token})
check("login", st == 302 and "/login" not in (h.get("Location") or ""), f"{st} {h.get('Location')}")

# --- pages
app_token = None
for page in PAGES:
    st, body, _ = fetch(page)
    errs = errors(body) if page != "/logs" else []  # /logs affiche le journal, qui peut contenir des messages d'erreur
    check(f"GET {page}", st == 200 and not errs, f"{st} {errs[:2]}")
    m = re.search(r'name="_token" value="([^"]+)"', body)
    app_token = app_token or (m and m.group(1))


class Forms(HTMLParser):
    def __init__(self):
        super().__init__()
        self.forms, self.cur = [], None

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == "form":
            self.cur = {"action": a.get("action", ""), "method": a.get("method", "get").lower(), "id": a.get("id"), "fields": []}
        elif self.cur is not None and tag == "input" and a.get("name"):
            t = a.get("type", "text")
            if t == "checkbox":
                if "checked" in a:
                    self.cur["fields"].append((a["name"], "on"))
            else:
                v = a.get("value", "")
                if not v:
                    v = {"date": "2026-11-02", "number": "12"}.get(t, "1" if a["name"].startswith("value") else "test")
                self.cur["fields"].append((a["name"], v))

    def handle_endtag(self, tag):
        if tag == "form" and self.cur is not None:
            self.forms.append(self.cur)
            self.cur = None


# --- formulaires (sauf suppressions, testées à part)
for page in PAGES:
    st, body, _ = fetch(page)
    p = Forms()
    p.feed(body)
    for f in p.forms:
        if f["method"] != "post" or f["action"].startswith("javascript") or "supprimer" in f["action"] or f["id"] in ("form-status", "actions-rapides"):
            continue
        st, rbody, h = fetch(urllib.parse.urljoin(BASE + page, f["action"]), dict(f["fields"]))
        check(f"POST {f['action']} (depuis {page})", st in (200, 302) and not errors(rbody), f"{st} {errors(rbody)[:2]}")

# --- AJAX
hdr = {"X-CSRF-TOKEN": app_token}
st, body, _ = fetch("/configuration/statuts", {"osmolateur": "on", "ecumeur": "on", "bailling": "on", "temperature": "on",
                                                 "temperature_air": "on", "temperature_rpi": "on", "cron_controle": "on", "mail": "on",
                                                 "on_off_ecumeur": "on"}, hdr)
check("AJAX statuts", st == 200 and body == "ok", f"{st} {body[:80]}")
st, body, _ = fetch("/actions-rapides", {"on_off_ecumeur": "on"}, hdr)
check("AJAX actions rapides", st == 200, f"{st}")
st, body, _ = fetch("/configuration/temperature/eau", {}, hdr)
check("AJAX température eau", st == 200 and "°C" in body, body[:60])
st, body, _ = fetch("/crons/run", {"file": "app:cron:timer"}, hdr)
check("AJAX run cron", st == 200 and "Erreur" not in body, body[:120])

# --- sécurité
st, body, _ = fetch("/crons/run", {"file": "cache:clear"}, hdr)
check("run d'une commande hors liste refusé", st == 400, f"{st}")
st, body, _ = fetch("/analyse", {"value_ca": "420"})
check("POST sans jeton CSRF refusé", st == 403, f"{st}")
st, body, _ = fetch("/changement-eau", {"_token": app_token, "value": "<script>alert(1)</script>"})
check("valeur non numérique refusée", st == 302)
st, body, _ = fetch("/budget")
check("page budget supprimée", st == 404, f"{st}")
print("TOTAL ÉCHECS:", fails)
sys.exit(1 if fails else 0)
