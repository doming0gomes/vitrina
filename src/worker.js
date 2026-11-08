// Vitrina · tu página de enlaces (tipo Linktree) en tu propia cuenta gratuita de Cloudflare.
//   /          → la página pública
//   /admin     → el panel privado (con contraseña)
//   /api/...   → lo que usa el panel
//   /i/<id>    → las imágenes que subes
// Datos: una base de datos D1 conectada con el nombre DB. Contraseña inicial: el secreto CONTRASENA.

import { vitrinaRenderer } from "./render.js";
import PANEL_HTML from "./panel.html";

const R = vitrinaRenderer();
// (el empaquetador puede añadir __name(); se define vacío para el navegador)
const RENDER_SRC = "var __name=function(f){return f};var VitrinaR=(" + vitrinaRenderer.toString().replace(/<\/(script)/gi, "<\\/$1") + ")();";
const VERSION = "1.0.0";

const ITER = 20000;               // PBKDF2: medido en Cloudflare, 50.000 pasaba de los 10 ms de CPU del plan gratis
const SESSION_DAYS = 30;
const MAX_FAILS = 8;              // intentos fallidos por IP…
const FAIL_WINDOW = 15 * 60e3;    // …en 15 minutos
const MAX_IMG = 1400 * 1024;      // las fotos se reducen en el navegador; esto es el tope duro
const MAX_IMAGES = 400;
const MAX_CONFIG = 200 * 1024;
const MAX_BACKUP = 40 * 1024 * 1024;

// ---------------------------------------------------------------- base de datos
let ready = null;
function db(env) {
  if (!env.DB || typeof env.DB.prepare !== "function") throw new SetupError("nodb");
  if (!ready) {
    ready = env.DB.batch([
      env.DB.prepare("CREATE TABLE IF NOT EXISTS kv (k TEXT PRIMARY KEY, v TEXT NOT NULL)"),
      env.DB.prepare("CREATE TABLE IF NOT EXISTS img (id TEXT PRIMARY KEY, type TEXT NOT NULL, data TEXT NOT NULL, created INTEGER NOT NULL)"),
      env.DB.prepare("CREATE TABLE IF NOT EXISTS ses (h TEXT PRIMARY KEY, exp INTEGER NOT NULL)"),
      env.DB.prepare("CREATE TABLE IF NOT EXISTS fails (ip TEXT NOT NULL, ts INTEGER NOT NULL)"),
      env.DB.prepare("CREATE INDEX IF NOT EXISTS fails_ip ON fails (ip, ts)"),
      env.DB.prepare("CREATE TABLE IF NOT EXISTS stats (day TEXT NOT NULL, k TEXT NOT NULL, n INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, k))")
    ]).catch((e) => { ready = null; throw e; });
  }
  return ready.then(() => env.DB);
}
class SetupError extends Error { constructor(code) { super(code); this.code = code; } }

async function kvGet(env, k) {
  const row = await (await db(env)).prepare("SELECT v FROM kv WHERE k = ?").bind(k).first();
  return row ? row.v : null;
}
async function kvSet(env, k, v) {
  await (await db(env)).prepare("INSERT INTO kv (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = excluded.v").bind(k, v).run();
}
async function kvDel(env, k) {
  await (await db(env)).prepare("DELETE FROM kv WHERE k = ?").bind(k).run();
}
async function loadConfig(env) {
  const raw = await kvGet(env, "config");
  if (!raw) return null;
  try { return R.sanitize(JSON.parse(raw)); } catch { return null; }
}

// ---------------------------------------------------------------- utilidades
const enc = new TextEncoder();
function hexOf(buf) { return [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, "0")).join(""); }
function randHex(bytes) { return hexOf(crypto.getRandomValues(new Uint8Array(bytes))); }
async function sha256(s) { return hexOf(await crypto.subtle.digest("SHA-256", enc.encode(s))); }
async function sameText(a, b) {
  const [ha, hb] = await Promise.all([a, b].map((s) => crypto.subtle.digest("SHA-256", enc.encode(String(s)))));
  return crypto.subtle.timingSafeEqual(ha, hb);
}
async function pbkdf2(pass, saltHex, iter) {
  const key = await crypto.subtle.importKey("raw", enc.encode(pass), "PBKDF2", false, ["deriveBits"]);
  const salt = new Uint8Array(saltHex.match(/../g).map((h) => parseInt(h, 16)));
  return hexOf(await crypto.subtle.deriveBits({ name: "PBKDF2", hash: "SHA-256", salt, iterations: iter }, key, 256));
}
function b64encode(bytes) {
  if (typeof bytes.toBase64 === "function") return bytes.toBase64();
  let s = "";
  for (let i = 0; i < bytes.length; i += 0x8000) s += String.fromCharCode.apply(null, bytes.subarray(i, i + 0x8000));
  return btoa(s);
}
function b64decode(str) {
  if (typeof Uint8Array.fromBase64 === "function") return Uint8Array.fromBase64(str);
  const bin = atob(str);
  const out = new Uint8Array(bin.length);
  for (let i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
  return out;
}
function imageType(b) {
  if (b.length < 12) return "";
  if (b[0] === 0x89 && b[1] === 0x50 && b[2] === 0x4e && b[3] === 0x47) return "image/png";
  if (b[0] === 0xff && b[1] === 0xd8 && b[2] === 0xff) return "image/jpeg";
  if (b[0] === 0x47 && b[1] === 0x49 && b[2] === 0x46 && b[3] === 0x38) return "image/gif";
  if (b[0] === 0x52 && b[1] === 0x49 && b[2] === 0x46 && b[3] === 0x46 && b[8] === 0x57 && b[9] === 0x45 && b[10] === 0x42 && b[11] === 0x50) return "image/webp";
  return "";
}
function today() { return new Date().toISOString().slice(0, 10); }

const BASE_HEADERS = {
  "x-content-type-options": "nosniff",
  "referrer-policy": "strict-origin-when-cross-origin",
  "permissions-policy": "camera=(), microphone=(), geolocation=(), interest-cohort=()"
};
function json(obj, status = 200, extra = {}) {
  return new Response(JSON.stringify(obj), {
    status,
    headers: { ...BASE_HEADERS, "content-type": "application/json; charset=utf-8", "cache-control": "no-store", ...extra }
  });
}
function fail(msg, status = 400) { return json({ error: msg }, status); }

async function readJson(request, max) {
  const len = parseInt(request.headers.get("content-length") || "0", 10);
  if (len > max) throw new HttpError(413, "Es demasiado grande.");
  const text = await request.text();
  if (text.length > max) throw new HttpError(413, "Es demasiado grande.");
  try { return JSON.parse(text); } catch { throw new HttpError(400, "Los datos no son válidos."); }
}
class HttpError extends Error { constructor(status, msg) { super(msg); this.status = status; } }

// ---------------------------------------------------------------- sesión y contraseña
function cookieOf(request, name) {
  const m = (request.headers.get("cookie") || "").match(new RegExp("(?:^|;\\s*)" + name + "=([a-f0-9]{64})"));
  return m ? m[1] : "";
}
function sessionCookie(token, url, maxAge) {
  const secure = url.protocol === "https:" ? "; Secure" : "";
  return `vt=${token}; Path=/; HttpOnly; SameSite=Strict; Max-Age=${maxAge}${secure}`;
}
async function isLogged(request, env) {
  const token = cookieOf(request, "vt");
  if (!token) return false;
  const row = await (await db(env)).prepare("SELECT exp FROM ses WHERE h = ?").bind(await sha256(token)).first();
  return !!row && row.exp > Date.now();
}
// La IP solo se usa (con sal aleatoria propia de cada instalación) para frenar contraseñas fallidas.
let ipSalt = "";
async function ipKey(request, env) {
  if (!ipSalt) {
    ipSalt = (await kvGet(env, "ipSalt")) || "";
    if (!ipSalt) { ipSalt = randHex(16); await kvSet(env, "ipSalt", ipSalt); }
  }
  return (await sha256(ipSalt + ":" + (request.headers.get("cf-connecting-ip") || "local"))).slice(0, 32);
}

// La contraseña: al principio vale el secreto CONTRASENA. Si la cambias en el panel se guarda
// con hash (PBKDF2) y el secreto deja de valer… hasta que cambies el secreto en Cloudflare:
// entonces vuelve a mandar el secreto (así recuperas el acceso si la olvidas).
let secretChecked = false;
const EXAMPLE_PASSWORD = "pon-aqui-una-contrasena-larga";
async function passwordState(env) {
  const secret = typeof env.CONTRASENA === "string" ? env.CONTRASENA : "";
  let rec = null;
  try { rec = JSON.parse((await kvGet(env, "pw")) || "null"); } catch { rec = null; }
  if (secret && !secretChecked) {
    // «Huella» del secreto (barata, con sal) para saber si lo has cambiado en Cloudflare.
    // Si cambió: vuelve a mandar el secreto y se cierran todas las sesiones abiertas.
    let sf = null;
    try { sf = JSON.parse((await kvGet(env, "secretFp")) || "null"); } catch { sf = null; }
    const salt = sf && sf.salt ? sf.salt : randHex(16);
    const fp = await sha256(salt + ":" + secret);
    if (!sf) await kvSet(env, "secretFp", JSON.stringify({ salt, fp }));
    else if (!(await sameText(fp, sf.fp))) {
      const DB = await db(env);
      await DB.batch([DB.prepare("DELETE FROM ses"), DB.prepare("DELETE FROM kv WHERE k = 'pw'")]);
      await kvSet(env, "secretFp", JSON.stringify({ salt, fp }));
      rec = null;
    }
    secretChecked = true;
  }
  if (rec && rec.src === "panel") return { mode: "panel", rec, secret };
  if (secret && (secret.length < 10 || secret === EXAMPLE_PASSWORD)) return { mode: "weak" };
  if (secret) return { mode: "secret", secret };
  return { mode: "none" };
}
async function verifyPassword(env, pass) {
  const st = await passwordState(env);
  if (st.mode === "secret") return sameText(pass, st.secret);
  if (st.mode === "panel") return sameText(await pbkdf2(pass, st.rec.salt, st.rec.iter), st.rec.hash);
  return false;
}

// ---------------------------------------------------------------- páginas
function publicHeaders(extra = {}) {
  return {
    ...BASE_HEADERS,
    "content-type": "text/html; charset=utf-8",
    "cache-control": "public, max-age=0, must-revalidate",
    "content-security-policy": "default-src 'none'; img-src 'self'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'",
    ...extra
  };
}

function placeholderPage() {
  return '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' +
    '<meta name="robots" content="noindex"><title>Página en preparación</title><style>' +
    "body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3efe6;color:#161513;font:16px/1.5 system-ui,-apple-system,'Segoe UI',sans-serif;text-align:center;padding:24px;box-sizing:border-box}" +
    "h1{font-size:22px;margin:0 0 8px}p{margin:0;opacity:.7}a{color:inherit}</style></head><body><div>" +
    "<h1>Esta página está en preparación</h1><p>Si es tuya, entra en <a href=\"/admin\">/admin</a> para montarla.</p></div></body></html>";
}

async function publicPage(request, env, url, ctx) {
  let cfg = null;
  try { cfg = await loadConfig(env); } catch (e) { if (!(e instanceof SetupError)) throw e; }
  if (!cfg) return new Response(placeholderPage(), { status: 200, headers: publicHeaders({ "cache-control": "no-store" }) });
  // La visita se cuenta aquí mismo (sin otra petición), salvo robots y vistas previas de enlaces.
  const ua = request.headers.get("user-agent") || "";
  const purpose = (request.headers.get("sec-purpose") || request.headers.get("purpose") || "").toLowerCase();
  if (request.method === "GET" && ua && !BOT_RE.test(ua) && !purpose.includes("prefetch") && !tooMany(request)) {
    ctx.waitUntil(count(env, ctx, "v").catch(() => {}));
  }
  const html = R.renderPage(cfg, {
    img: (id) => "/i/" + id,
    absImg: (id) => url.origin + "/i/" + id,
    hit: "/api/hit",
    inlineScript: true
  });
  return new Response(html, { headers: publicHeaders({ "content-security-policy": await publicCsp() }) });
}

let hitHash = "";
async function publicCsp() {
  if (!hitHash) {
    const d = await crypto.subtle.digest("SHA-256", enc.encode(R.HIT_SCRIPT));
    hitHash = btoa(String.fromCharCode(...new Uint8Array(d)));
  }
  return `default-src 'none'; img-src 'self'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'sha256-${hitHash}'; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'`;
}

function panelPage(url) {
  const nonce = randHex(16);
  const html = PANEL_HTML
    .replace(/__NONCE__/g, nonce)
    .replace("/*__RENDER__*/", () => RENDER_SRC)
    .replace("/*__HOST__*/", () => "var VITRINA_HOST=" + JSON.stringify({ kind: "cloudflare", api: "/api/{p}", img: "/i/{id}", page: url.origin + "/", version: VERSION }) + ";");
  return new Response(html, {
    headers: {
      ...BASE_HEADERS,
      "content-type": "text/html; charset=utf-8",
      "cache-control": "no-store",
      "referrer-policy": "no-referrer",
      "x-frame-options": "DENY",
      "x-robots-tag": "noindex, nofollow",
      "content-security-policy": `default-src 'none'; script-src 'nonce-${nonce}'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' blob: data:; connect-src 'self'; frame-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'`
    }
  });
}

async function serveImage(env, id) {
  if (!/^[a-f0-9]{24}$/.test(id)) return new Response("No encontrada", { status: 404 });
  const row = await (await db(env)).prepare("SELECT type, data FROM img WHERE id = ?").bind(id).first();
  if (!row) return new Response("No encontrada", { status: 404, headers: { "cache-control": "no-store" } });
  return new Response(b64decode(row.data), {
    headers: {
      ...BASE_HEADERS,
      "content-type": row.type,
      "cache-control": "public, max-age=31536000, immutable",
      "content-security-policy": "default-src 'none'; sandbox"
    }
  });
}

// ---------------------------------------------------------------- API
const BOT_RE = /bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|slack|discord|embedly|headless|lighthouse|curl|wget|python|go-http/i;

async function hit(request, env, ctx) {
  // Sin cookies, sin IP guardada: solo se suma 1 al contador del día.
  const ua = request.headers.get("user-agent") || "";
  if (!ua || BOT_RE.test(ua)) return new Response(null, { status: 204 });
  let d;
  try { d = await readJson(request, 512); } catch { return new Response(null, { status: 204 }); }
  if (!d || typeof d !== "object") return new Response(null, { status: 204 });
  if (tooMany(request)) return new Response(null, { status: 204 });
  let key = "";
  if (d.t === "c" || d.t === "s") {
    const cfg = await loadConfig(env);
    if (!cfg) return new Response(null, { status: 204 });
    if (d.t === "c" && cfg.items.some((i) => i.id === d.id && i.kind === "link" && !i.hidden)) key = "c:" + d.id;
    if (d.t === "c" && d.id === "video" && cfg.feature.url) key = "c:video";
    if (d.t === "s" && cfg.socials.some((s) => s.type === d.id)) key = "s:" + d.id;
  }
  if (!key) return new Response(null, { status: 204 });
  await count(env, ctx, key);
  return new Response(null, { status: 204 });
}

async function count(env, ctx, key) {
  const DB = await db(env);
  await DB.prepare("INSERT INTO stats (day, k, n) VALUES (?, ?, 1) ON CONFLICT(day, k) DO UPDATE SET n = n + 1").bind(today(), key).run();
  if (Math.random() < 0.02) ctx.waitUntil(cleanup(env));
}

// Freno sencillo contra quien mande miles de visitas falsas: 60 por minuto y por IP
// (en memoria de cada servidor; no guarda nada).
const recent = new Map();
function tooMany(request) {
  const ip = request.headers.get("cf-connecting-ip") || "local";
  const minute = Math.floor(Date.now() / 60000);
  const k = ip + "|" + minute;
  const n = (recent.get(k) || 0) + 1;
  recent.set(k, n);
  if (recent.size > 5000) recent.clear();
  return n > 60;
}

async function cleanup(env) {
  const DB = await db(env);
  const old = new Date(Date.now() - 400 * 864e5).toISOString().slice(0, 10);
  await DB.batch([
    DB.prepare("DELETE FROM stats WHERE day < ?").bind(old),
    DB.prepare("DELETE FROM fails WHERE ts < ?").bind(Date.now() - 864e5),
    DB.prepare("DELETE FROM ses WHERE exp < ?").bind(Date.now())
  ]);
}

async function handleApi(request, env, url, ctx) {
  const path = url.pathname.slice(5); // sin "/api/"
  const method = request.method;

  if (path === "hit" && method === "POST") return hit(request, env, ctx);

  // Todo lo demás es del panel: misma web y una cabecera propia (una web ajena no puede ponerla).
  const origin = request.headers.get("origin");
  if (method !== "GET" && origin && origin !== url.origin) return fail("Origen no permitido.", 403);
  if (method !== "GET" && request.headers.get("x-vitrina") !== "1") return fail("Petición no válida.", 403);

  if (path === "estado" && method === "GET") {
    let setup = null, logged = false;
    try {
      const st = await passwordState(env);
      if (st.mode === "none") setup = "nopass";
      else if (st.mode === "weak") setup = "weak";
      else logged = await isLogged(request, env);
    } catch (e) {
      if (e instanceof SetupError) setup = e.code; else throw e;
    }
    return json({ setup, logged, version: VERSION });
  }

  if (path === "entrar" && method === "POST") {
    const DB = await db(env);
    const ip = await ipKey(request, env);
    const since = Date.now() - FAIL_WINDOW;
    const body = await readJson(request, 2048);
    const pass = typeof body.password === "string" ? body.password : "";
    const st = await passwordState(env);
    if (st.mode === "none") return fail("Falta la contraseña inicial (el secreto CONTRASENA en Cloudflare).", 409);
    if (st.mode === "weak") return fail("La contraseña de Cloudflare es demasiado corta o es la del ejemplo. Cámbiala por una de 10 caracteres o más.", 409);
    // Primero se apunta el intento y luego se cuenta (así varios intentos a la vez no se cuelan).
    const ts = Date.now() + Math.random();
    const [, cnt] = await DB.batch([
      DB.prepare("INSERT INTO fails (ip, ts) VALUES (?, ?)").bind(ip, ts),
      DB.prepare("SELECT COUNT(*) AS n FROM fails WHERE ip = ? AND ts > ?").bind(ip, since)
    ]);
    const n = cnt.results[0].n;
    if (n > MAX_FAILS) return fail("Demasiados intentos. Espera 15 minutos y vuelve a probar.", 429);
    if (!pass || !(await verifyPassword(env, pass))) {
      const left = MAX_FAILS - n;
      return fail(left > 0 ? `Contraseña incorrecta. Te quedan ${left} intentos.` : "Contraseña incorrecta. Espera 15 minutos.", 401);
    }
    const token = randHex(32);
    await DB.batch([
      DB.prepare("DELETE FROM fails WHERE ip = ?").bind(ip),
      DB.prepare("INSERT INTO ses (h, exp) VALUES (?, ?)").bind(await sha256(token), Date.now() + SESSION_DAYS * 864e5)
    ]);
    return json({ ok: true }, 200, { "set-cookie": sessionCookie(token, url, SESSION_DAYS * 86400) });
  }

  if (path === "salir" && method === "POST") {
    const token = cookieOf(request, "vt");
    if (token) await (await db(env)).prepare("DELETE FROM ses WHERE h = ?").bind(await sha256(token)).run();
    return json({ ok: true }, 200, { "set-cookie": sessionCookie("", url, 0) });
  }

  // ---- a partir de aquí, hace falta haber entrado
  if (!(await isLogged(request, env))) return fail("Tienes que entrar otra vez.", 401);
  const DB = await db(env);

  if (path === "config" && method === "GET") {
    const cfg = await loadConfig(env);
    const at = await kvGet(env, "publishedAt");
    return json({ config: cfg, publishedAt: at ? Number(at) : null });
  }

  if (path === "config" && method === "PUT") {
    const body = await readJson(request, MAX_CONFIG);
    const cfg = R.sanitize(body.config);
    // Fuera las fotos que ya no existen (si no, la página enseñaría una imagen rota).
    const { results: imgRows } = await DB.prepare("SELECT id FROM img").all();
    const exists = new Set((imgRows || []).map((r) => r.id));
    let missing = 0;
    const keep = (id) => { if (id && !exists.has(id)) { missing++; return ""; } return id; };
    cfg.profile.avatar = keep(cfg.profile.avatar);
    cfg.theme.bgImage = keep(cfg.theme.bgImage);
    cfg.feature.image = keep(cfg.feature.image);
    if (cfg.theme.bgType === "image" && !cfg.theme.bgImage) cfg.theme.bgType = "solid";
    cfg.items.forEach((i) => { i.thumb = keep(i.thumb); });
    const now = Date.now();
    await kvSet(env, "config", JSON.stringify(cfg));
    await kvSet(env, "publishedAt", String(now));
    ctx.waitUntil(removeUnused(env, cfg, exists));
    return json({ ok: true, config: cfg, publishedAt: now, missing });
  }

  if (path === "imagen" && method === "POST") {
    const len = parseInt(request.headers.get("content-length") || "0", 10);
    if (len > MAX_IMG) return fail("La imagen pesa demasiado (máximo 1,4 MB).", 413);
    const bytes = new Uint8Array(await request.arrayBuffer());
    if (bytes.length > MAX_IMG) return fail("La imagen pesa demasiado (máximo 1,4 MB).", 413);
    const type = imageType(bytes);
    if (!type) return fail("Ese archivo no es una imagen JPG, PNG, WebP o GIF.", 415);
    const count = (await DB.prepare("SELECT COUNT(*) AS n FROM img").first()).n;
    if (count >= MAX_IMAGES) return fail("Has llegado al máximo de imágenes. Publica para borrar las que ya no usas.", 409);
    const id = randHex(12);
    await DB.prepare("INSERT INTO img (id, type, data, created) VALUES (?, ?, ?, ?)").bind(id, type, b64encode(bytes), Date.now()).run();
    return json({ ok: true, id });
  }

  if (path === "estadisticas" && method === "GET") {
    const days = Math.max(1, Math.min(365, parseInt(url.searchParams.get("dias") || "30", 10) || 30));
    const from = new Date(Date.now() - (days - 1) * 864e5).toISOString().slice(0, 10);
    const { results } = await DB.prepare("SELECT day, k, n FROM stats WHERE day >= ? ORDER BY day").bind(from).all();
    return json({ from, days, rows: results || [] });
  }

  if (path === "contrasena" && method === "POST") {
    const body = await readJson(request, 4096);
    const actual = typeof body.actual === "string" ? body.actual : "";
    const nueva = typeof body.nueva === "string" ? body.nueva : "";
    if (!(await verifyPassword(env, actual))) return fail("La contraseña actual no es correcta.", 401);
    if (nueva.length < 10) return fail("La nueva contraseña tiene que tener al menos 10 caracteres.");
    if (nueva.length > 200) return fail("La nueva contraseña es demasiado larga.");
    const salt = randHex(16);
    const rec = { src: "panel", iter: ITER, salt, hash: await pbkdf2(nueva, salt, ITER) };
    await kvSet(env, "pw", JSON.stringify(rec));
    secretChecked = true;
    // Cierra las demás sesiones abiertas
    const token = cookieOf(request, "vt");
    await DB.prepare("DELETE FROM ses WHERE h != ?").bind(await sha256(token)).run();
    return json({ ok: true });
  }

  if (path === "copia" && method === "GET") {
    const cfg = await loadConfig(env);
    const images = {};
    if (cfg) {
      for (const id of R.usedImages(cfg)) {
        const row = await DB.prepare("SELECT type, data FROM img WHERE id = ?").bind(id).first();
        if (row) images[id] = { type: row.type, data: row.data };
      }
    }
    return json({ app: "vitrina", v: 1, exported: new Date().toISOString(), config: cfg, images }, 200, {
      "content-disposition": `attachment; filename="vitrina-copia-${today()}.json"`
    });
  }

  if (path === "copia" && method === "POST") {
    const body = await readJson(request, MAX_BACKUP);
    if (!body || body.app !== "vitrina" || !body.config) return fail("Ese archivo no es una copia de Vitrina.");
    const cfg = R.sanitize(body.config);
    const imgs = body.images && typeof body.images === "object" ? body.images : {};
    const stmts = [];
    for (const id of R.usedImages(cfg)) {
      const im = imgs[id];
      if (!im || typeof im.data !== "string") continue;
      let bytes;
      try { bytes = b64decode(im.data); } catch { continue; }
      const type = imageType(bytes);
      if (!type || bytes.length > MAX_IMG) continue;
      stmts.push(DB.prepare("INSERT INTO img (id, type, data, created) VALUES (?, ?, ?, ?) ON CONFLICT(id) DO NOTHING").bind(id, type, im.data, Date.now()));
    }
    for (let i = 0; i < stmts.length; i += 20) await DB.batch(stmts.slice(i, i + 20));
    await kvSet(env, "config", JSON.stringify(cfg));
    await kvSet(env, "publishedAt", String(Date.now()));
    return json({ ok: true, config: cfg });
  }

  return fail("No encontrado.", 404);
}

// Borra las imágenes que ya no usa la página (con un día de margen por si deshaces un cambio).
// Una foto que deja de usarse se guarda 7 días más (por si deshaces el cambio o recuperas un borrador).
async function removeUnused(env, cfg, exists) {
  const DB = await db(env);
  const used = new Set(R.usedImages(cfg));
  let since = {};
  try { since = JSON.parse((await kvGet(env, "imgUnused")) || "{}") || {}; } catch { since = {}; }
  const now = Date.now(), next = {}, drop = [];
  for (const id of exists) {
    if (used.has(id)) continue;
    const t = typeof since[id] === "number" ? since[id] : now;
    if (now - t > 7 * 864e5) drop.push(id); else next[id] = t;
  }
  await kvSet(env, "imgUnused", JSON.stringify(next));
  for (let i = 0; i < drop.length; i += 20) {
    await DB.batch(drop.slice(i, i + 20).map((id) => DB.prepare("DELETE FROM img WHERE id = ?").bind(id)));
  }
}

// ---------------------------------------------------------------- entrada
export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);
    const p = url.pathname;
    try {
      if (p.startsWith("/api/")) return await handleApi(request, env, url, ctx);
      if (request.method !== "GET" && request.method !== "HEAD") return new Response("Método no permitido", { status: 405 });
      if (p === "/") return await publicPage(request, env, url, ctx);
      if (p === "/admin" || p === "/admin/") return panelPage(url);
      if (p.startsWith("/i/")) return await serveImage(env, p.slice(3));
      if (p === "/v.js") {
        return new Response(R.HIT_SCRIPT, { headers: { ...BASE_HEADERS, "content-type": "text/javascript; charset=utf-8", "cache-control": "public, max-age=86400" } });
      }
      if (p === "/robots.txt") return new Response("User-agent: *\nDisallow: /admin\nDisallow: /api/\n", { headers: { "content-type": "text/plain" } });
      if (p === "/favicon.ico") {
        const cfg = await loadConfig(env).catch(() => null);
        if (cfg && cfg.profile.avatar) return Response.redirect(url.origin + "/i/" + cfg.profile.avatar, 302);
        return new Response(null, { status: 204 });
      }
      return new Response("No encontrado", { status: 404, headers: { ...BASE_HEADERS, "content-type": "text/plain; charset=utf-8" } });
    } catch (e) {
      if (e instanceof HttpError) return fail(e.message, e.status);
      if (e instanceof SetupError) {
        if (p.startsWith("/api/")) return fail("Falta conectar la base de datos D1 con el nombre DB.", 503);
        return new Response(placeholderPage(), { status: 503, headers: publicHeaders({ "cache-control": "no-store" }) });
      }
      console.error(e);
      return fail("Ha fallado algo en el servidor. Prueba otra vez en un momento.", 500);
    }
  }
};
