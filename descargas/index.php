<?php
/*
 * Vitrina · tu página de enlaces (tipo Linktree) en tu propia web.
 *
 * CÓMO SE INSTALA
 *   1. Escribe tu contraseña abajo, entre las comillas de CONTRASENA_INICIAL.
 *   2. Sube este archivo a una carpeta de tu web, por ejemplo  tuweb.com/enlaces/index.php
 *   3. Entra en  tuweb.com/enlaces/?panel  con esa contraseña.
 *   La página pública es  tuweb.com/enlaces/
 *
 * Necesita PHP 7.4 o más nuevo (lo tiene casi cualquier hosting). Guarda los datos en una
 * carpeta «vitrina-datos» que crea solo, al lado de este archivo.
 */

// =====================  AJUSTES (solo tienes que tocar esto)  =====================
const CONTRASENA_INICIAL = '';   // ← tu contraseña, entre las comillas (mínimo 10 caracteres, sin ' ni \)
// ==================================================================================
// Si la cambias desde el panel, la de aquí deja de valer. Si la olvidas, escribe otra
// aquí (distinta) y vuelve a entrar con ella.

const VT_VERSION = '1.0.0';
const VT_MAX_IMG = 1433600;          // 1,4 MB por imagen (el panel ya las reduce antes)
const VT_MAX_IMAGES = 400;
const VT_MAX_CONFIG = 204800;
const VT_SESSION_DAYS = 30;
const VT_MAX_FAILS = 8;
const VT_FAIL_WINDOW = 900;          // 15 minutos

$VT = json_decode(<<<'VT_DATA'
{"templates":{"papel":{"label":"Papel","font":"dmsans","bgType":"solid","bg":"#f3efe6","bg2":"#e6dfcf","angle":160,"text":"#161513","btnStyle":"fill","btnShape":"pill","btnBg":"#161513","btnText":"#f3efe6"},"noche":{"label":"Noche","font":"inter","bgType":"solid","bg":"#0f1013","bg2":"#1c1e24","angle":160,"text":"#f2f2f0","btnStyle":"outline","btnShape":"rounded","btnBg":"#f2f2f0","btnText":"#0f1013"},"lima":{"label":"Lima","font":"grotesk","bgType":"solid","bg":"#d4f45a","bg2":"#b9e23a","angle":160,"text":"#121212","btnStyle":"shadow","btnShape":"rounded","btnBg":"#ffffff","btnText":"#121212"},"cristal":{"label":"Cristal","font":"outfit","bgType":"gradient","bg":"#2d1f8f","bg2":"#9c2a6b","angle":150,"text":"#ffffff","btnStyle":"glass","btnShape":"pill","btnBg":"#ffffff","btnText":"#ffffff"},"editorial":{"label":"Editorial","font":"fraunces","bgType":"solid","bg":"#f7f3ec","bg2":"#ede6d8","angle":160,"text":"#1b1a17","btnStyle":"line","btnShape":"square","btnBg":"#1b1a17","btnText":"#f7f3ec"},"oceano":{"label":"Océano","font":"inter","bgType":"gradient","bg":"#0b2545","bg2":"#17577f","angle":170,"text":"#eaf2fa","btnStyle":"soft","btnShape":"rounded","btnBg":"#eaf2fa","btnText":"#0b2545"},"chicle":{"label":"Chicle","font":"bricolage","bgType":"solid","bg":"#ffe3ec","bg2":"#ffc9da","angle":160,"text":"#3a1024","btnStyle":"fill","btnShape":"pill","btnBg":"#d6286a","btnText":"#ffffff"},"terminal":{"label":"Terminal","font":"mono","bgType":"solid","bg":"#0a0f0a","bg2":"#122012","angle":160,"text":"#7cff8a","btnStyle":"outline","btnShape":"square","btnBg":"#7cff8a","btnText":"#0a0f0a"},"arcilla":{"label":"Arcilla","font":"syne","bgType":"gradient","bg":"#b34a24","bg2":"#a8431f","angle":200,"text":"#fff6ef","btnStyle":"fill","btnShape":"rounded","btnBg":"#fff6ef","btnText":"#7a2c12"},"museo":{"label":"Museo","font":"playfair","bgType":"solid","bg":"#ffffff","bg2":"#f2f2f2","angle":160,"text":"#111111","btnStyle":"outline","btnShape":"square","btnBg":"#111111","btnText":"#ffffff"}},"fonts":{"sistema":{"g":""},"inter":{"g":"Inter:wght@400;500;600;700"},"dmsans":{"g":"DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700"},"grotesk":{"g":"Space+Grotesk:wght@400;500;700"},"outfit":{"g":"Outfit:wght@400;500;700"},"bricolage":{"g":"Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800"},"syne":{"g":"Syne:wght@400;600;800"},"fraunces":{"g":"Fraunces:opsz,wght@9..144,400;9..144,600;9..144,800"},"playfair":{"g":"Playfair+Display:wght@400;600;800"},"mono":{"g":"JetBrains+Mono:wght@400;500;700"}},"socials":{"instagram":1,"tiktok":1,"youtube":1,"x":1,"threads":1,"facebook":1,"twitch":1,"kick":1,"linkedin":1,"github":1,"bluesky":1,"pinterest":1,"snapchat":1,"spotify":1,"patreon":1,"discord":1,"telegram":1,"whatsapp":1,"email":1,"web":1}}
VT_DATA, true);

// ------------------------------------------------------------------ almacenamiento
function vt_dir() {
  $d = __DIR__ . '/vitrina-datos';
  if (!is_dir($d)) {
    if (!@mkdir($d, 0755, true)) return null;
    @mkdir($d . '/img', 0755, true);
  }
  if (!is_dir($d . '/img')) @mkdir($d . '/img', 0755, true);
  if (!is_file($d . '/.htaccess')) @file_put_contents($d . '/.htaccess', "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\nOptions -Indexes\n");
  if (!is_file($d . '/index.html')) @file_put_contents($d . '/index.html', '');
  if (!is_file($d . '/img/index.html')) @file_put_contents($d . '/img/index.html', '');
  return is_writable($d) ? $d : null;
}
// Cada archivo de datos empieza con una línea PHP que corta la ejecución, para que nadie pueda leerlo desde la web.
const VT_GUARD = "<?php exit; ?>\n";
function vt_read($name, $default = null) {
  $d = vt_dir(); if (!$d) return $default;
  $f = $d . '/' . $name . '.php';
  if (!is_file($f)) return $default;
  // Lectura con bloqueo compartido: espera si alguien está escribiendo ese archivo.
  $h = @fopen($f, 'r'); if (!$h) return $default;
  flock($h, LOCK_SH); $raw = stream_get_contents($h); flock($h, LOCK_UN); fclose($h);
  if ($raw === false || $raw === '') return $default;
  $raw = substr($raw, strlen(VT_GUARD));
  $v = json_decode($raw, true);
  return $v === null ? $default : $v;
}
function vt_write($name, $value) {
  $d = vt_dir(); if (!$d) throw new Exception('No puedo escribir en la carpeta de datos.');
  $f = $d . '/' . $name . '.php';
  $tmp = $f . '.' . bin2hex(random_bytes(4)) . '.tmp';
  if (@file_put_contents($tmp, VT_GUARD . json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) throw new Exception('No puedo guardar los datos.');
  if (!@rename($tmp, $f)) { @unlink($f); @rename($tmp, $f); }
}
// Leer-cambiar-guardar con bloqueo (para contadores y sesiones).
function vt_update($name, $default, $fn) {
  $d = vt_dir(); if (!$d) return null;
  $f = $d . '/' . $name . '.php';
  $h = @fopen($f, 'c+'); if (!$h) return null;
  flock($h, LOCK_EX);
  $raw = stream_get_contents($h);
  $v = $raw ? json_decode(substr($raw, strlen(VT_GUARD)), true) : null;
  if (!is_array($v)) $v = $default;
  $v = $fn($v);
  ftruncate($h, 0); rewind($h);
  fwrite($h, VT_GUARD . json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  fflush($h); flock($h, LOCK_UN); fclose($h);
  return $v;
}

// ------------------------------------------------------------------ limpieza de datos (igual que en el navegador)
function vt_len($s) { return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : count(preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY)); }
function vt_cut($s, $max) {
  if (function_exists('mb_substr')) return mb_substr($s, 0, $max, 'UTF-8');
  $a = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY); return implode('', array_slice($a ?: [], 0, $max));
}
// Los mismos espacios que en el navegador (JS), para que las dos versiones limpien igual.
const VT_WS = '\t\n\x{000B}\f\r \x{00A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}';
function vt_trim($v) { return preg_replace('/^[' . VT_WS . ']+|[' . VT_WS . ']+$/u', '', $v); }
function vt_str($v, $max) {
  if (!is_string($v)) return '';
  if (!preg_match('//u', $v)) return '';
  $v = preg_replace('/[\x00-\x09\x0b-\x1f\x7f\x{2028}\x{2029}]/u', '', $v);
  return vt_cut(vt_trim($v), $max);
}
function vt_list($v) {
  if (!is_array($v)) return [];
  $i = 0; foreach ($v as $k => $_) { if ($k !== $i++) return []; }
  return $v;
}
function vt_one($v, $list, $def) { return in_array($v, $list, true) ? $v : $def; }
function vt_hex($v, $def) { return is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? strtolower($v) : $def; }
function vt_int($v, $min, $max, $def) {
  if (is_string($v) && preg_match('/^-?\d+(\.\d+)?$/', $v)) $v = (float)$v;
  if (!is_int($v) && !is_float($v)) return $def;
  if (is_nan($v) || is_infinite($v)) return $def;
  return (int)max($min, min($max, round($v)));
}
function vt_imgid($v) { return is_string($v) && preg_match('/^[a-f0-9]{24}$/', $v) ? $v : ''; }
function vt_itemid($v) { return is_string($v) && preg_match('/^[a-z0-9]{4,16}$/', $v) ? $v : ''; }
function vt_url($u) {
  if (!is_string($u)) return '';
  if (!preg_match('//u', $u)) return '';
  $u = vt_trim($u);
  if ($u === '' || strlen($u) > 2000 || preg_match('/[' . VT_WS . ']/u', $u)) return '';
  if (preg_match('~^https?://[^"<>\\\\`]+$~i', $u)) {
    $host = preg_replace('~^https?://~i', '', $u);
    $host = preg_split('~[/?#]~', $host)[0];
    $host = preg_replace('~^[^@]*@~', '', $host);
    $host = explode(':', $host)[0];
    return (preg_match('~^[^.]+(\.[^.]+)+$~', $host) || $host === 'localhost') ? $u : '';
  }
  if (preg_match('~^mailto:[^"<>\\\\`@]+@[^"<>\\\\`@]+\.[^"<>\\\\`@?]+(\?[^"<>\\\\`]*)?$~i', $u)) return $u;
  if (preg_match('~^tel:\+?[0-9][0-9\-.()]{2,30}$~i', $u)) return $u;
  return '';
}
function vt_default_theme($name) {
  global $VT;
  $t = isset($VT['templates'][$name]) ? $VT['templates'][$name] : $VT['templates']['papel'];
  return [
    'template' => isset($VT['templates'][$name]) ? $name : 'papel',
    'font' => $t['font'], 'bgType' => $t['bgType'], 'bg' => $t['bg'], 'bg2' => $t['bg2'], 'angle' => $t['angle'], 'bgImage' => '', 'overlay' => 35,
    'text' => $t['text'], 'btnStyle' => $t['btnStyle'], 'btnShape' => $t['btnShape'], 'btnBg' => $t['btnBg'], 'btnText' => $t['btnText'],
  ];
}
function vt_sanitize($c) {
  global $VT;
  if (!is_array($c)) $c = [];
  $p = isset($c['profile']) && is_array($c['profile']) ? $c['profile'] : [];
  $t = isset($c['theme']) && is_array($c['theme']) ? $c['theme'] : [];
  $base = vt_default_theme(vt_one(isset($t['template']) ? $t['template'] : '', array_keys($VT['templates']), 'papel'));
  $seen = [];
  $uniq = function ($id) use (&$seen) { if (!$id || isset($seen[$id])) return ''; $seen[$id] = 1; return $id; };
  $g = function ($a, $k) { return is_array($a) && array_key_exists($k, $a) ? $a[$k] : null; };
  $socials = [];
  foreach (array_slice(isset($c['socials']) ? vt_list($c['socials']) : [], 0, 20) as $s) {
    $s = is_array($s) ? $s : [];
    $id = $uniq(vt_itemid($g($s, 'id')));
    $type = vt_one($g($s, 'type'), array_keys($VT['socials']), '');
    if ($id && $type) $socials[] = ['id' => $id, 'type' => $type, 'url' => vt_url($g($s, 'url'))];
  }
  $items = [];
  foreach (array_slice(isset($c['items']) ? vt_list($c['items']) : [], 0, 100) as $i) {
    $i = is_array($i) ? $i : [];
    $kind = vt_one($g($i, 'kind'), ['link', 'header'], 'link');
    $id = $uniq(vt_itemid($g($i, 'id')));
    if (!$id) continue;
    $items[] = [
      'id' => $id, 'kind' => $kind, 'title' => vt_str($g($i, 'title'), 100),
      'url' => $kind === 'link' ? vt_url($g($i, 'url')) : '',
      'sub' => $kind === 'link' ? vt_str($g($i, 'sub'), 140) : '',
      'thumb' => $kind === 'link' ? vt_imgid($g($i, 'thumb')) : '',
      'hidden' => $g($i, 'hidden') === true,
      'hl' => $kind === 'link' && $g($i, 'hl') === true,
    ];
  }
  $seo = isset($c['seo']) && is_array($c['seo']) ? $c['seo'] : [];
  $o = [
    'v' => 1,
    'profile' => ['name' => vt_str($g($p, 'name'), 60), 'bio' => vt_str($g($p, 'bio'), 300), 'avatar' => vt_imgid($g($p, 'avatar')), 'shape' => vt_one($g($p, 'shape'), ['circle', 'rounded', 'square'], 'circle')],
    'socials' => $socials,
    'socialsPos' => vt_one($g($c, 'socialsPos'), ['top', 'bottom'], 'top'),
    'items' => $items,
    'theme' => [
      'template' => $base['template'],
      'font' => vt_one($g($t, 'font'), array_keys($VT['fonts']), $base['font']),
      'bgType' => vt_one($g($t, 'bgType'), ['solid', 'gradient', 'image'], $base['bgType']),
      'bg' => vt_hex($g($t, 'bg'), $base['bg']), 'bg2' => vt_hex($g($t, 'bg2'), $base['bg2']),
      'angle' => vt_int($g($t, 'angle'), 0, 360, $base['angle']),
      'bgImage' => vt_imgid($g($t, 'bgImage')),
      'overlay' => vt_int($g($t, 'overlay'), 0, 90, 35),
      'text' => vt_hex($g($t, 'text'), $base['text']),
      'btnStyle' => vt_one($g($t, 'btnStyle'), ['fill', 'outline', 'shadow', 'glass', 'soft', 'line'], $base['btnStyle']),
      'btnShape' => vt_one($g($t, 'btnShape'), ['pill', 'rounded', 'square'], $base['btnShape']),
      'btnBg' => vt_hex($g($t, 'btnBg'), $base['btnBg']), 'btnText' => vt_hex($g($t, 'btnText'), $base['btnText']),
    ],
    'seo' => ['title' => vt_str($g($seo, 'title'), 70), 'desc' => vt_str($g($seo, 'desc'), 160)],
  ];
  if ($o['theme']['bgType'] === 'image' && !$o['theme']['bgImage']) $o['theme']['bgType'] = 'solid';
  return $o;
}
function vt_used_images($c) {
  $out = [];
  if ($c['profile']['avatar']) $out[] = $c['profile']['avatar'];
  if ($c['theme']['bgImage']) $out[] = $c['theme']['bgImage'];
  foreach ($c['items'] as $i) if ($i['thumb']) $out[] = $i['thumb'];
  return $out;
}
function vt_config() { $c = vt_read('config'); return is_array($c) ? vt_sanitize($c) : null; }

// ------------------------------------------------------------------ utilidades HTTP
function vt_h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function vt_https() {
  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
}
function vt_origin() { return (vt_https() ? 'https' : 'http') . '://' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'); }
function vt_base_path() {
  $p = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php';
  $d = rtrim(str_replace('\\', '/', dirname($p)), '/');
  return $d . '/';
}
function vt_page_url() { return vt_origin() . vt_base_path(); }
function vt_common_headers() {
  header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
function vt_json($obj, $status = 200) {
  http_response_code($status);
  vt_common_headers();
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($obj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function vt_fail($msg, $status = 400) { vt_json(['error' => $msg], $status); }
function vt_body($max) {
  $len = isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0;
  if ($len > $max) vt_fail('Es demasiado grande.', 413);
  $raw = file_get_contents('php://input', false, null, 0, $max + 1);
  if ($raw === false) $raw = '';
  if (strlen($raw) > $max) vt_fail('Es demasiado grande.', 413);
  return $raw;
}
function vt_json_body($max) {
  $d = json_decode(vt_body($max), true);
  if (!is_array($d)) vt_fail('Los datos no son válidos.');
  return $d;
}
function vt_image_type($b) {
  if (strlen($b) < 12) return '';
  if (substr($b, 0, 4) === "\x89PNG") return 'image/png';
  if (substr($b, 0, 3) === "\xFF\xD8\xFF") return 'image/jpeg';
  if (substr($b, 0, 4) === 'GIF8') return 'image/gif';
  if (substr($b, 0, 4) === 'RIFF' && substr($b, 8, 4) === 'WEBP') return 'image/webp';
  return '';
}

// ------------------------------------------------------------------ sesión y contraseña
function vt_cookie_name() { return 'vt_' . substr(sha1(vt_base_path()), 0, 8); }
function vt_set_cookie($token, $maxAge) {
  $parts = [vt_cookie_name() . '=' . $token, 'Path=' . vt_base_path(), 'HttpOnly', 'SameSite=Strict', 'Max-Age=' . $maxAge];
  if (vt_https()) $parts[] = 'Secure';
  header('Set-Cookie: ' . implode('; ', $parts), false);
}
function vt_token() {
  $n = vt_cookie_name();
  return isset($_COOKIE[$n]) && preg_match('/^[a-f0-9]{64}$/', $_COOKIE[$n]) ? $_COOKIE[$n] : '';
}
function vt_logged() {
  $t = vt_token(); if (!$t) return false;
  $s = vt_read('sesiones', []);
  $h = hash('sha256', $t);
  return isset($s[$h]) && $s[$h] > time();
}
// La IP solo se usa (con sal aleatoria de esta instalación) para frenar contraseñas fallidas.
function vt_ip() {
  $salt = vt_read('sal');
  if (!is_string($salt) || $salt === '') { $salt = bin2hex(random_bytes(16)); vt_write('sal', $salt); }
  return substr(hash('sha256', $salt . ':' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')), 0, 32);
}
function vt_password_state() {
  static $checked = false;
  $secret = CONTRASENA_INICIAL;
  if ($secret !== '' && !$checked) {
    // Si has escrito otra contraseña en el archivo, vuelve a mandar esa y se cierran todas las sesiones.
    $fp = vt_read('huella');
    if (!is_string($fp) || $fp === '') vt_write('huella', password_hash($secret, PASSWORD_DEFAULT));
    elseif (!password_verify($secret, $fp)) {
      @unlink(vt_dir() . '/clave.php');
      vt_write('sesiones', []);
      vt_write('huella', password_hash($secret, PASSWORD_DEFAULT));
    }
    $checked = true;
  }
  $rec = vt_read('clave');
  if (is_array($rec) && isset($rec['src']) && $rec['src'] === 'panel') return ['mode' => 'panel', 'rec' => $rec];
  if ($secret !== '' && vt_len($secret) < 10) return ['mode' => 'weak'];
  if ($secret !== '') return ['mode' => 'secret'];
  return ['mode' => 'none'];
}
function vt_verify($pass) {
  $st = vt_password_state();
  if ($st['mode'] === 'secret') return hash_equals(hash('sha256', CONTRASENA_INICIAL), hash('sha256', $pass));
  if ($st['mode'] === 'panel') return password_verify($pass, $st['rec']['hash']);
  return false;
}

// ------------------------------------------------------------------ estadísticas (sin cookies ni IPs)
function vt_is_bot() {
  $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
  $purpose = strtolower((isset($_SERVER['HTTP_SEC_PURPOSE']) ? $_SERVER['HTTP_SEC_PURPOSE'] : '') . (isset($_SERVER['HTTP_PURPOSE']) ? $_SERVER['HTTP_PURPOSE'] : ''));
  return !$ua || strpos($purpose, 'prefetch') !== false
    || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|slack|discord|embedly|headless|lighthouse|curl|wget|python|go-http/i', $ua);
}
function vt_count($key) {
  $day = gmdate('Y-m-d');
  vt_update('estadisticas', [], function ($st) use ($day, $key) {
    if (!isset($st[$day])) $st[$day] = [];
    $st[$day][$key] = (isset($st[$day][$key]) ? $st[$day][$key] : 0) + 1;
    if (count($st) > 400) { ksort($st); $st = array_slice($st, -400, null, true); }
    return $st;
  });
}

// ------------------------------------------------------------------ API
function vt_api($path) {
  $m = $_SERVER['REQUEST_METHOD'];

  if ($path === 'hit' && $m === 'POST') {
    if (vt_is_bot()) { http_response_code(204); exit; }
    $d = json_decode((string)file_get_contents('php://input', false, null, 0, 512), true);
    $key = '';
    if (is_array($d) && isset($d['t']) && ($d['t'] === 'c' || $d['t'] === 's') && isset($d['id']) && is_string($d['id'])) {
      $cfg = vt_config();
      if ($cfg && $d['t'] === 'c') foreach ($cfg['items'] as $i) if ($i['id'] === $d['id'] && $i['kind'] === 'link' && !$i['hidden']) $key = 'c:' . $d['id'];
      if ($cfg && $d['t'] === 's') foreach ($cfg['socials'] as $s) if ($s['type'] === $d['id']) $key = 's:' . $d['id'];
    }
    if ($key) vt_count($key);
    http_response_code(204); exit;
  }

  $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
  if ($m !== 'GET' && $origin && $origin !== vt_origin()) vt_fail('Origen no permitido.', 403);
  if ($m !== 'GET' && (!isset($_SERVER['HTTP_X_VITRINA']) || $_SERVER['HTTP_X_VITRINA'] !== '1')) vt_fail('Petición no válida.', 403);

  if ($path === 'estado' && $m === 'GET') {
    if (!vt_dir()) vt_json(['setup' => 'nodb', 'logged' => false, 'version' => VT_VERSION]);
    $st = vt_password_state();
    if ($st['mode'] === 'none') vt_json(['setup' => 'nopass', 'logged' => false, 'version' => VT_VERSION]);
    if ($st['mode'] === 'weak') vt_json(['setup' => 'weak', 'logged' => false, 'version' => VT_VERSION]);
    vt_json(['setup' => null, 'logged' => vt_logged(), 'version' => VT_VERSION]);
  }
  if (!vt_dir()) vt_fail('No puedo crear la carpeta de datos junto a index.php.', 503);

  if ($path === 'entrar' && $m === 'POST') {
    $ip = vt_ip(); $now = time();
    $b = vt_json_body(2048);
    $pass = isset($b['password']) && is_string($b['password']) ? $b['password'] : '';
    $mode = vt_password_state()['mode'];
    if ($mode === 'none') vt_fail('Falta la contraseña inicial: escríbela en CONTRASENA_INICIAL dentro de index.php.', 409);
    if ($mode === 'weak') vt_fail('La contraseña de index.php es demasiado corta. Pon una de 10 caracteres o más.', 409);
    // Se apunta el intento y se cuenta en el mismo paso (con bloqueo), antes de comprobar la contraseña.
    $n = 0;
    vt_update('fallos', [], function ($f) use ($ip, $now, &$n) {
      foreach ($f as $k => $list) { $f[$k] = array_values(array_filter($list, function ($t) use ($now) { return $t > $now - VT_FAIL_WINDOW; })); if (!$f[$k]) unset($f[$k]); }
      $f[$ip][] = $now; $n = count($f[$ip]); return $f;
    });
    if ($n > VT_MAX_FAILS) vt_fail('Demasiados intentos. Espera 15 minutos y vuelve a probar.', 429);
    if ($pass === '' || !vt_verify($pass)) {
      $left = VT_MAX_FAILS - $n;
      vt_fail($left > 0 ? "Contraseña incorrecta. Te quedan $left intentos." : 'Contraseña incorrecta. Espera 15 minutos.', 401);
    }
    $token = bin2hex(random_bytes(32));
    vt_update('fallos', [], function ($f) use ($ip) { unset($f[$ip]); return $f; });
    vt_update('sesiones', [], function ($s) use ($token, $now) {
      foreach ($s as $k => $exp) if ($exp < $now) unset($s[$k]);
      $s[hash('sha256', $token)] = $now + VT_SESSION_DAYS * 86400; return $s;
    });
    vt_set_cookie($token, VT_SESSION_DAYS * 86400);
    vt_json(['ok' => true]);
  }

  if ($path === 'salir' && $m === 'POST') {
    $t = vt_token();
    if ($t) vt_update('sesiones', [], function ($s) use ($t) { unset($s[hash('sha256', $t)]); return $s; });
    vt_set_cookie('', 0);
    vt_json(['ok' => true]);
  }

  if (!vt_logged()) vt_fail('Tienes que entrar otra vez.', 401);

  if ($path === 'config' && $m === 'GET') {
    $at = vt_read('publicado');
    vt_json(['config' => vt_config(), 'publishedAt' => $at ? (int)$at : null]);
  }
  if ($path === 'config' && $m === 'PUT') {
    $b = vt_json_body(VT_MAX_CONFIG);
    $cfg = vt_sanitize(isset($b['config']) ? $b['config'] : null);
    $missing = 0;
    $keep = function ($id) use (&$missing) { if ($id && !is_file(vt_dir() . '/img/' . $id . '.img')) { $missing++; return ''; } return $id; };
    $cfg['profile']['avatar'] = $keep($cfg['profile']['avatar']);
    $cfg['theme']['bgImage'] = $keep($cfg['theme']['bgImage']);
    if ($cfg['theme']['bgType'] === 'image' && !$cfg['theme']['bgImage']) $cfg['theme']['bgType'] = 'solid';
    foreach ($cfg['items'] as $k => $i) $cfg['items'][$k]['thumb'] = $keep($i['thumb']);
    $now = (int)round(microtime(true) * 1000);
    vt_write('config', $cfg);
    vt_write('publicado', $now);
    vt_remove_unused($cfg);
    vt_json(['ok' => true, 'config' => $cfg, 'publishedAt' => $now, 'missing' => $missing]);
  }
  if ($path === 'imagen' && $m === 'POST') {
    $bytes = vt_body(VT_MAX_IMG);
    $type = vt_image_type($bytes);
    if (!$type) vt_fail('Ese archivo no es una imagen JPG, PNG, WebP o GIF.', 415);
    $files = glob(vt_dir() . '/img/*.img') ?: [];
    if (count($files) >= VT_MAX_IMAGES) vt_fail('Has llegado al máximo de imágenes. Publica para borrar las que ya no usas.', 409);
    $id = bin2hex(random_bytes(12));
    if (@file_put_contents(vt_dir() . '/img/' . $id . '.img', $bytes, LOCK_EX) === false) vt_fail('No he podido guardar la imagen.', 500);
    vt_json(['ok' => true, 'id' => $id]);
  }
  if ($path === 'estadisticas' && $m === 'GET') {
    $days = max(1, min(365, (int)(isset($_GET['dias']) ? $_GET['dias'] : 30) ?: 30));
    $from = gmdate('Y-m-d', time() - ($days - 1) * 86400);
    $rows = [];
    foreach (vt_read('estadisticas', []) as $day => $ks) {
      if ($day < $from) continue;
      foreach ($ks as $k => $n) $rows[] = ['day' => $day, 'k' => $k, 'n' => $n];
    }
    vt_json(['from' => $from, 'days' => $days, 'rows' => $rows]);
  }
  if ($path === 'contrasena' && $m === 'POST') {
    $b = vt_json_body(4096);
    $actual = isset($b['actual']) && is_string($b['actual']) ? $b['actual'] : '';
    $nueva = isset($b['nueva']) && is_string($b['nueva']) ? $b['nueva'] : '';
    if (!vt_verify($actual)) vt_fail('La contraseña actual no es correcta.', 401);
    if (strlen($nueva) < 10) vt_fail('La nueva contraseña tiene que tener al menos 10 caracteres.');
    if (strlen($nueva) > 200) vt_fail('La nueva contraseña es demasiado larga.');
    vt_write('clave', ['src' => 'panel', 'hash' => password_hash($nueva, PASSWORD_DEFAULT)]);
    $keep = hash('sha256', vt_token());
    vt_update('sesiones', [], function ($s) use ($keep) { return isset($s[$keep]) ? [$keep => $s[$keep]] : []; });
    vt_json(['ok' => true]);
  }
  if ($path === 'copia' && $m === 'GET') {
    $cfg = vt_config(); $images = [];
    if ($cfg) foreach (vt_used_images($cfg) as $id) {
      $f = vt_dir() . '/img/' . $id . '.img';
      if (is_file($f)) { $b = file_get_contents($f); $images[$id] = ['type' => vt_image_type($b), 'data' => base64_encode($b)]; }
    }
    header('Content-Disposition: attachment; filename="vitrina-copia-' . gmdate('Y-m-d') . '.json"');
    vt_json(['app' => 'vitrina', 'v' => 1, 'exported' => gmdate('c'), 'config' => $cfg, 'images' => $images]);
  }
  if ($path === 'copia' && $m === 'POST') {
    $b = vt_json_body(40 * 1024 * 1024);
    if (!isset($b['app']) || $b['app'] !== 'vitrina' || empty($b['config'])) vt_fail('Ese archivo no es una copia de Vitrina.');
    $cfg = vt_sanitize($b['config']);
    $imgs = isset($b['images']) && is_array($b['images']) ? $b['images'] : [];
    foreach (vt_used_images($cfg) as $id) {
      if (!isset($imgs[$id]['data']) || !is_string($imgs[$id]['data'])) continue;
      $bytes = base64_decode($imgs[$id]['data'], true);
      if ($bytes === false || strlen($bytes) > VT_MAX_IMG || !vt_image_type($bytes)) continue;
      $f = vt_dir() . '/img/' . $id . '.img';
      if (!is_file($f)) @file_put_contents($f, $bytes, LOCK_EX);
    }
    vt_write('config', $cfg);
    vt_write('publicado', (int)round(microtime(true) * 1000));
    vt_json(['ok' => true, 'config' => $cfg]);
  }
  vt_fail('No encontrado.', 404);
}
// Una foto que deja de usarse se guarda 7 días más (por si deshaces el cambio o recuperas un borrador).
function vt_remove_unused($cfg) {
  $used = array_flip(vt_used_images($cfg));
  $since = vt_read('sin-usar', []);
  $next = []; $now = time();
  foreach (glob(vt_dir() . '/img/*.img') ?: [] as $f) {
    $id = basename($f, '.img');
    if (isset($used[$id])) continue;
    $t = isset($since[$id]) && is_int($since[$id]) ? $since[$id] : $now;
    if ($now - $t > 7 * 86400) @unlink($f); else $next[$id] = $t;
  }
  vt_write('sin-usar', $next);
}

// ------------------------------------------------------------------ páginas
function vt_render_js() {
  return <<<'VT_RENDER'
var __name=function(f){return f};var VitrinaR=(function vitrinaRenderer() {
  "use strict";

  var ICONS = {"bluesky":"M5.202 2.857C7.954 4.922 10.913 9.11 12 11.358c1.087-2.247 4.046-6.436 6.798-8.501C20.783 1.366 24 .213 24 3.883c0 .732-.42 6.156-.667 7.037-.856 3.061-3.978 3.842-6.755 3.37 4.854.826 6.089 3.562 3.422 6.299-5.065 5.196-7.28-1.304-7.847-2.97-.104-.305-.152-.448-.153-.327 0-.121-.05.022-.153.327-.568 1.666-2.782 8.166-7.847 2.97-2.667-2.737-1.432-5.473 3.422-6.3-2.777.473-5.899-.308-6.755-3.369C.42 10.04 0 4.615 0 3.883c0-3.67 3.217-2.517 5.202-1.026","discord":"M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z","facebook":"M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z","github":"M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12","instagram":"M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077","kick":"M1.333 0h8v5.333H12V2.667h2.667V0h8v8H20v2.667h-2.667v2.666H20V16h2.667v8h-8v-2.667H12v-2.666H9.333V24h-8Z","patreon":"M22.957 7.21c-.004-3.064-2.391-5.576-5.191-6.482-3.478-1.125-8.064-.962-11.384.604C2.357 3.231 1.093 7.391 1.046 11.54c-.039 3.411.302 12.396 5.369 12.46 3.765.047 4.326-4.804 6.068-7.141 1.24-1.662 2.836-2.132 4.801-2.618 3.376-.836 5.678-3.501 5.673-7.031Z","pinterest":"M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z","snapchat":"M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z","spotify":"M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z","telegram":"M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z","threads":"M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 0 1 3.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.308-.883-2.359-.89h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044c3.194.02 5.097 1.975 5.287 5.388.108.046.216.094.321.142 1.49.7 2.58 1.761 3.154 3.07.797 1.82.871 4.79-1.548 7.158-1.85 1.81-4.094 2.628-7.277 2.65Zm1.003-11.69c-.242 0-.487.007-.739.021-1.836.103-2.98.946-2.916 2.143.067 1.256 1.452 1.839 2.784 1.767 1.224-.065 2.818-.543 3.086-3.71a10.5 10.5 0 0 0-2.215-.221z","tiktok":"M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z","twitch":"M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z","whatsapp":"M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z","x":"M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z","youtube":"M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z","linkedin":"M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z","email":"M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm1 2.3V17h16V7.3l-8 5.6-8-5.6zM5.6 7l6.4 4.5L18.4 7H5.6z","web":"M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.9 6h-2.9a15.7 15.7 0 0 0-1.3-3.6A8 8 0 0 1 18.9 8zM12 4.1c.8 1.1 1.5 2.4 1.9 3.9h-3.8c.4-1.5 1.1-2.8 1.9-3.9zM4.3 14a8.2 8.2 0 0 1 0-4h3.3a16.5 16.5 0 0 0 0 4H4.3zm.8 2h2.9c.3 1.3.8 2.5 1.3 3.6A8 8 0 0 1 5.1 16zM8 8H5.1a8 8 0 0 1 4.2-3.6C8.8 5.5 8.3 6.7 8 8zm4 11.9c-.8-1.1-1.5-2.4-1.9-3.9h3.8c-.4 1.5-1.1 2.8-1.9 3.9zM14.3 14H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4zm.4 5.6c.5-1.1 1-2.3 1.3-3.6h2.9a8 8 0 0 1-4.2 3.6zM16.4 14a16.5 16.5 0 0 0 0-4h3.3a8.2 8.2 0 0 1 0 4h-3.3z"};

  // Redes: etiqueta, y cómo convertir lo que escribe la persona (@usuario, número…) en una URL.
  var SOCIALS = {
    instagram: { label: "Instagram", base: "https://instagram.com/", hint: "@usuario" },
    tiktok: { label: "TikTok", base: "https://www.tiktok.com/@", hint: "@usuario" },
    youtube: { label: "YouTube", base: "https://www.youtube.com/@", hint: "@canal" },
    x: { label: "X", base: "https://x.com/", hint: "@usuario" },
    threads: { label: "Threads", base: "https://www.threads.net/@", hint: "@usuario" },
    facebook: { label: "Facebook", base: "https://facebook.com/", hint: "usuario o enlace" },
    twitch: { label: "Twitch", base: "https://twitch.tv/", hint: "canal" },
    kick: { label: "Kick", base: "https://kick.com/", hint: "canal" },
    linkedin: { label: "LinkedIn", base: "https://www.linkedin.com/in/", hint: "usuario o enlace" },
    github: { label: "GitHub", base: "https://github.com/", hint: "usuario" },
    bluesky: { label: "Bluesky", base: "https://bsky.app/profile/", hint: "usuario.bsky.social" },
    pinterest: { label: "Pinterest", base: "https://pinterest.com/", hint: "usuario" },
    snapchat: { label: "Snapchat", base: "https://www.snapchat.com/add/", hint: "usuario" },
    spotify: { label: "Spotify", base: "", hint: "enlace de tu perfil o lista" },
    patreon: { label: "Patreon", base: "https://www.patreon.com/", hint: "usuario" },
    discord: { label: "Discord", base: "https://discord.gg/", hint: "código de invitación" },
    telegram: { label: "Telegram", base: "https://t.me/", hint: "@usuario o canal" },
    whatsapp: { label: "WhatsApp", base: "https://wa.me/", hint: "número con prefijo, p. ej. 34600111222" },
    email: { label: "Correo", base: "mailto:", hint: "tu@correo.com" },
    web: { label: "Web", base: "https://", hint: "tuweb.com" }
  };

  var FONTS = {
    sistema: { label: "Sistema", css: "system-ui,-apple-system,'Segoe UI',Roboto,sans-serif", g: "" },
    inter: { label: "Inter", css: "'Inter',system-ui,sans-serif", g: "Inter:wght@400;500;600;700" },
    dmsans: { label: "DM Sans", css: "'DM Sans',system-ui,sans-serif", g: "DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,700" },
    grotesk: { label: "Space Grotesk", css: "'Space Grotesk',system-ui,sans-serif", g: "Space+Grotesk:wght@400;500;700" },
    outfit: { label: "Outfit", css: "'Outfit',system-ui,sans-serif", g: "Outfit:wght@400;500;700" },
    bricolage: { label: "Bricolage", css: "'Bricolage Grotesque',system-ui,sans-serif", g: "Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800" },
    syne: { label: "Syne", css: "'Syne',system-ui,sans-serif", g: "Syne:wght@400;600;800" },
    fraunces: { label: "Fraunces", css: "'Fraunces',Georgia,serif", g: "Fraunces:opsz,wght@9..144,400;9..144,600;9..144,800" },
    playfair: { label: "Playfair", css: "'Playfair Display',Georgia,serif", g: "Playfair+Display:wght@400;600;800" },
    mono: { label: "JetBrains Mono", css: "'JetBrains Mono',ui-monospace,monospace", g: "JetBrains+Mono:wght@400;500;700" }
  };

  // Plantillas: un tema completo cada una. Al elegir una se copian todos sus valores
  // y luego la persona puede retocar lo que quiera.
  var TEMPLATES = {
    papel: { label: "Papel", font: "dmsans", bgType: "solid", bg: "#f3efe6", bg2: "#e6dfcf", angle: 160, text: "#161513", btnStyle: "fill", btnShape: "pill", btnBg: "#161513", btnText: "#f3efe6" },
    noche: { label: "Noche", font: "inter", bgType: "solid", bg: "#0f1013", bg2: "#1c1e24", angle: 160, text: "#f2f2f0", btnStyle: "outline", btnShape: "rounded", btnBg: "#f2f2f0", btnText: "#0f1013" },
    lima: { label: "Lima", font: "grotesk", bgType: "solid", bg: "#d4f45a", bg2: "#b9e23a", angle: 160, text: "#121212", btnStyle: "shadow", btnShape: "rounded", btnBg: "#ffffff", btnText: "#121212" },
    cristal: { label: "Cristal", font: "outfit", bgType: "gradient", bg: "#2d1f8f", bg2: "#9c2a6b", angle: 150, text: "#ffffff", btnStyle: "glass", btnShape: "pill", btnBg: "#ffffff", btnText: "#ffffff" },
    editorial: { label: "Editorial", font: "fraunces", bgType: "solid", bg: "#f7f3ec", bg2: "#ede6d8", angle: 160, text: "#1b1a17", btnStyle: "line", btnShape: "square", btnBg: "#1b1a17", btnText: "#f7f3ec" },
    oceano: { label: "Océano", font: "inter", bgType: "gradient", bg: "#0b2545", bg2: "#17577f", angle: 170, text: "#eaf2fa", btnStyle: "soft", btnShape: "rounded", btnBg: "#eaf2fa", btnText: "#0b2545" },
    chicle: { label: "Chicle", font: "bricolage", bgType: "solid", bg: "#ffe3ec", bg2: "#ffc9da", angle: 160, text: "#3a1024", btnStyle: "fill", btnShape: "pill", btnBg: "#d6286a", btnText: "#ffffff" },
    terminal: { label: "Terminal", font: "mono", bgType: "solid", bg: "#0a0f0a", bg2: "#122012", angle: 160, text: "#7cff8a", btnStyle: "outline", btnShape: "square", btnBg: "#7cff8a", btnText: "#0a0f0a" },
    arcilla: { label: "Arcilla", font: "syne", bgType: "gradient", bg: "#b34a24", bg2: "#a8431f", angle: 200, text: "#fff6ef", btnStyle: "fill", btnShape: "rounded", btnBg: "#fff6ef", btnText: "#7a2c12" },
    museo: { label: "Museo", font: "playfair", bgType: "solid", bg: "#ffffff", bg2: "#f2f2f2", angle: 160, text: "#111111", btnStyle: "outline", btnShape: "square", btnBg: "#111111", btnText: "#ffffff" }
  };

  var BTN_STYLES = ["fill", "outline", "shadow", "glass", "soft", "line"];
  var BTN_SHAPES = ["pill", "rounded", "square"];
  var BG_TYPES = ["solid", "gradient", "image"];
  var SHAPES = ["circle", "rounded", "square"];

  // ---------- utilidades ----------
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  function str(v, max) {
    if (typeof v !== "string") return "";
    // fuera caracteres de control (salvo salto de línea) y espacios sobrantes
    v = v.replace(/[\x00-\x09\x0b-\x1f\x7f\u2028\u2029]/g, "").replace(WS_EDGE, "");
    // se corta por caracteres (no por mitades de emoji), igual que mb_substr en PHP
    var a = Array.from(v);
    return a.length > max ? a.slice(0, max).join("") : v;
  }
  function oneOf(v, list, def) { return list.indexOf(v) >= 0 ? v : def; }
  function hex(v, def) { return typeof v === "string" && /^#[0-9a-fA-F]{6}$/.test(v) ? v.toLowerCase() : def; }
  function int(v, min, max, def) {
    if (typeof v === "string" && /^-?\d+(\.\d+)?$/.test(v)) v = Number(v);
    if (typeof v !== "number" || !isFinite(v)) return def;
    return Math.max(min, Math.min(max, Math.round(v)));
  }
  // Espacios: los mismos en JS y en PHP (para que las dos versiones limpien igual)
  var WS = "\t\n\v\f\r \u00a0\u1680\u2000-\u200a\u2028\u2029\u202f\u205f\u3000\ufeff";
  var WS_ANY = new RegExp("[" + WS + "]");
  var WS_EDGE = new RegExp("^[" + WS + "]+|[" + WS + "]+$", "g");
  function imgId(v) { return typeof v === "string" && /^[a-f0-9]{24}$/.test(v) ? v : ""; }
  function itemId(v) { return typeof v === "string" && /^[a-z0-9]{4,16}$/.test(v) ? v : ""; }
  function arr(v) { return Array.isArray(v) ? v : []; }

  // Solo se aceptan enlaces http(s), correo y teléfono. Nada de javascript:, data:, etc.
  function safeUrl(u) {
    if (typeof u !== "string") return "";
    u = u.replace(WS_EDGE, "");
    if (!u || u.length > 2000 || WS_ANY.test(u)) return "";
    if (/^https?:\/\/[^"<>\\`]+$/i.test(u)) {
      var host = u.replace(/^https?:\/\//i, "").split(/[\/?#]/)[0].replace(/^[^@]*@/, "").split(":")[0];
      return /^[^.]+(\.[^.]+)+$/.test(host) || host === "localhost" ? u : "";
    }
    if (/^mailto:[^"<>\\`@]+@[^"<>\\`@]+\.[^"<>\\`@?]+(\?[^"<>\\`]*)?$/i.test(u)) return u;
    if (/^tel:\+?[0-9][0-9\-.()]{2,30}$/i.test(u)) return u;
    return "";
  }

  // Convierte «@usuario», «instagram.com/usuario», un número de WhatsApp… en una URL completa.
  function socialUrl(type, input) {
    var s = SOCIALS[type];
    var v = String(input || "").trim();
    if (!s || !v) return "";
    if (type === "email") {
      v = v.replace(/^mailto:/i, "");
      return safeUrl("mailto:" + v);
    }
    if (type === "whatsapp" && /^[+\d\s\-().]+$/.test(v)) {
      return safeUrl("https://wa.me/" + v.replace(/[^\d]/g, ""));
    }
    if (/^https?:\/\//i.test(v)) return safeUrl(v);
    if (/^[\w-]+(\.[\w-]+)+(\/|$)/.test(v) && type !== "bluesky") return safeUrl("https://" + v);
    if (type === "web") return safeUrl("https://" + v);
    if (!s.base) return "";
    v = v.replace(/^@/, "");
    if (!/^[\w.\-]+$/.test(v)) return "";
    return safeUrl(s.base + v);
  }

  // Lo que escribe la persona en «Dirección» de un enlace: si no pone https://, se lo ponemos.
  function normalizeUrl(input) {
    var v = String(input || "").trim();
    if (!v) return "";
    if (/^(https?:|mailto:|tel:)/i.test(v)) return v;
    if (/^\+?[\d\s\-().]{6,}$/.test(v)) return "tel:" + v.replace(/[\s]/g, "");
    if (/^@|[\s,]/.test(v)) return v; // «@usuario» o con espacios: no es una dirección, se deja como está
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return "mailto:" + v;
    return "https://" + v.replace(/^\/+/, "");
  }

  function defaultTheme(name) {
    var t = TEMPLATES[name] || TEMPLATES.papel;
    return {
      template: TEMPLATES[name] ? name : "papel",
      font: t.font, bgType: t.bgType, bg: t.bg, bg2: t.bg2, angle: t.angle, bgImage: "", overlay: 35,
      text: t.text, btnStyle: t.btnStyle, btnShape: t.btnShape, btnBg: t.btnBg, btnText: t.btnText
    };
  }

  function defaultConfig() {
    return {
      v: 1,
      profile: { name: "", bio: "", avatar: "", shape: "circle" },
      socials: [],
      socialsPos: "top",
      items: [],
      theme: defaultTheme("papel"),
      seo: { title: "", desc: "" }
    };
  }

  // Limpia una configuración que llega de fuera: solo pasan los campos conocidos y con valores válidos.
  function sanitize(c) {
    c = c && typeof c === "object" ? c : {};
    var d = defaultConfig();
    var p = c.profile && typeof c.profile === "object" ? c.profile : {};
    var t = c.theme && typeof c.theme === "object" ? c.theme : {};
    var base = defaultTheme(oneOf(t.template, Object.keys(TEMPLATES), "papel"));
    var seen = Object.create(null);
    function uniq(id) { if (!id || seen[id]) return ""; seen[id] = 1; return id; }
    var o = {
      v: 1,
      profile: {
        name: str(p.name, 60),
        bio: str(p.bio, 300),
        avatar: imgId(p.avatar),
        shape: oneOf(p.shape, SHAPES, "circle")
      },
      socials: arr(c.socials).slice(0, 20).map(function (s) {
        s = s && typeof s === "object" ? s : {};
        return { id: uniq(itemId(s.id)), type: oneOf(s.type, Object.keys(SOCIALS), ""), url: safeUrl(s.url) };
      }).filter(function (s) { return s.id && s.type; }),
      socialsPos: oneOf(c.socialsPos, ["top", "bottom"], "top"),
      items: arr(c.items).slice(0, 100).map(function (i) {
        i = i && typeof i === "object" ? i : {};
        var kind = oneOf(i.kind, ["link", "header"], "link");
        return {
          id: uniq(itemId(i.id)), kind: kind,
          title: str(i.title, 100),
          url: kind === "link" ? safeUrl(i.url) : "",
          sub: kind === "link" ? str(i.sub, 140) : "",
          thumb: kind === "link" ? imgId(i.thumb) : "",
          hidden: i.hidden === true,
          hl: kind === "link" && i.hl === true
        };
      }).filter(function (i) { return i.id; }),
      theme: {
        template: base.template,
        font: oneOf(t.font, Object.keys(FONTS), base.font),
        bgType: oneOf(t.bgType, BG_TYPES, base.bgType),
        bg: hex(t.bg, base.bg),
        bg2: hex(t.bg2, base.bg2),
        angle: int(t.angle, 0, 360, base.angle),
        bgImage: imgId(t.bgImage),
        overlay: int(t.overlay, 0, 90, 35),
        text: hex(t.text, base.text),
        btnStyle: oneOf(t.btnStyle, BTN_STYLES, base.btnStyle),
        btnShape: oneOf(t.btnShape, BTN_SHAPES, base.btnShape),
        btnBg: hex(t.btnBg, base.btnBg),
        btnText: hex(t.btnText, base.btnText)
      },
      seo: { title: str(c.seo && c.seo.title, 70), desc: str(c.seo && c.seo.desc, 160) }
    };
    if (o.theme.bgType === "image" && !o.theme.bgImage) o.theme.bgType = "solid";
    return o;
  }

  // Todas las imágenes que usa una configuración (para borrar las que sobran).
  function usedImages(c) {
    var out = [];
    if (c.profile.avatar) out.push(c.profile.avatar);
    if (c.theme.bgImage) out.push(c.theme.bgImage);
    c.items.forEach(function (i) { if (i.thumb) out.push(i.thumb); });
    return out;
  }

  function fontHref(fontId) {
    var f = FONTS[fontId];
    return f && f.g ? "https://fonts.googleapis.com/css2?family=" + f.g + "&display=swap" : "";
  }

  function icon(type) {
    var d = ICONS[type] || ICONS.web;
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' + d + '"/></svg>';
  }

  function hexToRgb(h) {
    var n = parseInt(h.slice(1), 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  }
  function rgba(h, a) { var c = hexToRgb(h); return "rgba(" + c[0] + "," + c[1] + "," + c[2] + "," + a + ")"; }
  function isDark(h) { var c = hexToRgb(h); return (c[0] * 299 + c[1] * 587 + c[2] * 114) / 1000 < 140; }

  // ---------- CSS de la página ----------
  function css(c, img) {
    var t = c.theme;
    var font = (FONTS[t.font] || FONTS.sistema).css;
    var radius = { pill: "999px", rounded: "16px", square: "4px" }[t.btnShape];
    var thumbR = { pill: "999px", rounded: "10px", square: "2px" }[t.btnShape];
    var bgLayer;
    if (t.bgType === "gradient") bgLayer = "linear-gradient(" + t.angle + "deg," + t.bg + "," + t.bg2 + ")";
    else if (t.bgType === "image" && t.bgImage) {
      var ov = (t.overlay / 100).toFixed(2);
      bgLayer = "linear-gradient(rgba(0,0,0," + ov + "),rgba(0,0,0," + ov + ")),url(\"" + img(t.bgImage) + "\") center/cover no-repeat," + t.bg;
    } else bgLayer = t.bg;

    var btn;
    switch (t.btnStyle) {
      case "outline":
        btn = ".b{border:2px solid " + t.btnBg + ";color:" + t.btnBg + ";background:transparent}" +
          ".b:hover{background:" + t.btnBg + ";color:" + t.btnText + "}"; break;
      case "shadow":
        btn = ".b{background:" + t.btnBg + ";color:" + t.btnText + ";border:2px solid " + t.text + ";box-shadow:4px 4px 0 " + t.text + "}" +
          ".b:hover{transform:translate(-2px,-2px);box-shadow:6px 6px 0 " + t.text + "}" +
          ".b:active{transform:translate(2px,2px);box-shadow:2px 2px 0 " + t.text + "}"; break;
      case "glass":
        btn = ".b{background:" + rgba(t.btnBg, 0.16) + ";color:" + t.btnText + ";border:1px solid " + rgba(t.btnBg, 0.32) +
          ";-webkit-backdrop-filter:blur(14px) saturate(1.4);backdrop-filter:blur(14px) saturate(1.4)}" +
          ".b:hover{background:" + rgba(t.btnBg, 0.26) + "}"; break;
      case "soft":
        btn = ".b{background:" + rgba(t.btnBg, 0.14) + ";color:" + t.text + ";border:1px solid " + rgba(t.btnBg, 0.12) + "}" +
          ".b:hover{background:" + rgba(t.btnBg, 0.22) + "}"; break;
      case "line":
        btn = ".l{gap:0}.b,.b.wt{justify-content:space-between;text-align:left;padding:18px 2px;min-height:0;border-bottom:1px solid " + rgba(t.text, 0.22) +
          ";color:" + t.text + ";font-weight:500;font-size:17px;border-radius:0!important}" +
          ".b:first-child{border-top:1px solid " + rgba(t.text, 0.22) + "}" +
          ".b::after{content:'\\2192';font-size:18px;transition:transform .2s ease;margin-left:12px}.b:hover{transform:none}.b:hover::after{transform:translateX(4px)}" +
          ".b .th{position:static;transform:none;margin-right:14px;flex:none}.b .tx{flex:1}" +
          ".l .h{text-align:left;align-self:flex-start;margin:26px 0 8px}"; break;
      default:
        btn = ".b{background:" + t.btnBg + ";color:" + t.btnText + "}" +
          ".b:hover{background:" + rgba(t.btnBg, 0.88) + "}";
    }

    return "*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}" +
      "html{background:" + t.bg + ";-webkit-text-size-adjust:100%}" +
      "body{min-height:100vh;min-height:100dvh;font-family:" + font + ";color:" + t.text + ";-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}" +
      "body::before{content:'';position:fixed;inset:0;z-index:-1;background:" + bgLayer + "}" +
      ".w{max-width:600px;margin:0 auto;padding:56px 20px 40px;display:flex;flex-direction:column;align-items:center;text-align:center}" +
      ".av{width:104px;height:104px;object-fit:cover;display:block;background:" + rgba(t.text, 0.08) + "}" +
      ".ini{width:104px;height:104px;display:grid;place-items:center;font-size:42px;font-weight:700;background:" + t.btnBg + ";color:" + t.btnText + "}" +
      ".circle{border-radius:50%}.rounded{border-radius:28px}.square{border-radius:6px}" +
      "h1{font-size:23px;font-weight:700;line-height:1.25;letter-spacing:-.01em;margin-top:18px;overflow-wrap:anywhere}" +
      ".bio{margin-top:8px;font-size:15.5px;line-height:1.5;opacity:.8;max-width:460px;overflow-wrap:anywhere}" +
      ".s{display:flex;flex-wrap:wrap;justify-content:center;gap:2px;margin-top:18px;max-width:100%}" +
      ".s.bot{margin-top:32px}" +
      ".s a{width:42px;height:42px;display:grid;place-items:center;border-radius:50%;color:" + t.text + ";transition:transform .18s ease,background .18s ease}" +
      ".s a:hover{transform:translateY(-2px);background:" + rgba(t.text, 0.08) + "}" +
      ".s svg{width:23px;height:23px;fill:currentColor}" +
      ".l{width:100%;display:flex;flex-direction:column;gap:14px;margin-top:30px}" +
      ".b{position:relative;display:flex;align-items:center;justify-content:center;min-height:60px;padding:12px 24px;border-radius:" + radius + ";text-decoration:none;font-weight:600;font-size:16px;line-height:1.3;overflow-wrap:anywhere;" +
      "transition:transform .18s cubic-bezier(.2,.8,.2,1),background .18s ease,box-shadow .18s ease,color .18s ease;-webkit-tap-highlight-color:transparent}" +
      ".b:hover{transform:translateY(-2px)}.b:active{transform:scale(.985)}" +
      ".b:focus-visible,.s a:focus-visible{outline:2px solid " + t.text + ";outline-offset:3px}" +
      ".b .th{position:absolute;left:8px;top:50%;transform:translateY(-50%);width:44px;height:44px;object-fit:cover;border-radius:" + thumbR + "}" +
      ".b.wt{padding:12px 60px}" +
      ".b small{display:block;font-weight:400;font-size:13.5px;opacity:.75;margin-top:3px}" +
      ".h{font-size:12.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;opacity:.85;margin:18px 0 0}" +
      ".h:first-child{margin-top:0}" +
      ".hl{animation:vt-hl 4s ease-in-out infinite}" +
      "@keyframes vt-hl{0%,86%,100%{transform:none}89%{transform:rotate(-1.6deg) scale(1.02)}92%{transform:rotate(1.6deg) scale(1.02)}95%{transform:rotate(-.8deg)}}" +
      ".vac{opacity:.6;font-size:14px;margin-top:30px}" +
      btn +
      "@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}";
  }

  // ---------- HTML ----------
  function inner(c, o) {
    var p = c.profile;
    var img = o.img;
    var h = '<main class="w">';
    if (p.avatar) h += '<img class="av ' + p.shape + '" src="' + esc(img(p.avatar)) + '" alt="" width="104" height="104">';
    else if (p.name) h += '<div class="ini ' + p.shape + '" aria-hidden="true">' + esc(Array.from(p.name)[0].toUpperCase()) + "</div>";
    if (p.name) h += "<h1>" + esc(p.name) + "</h1>";
    if (p.bio) h += '<p class="bio">' + esc(p.bio).replace(/\n/g, "<br>") + "</p>";

    var soc = c.socials.filter(function (s) { return safeUrl(s.url); });
    var socHtml = "";
    if (soc.length) {
      socHtml = '<nav class="s' + (c.socialsPos === "bottom" ? " bot" : "") + '" aria-label="Redes">';
      soc.forEach(function (s) {
        var label = (SOCIALS[s.type] || SOCIALS.web).label;
        socHtml += '<a href="' + esc(safeUrl(s.url)) + '" data-s="' + esc(s.type) + '" aria-label="' + esc(label) + '" title="' + esc(label) + '" rel="noopener me">' + icon(s.type) + "</a>";
      });
      socHtml += "</nav>";
    }
    if (c.socialsPos !== "bottom") h += socHtml;

    var vis = c.items.filter(function (i) {
      return !i.hidden && (i.kind === "header" ? i.title : safeUrl(i.url));
    });
    if (vis.length) {
      h += '<div class="l">';
      vis.forEach(function (i) {
        if (i.kind === "header") { h += '<h2 class="h">' + esc(i.title) + "</h2>"; return; }
        var u = safeUrl(i.url);
        var title = i.title || u.replace(/^(https?:\/\/|mailto:|tel:)/i, "").replace(/\/$/, "");
        h += '<a class="b' + (i.thumb ? " wt" : "") + (i.hl ? " hl" : "") + '" href="' + esc(u) + '" data-i="' + esc(i.id) + '" rel="noopener">';
        if (i.thumb) h += '<img class="th" src="' + esc(img(i.thumb)) + '" alt="" width="44" height="44" loading="lazy">';
        h += '<span class="tx">' + esc(title) + (i.sub ? "<small>" + esc(i.sub) + "</small>" : "") + "</span></a>";
      });
      h += "</div>";
    } else if (o.preview) {
      h += '<p class="vac">Aquí saldrán tus enlaces.</p>';
    }
    if (c.socialsPos === "bottom") h += socHtml;
    h += "</main>";
    return h;
  }

  var PREVIEW_CSS = "a{pointer-events:none}html{scrollbar-width:none}::-webkit-scrollbar{display:none}";
  function pageTitle(c) { return c.seo.title || c.profile.name || "Mis enlaces"; }
  function pageDesc(c) { return c.seo.desc || c.profile.bio.replace(/\s+/g, " ").slice(0, 160); }

  // Documento completo. o = { img(id) → url, absImg(id) → url absoluta para la vista previa en redes,
  //   preview, hit (url para contar visitas y clics), script (url del script de recuento) }
  function renderPage(c, o) {
    c = sanitize(c);
    o = o || {};
    var img = o.img || function (id) { return "/i/" + id; };
    var href = fontHref(c.theme.font);
    var title = pageTitle(c), desc = pageDesc(c);
    var h = '<!doctype html><html lang="es"><head><meta charset="utf-8">' +
      '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">' +
      "<title>" + esc(title) + "</title>" +
      (desc ? '<meta name="description" content="' + esc(desc) + '">' : "") +
      '<meta property="og:type" content="profile"><meta property="og:title" content="' + esc(title) + '">' +
      (desc ? '<meta property="og:description" content="' + esc(desc) + '">' : "") +
      (c.profile.avatar && o.absImg ? '<meta property="og:image" content="' + esc(o.absImg(c.profile.avatar)) + '">' +
        '<link rel="icon" href="' + esc(img(c.profile.avatar)) + '">' : "") +
      '<meta name="theme-color" content="' + c.theme.bg + '">' +
      '<meta name="color-scheme" content="' + (isDark(c.theme.bg) ? "dark" : "light") + '">' +
      (href ? '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="' + esc(href) + '">' : "") +
      "<style>" + css(c, img) + (o.preview ? PREVIEW_CSS : "") + "</style></head><body" +
      (o.hit ? ' data-hit="' + esc(o.hit) + '"' : "") + ">" + inner(c, { img: img, preview: o.preview }) +
      (o.inlineScript ? "<script>" + HIT_SCRIPT + "<\/script>" : "") +
      "</body></html>";
    return h;
  }

  // Script mínimo de la página pública: cuenta cada clic (sin cookies ni datos personales).
  // La visita la cuenta el servidor al entregar la página.
  var HIT_SCRIPT = "(function(){var u=document.body.getAttribute('data-hit');if(!u||!navigator.sendBeacon)return;" +
    "function s(d){try{navigator.sendBeacon(u,new Blob([JSON.stringify(d)],{type:'text/plain'}))}catch(e){}}" +
    "document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[data-i],a[data-s]');" +
    "if(!a)return;if(a.dataset.i)s({t:'c',id:a.dataset.i});else s({t:'s',id:a.dataset.s})},true)})();";

  return {
    SOCIALS: SOCIALS, FONTS: FONTS, TEMPLATES: TEMPLATES, ICONS: ICONS,
    BTN_STYLES: BTN_STYLES, BTN_SHAPES: BTN_SHAPES,
    esc: esc, safeUrl: safeUrl, socialUrl: socialUrl, normalizeUrl: normalizeUrl,
    sanitize: sanitize, defaultConfig: defaultConfig, defaultTheme: defaultTheme, usedImages: usedImages,
    renderPage: renderPage, css: css, inner: inner, fontHref: fontHref, icon: icon, isDark: isDark,
    pageTitle: pageTitle, PREVIEW_CSS: PREVIEW_CSS, pageDesc: pageDesc, HIT_SCRIPT: HIT_SCRIPT
  };
})();
VT_RENDER;
}
function vt_panel() {
  $nonce = bin2hex(random_bytes(16));
  $host = 'var VITRINA_HOST=' . json_encode(['kind' => 'php', 'api' => '?api={p}', 'img' => '?i={id}', 'page' => vt_page_url(), 'version' => VT_VERSION], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . ';';
  $html = <<<'VT_PANEL'
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Vitrina · Panel</title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='9' fill='%2317171a'/%3E%3Crect x='8' y='9' width='16' height='4' rx='2' fill='%23fff'/%3E%3Crect x='8' y='15' width='16' height='4' rx='2' fill='%23fff' opacity='.7'/%3E%3Crect x='8' y='21' width='16' height='4' rx='2' fill='%23fff' opacity='.45'/%3E%3C/svg%3E">
<style>
:root{
  --bg:#f5f5f2;--card:#ffffff;--ink:#17171a;--ink2:#3a3a40;--muted:#6c6c74;--line:#e5e5df;--line2:#d6d6cf;
  --soft:#efefea;--accent:#3651ff;--accent-soft:#e8ecff;--ok:#16794a;--ok-soft:#e3f4ea;--warn:#a15c00;--warn-soft:#fff2dc;
  --bad:#c0322b;--bad-soft:#fde8e6;--shadow:0 1px 2px rgba(20,20,30,.05),0 4px 14px rgba(20,20,30,.05);
  --r:14px;--r2:10px;--font:system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",sans-serif;
  color-scheme:light;
}
@media (prefers-color-scheme:dark){:root{
  --bg:#111113;--card:#1b1b1f;--ink:#f1f1ef;--ink2:#d2d2d0;--muted:#9a9aa2;--line:#2b2b31;--line2:#3a3a42;
  --soft:#232328;--accent:#7d8cff;--accent-soft:#252a4a;--ok:#4fcf8e;--ok-soft:#173326;--warn:#f0b357;--warn-soft:#3a2c14;
  --bad:#ff7b72;--bad-soft:#3d1c1a;--shadow:0 1px 2px rgba(0,0,0,.3),0 6px 18px rgba(0,0,0,.25);color-scheme:dark;
}}
*,*::before,*::after{box-sizing:border-box}
html,body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.45 var(--font);-webkit-font-smoothing:antialiased}
button,input,select,textarea{font:inherit;color:inherit}
button{cursor:pointer}
[hidden]{display:none!important}
a{color:var(--accent)}
:focus-visible{outline:2px solid var(--accent);outline-offset:2px;border-radius:6px}
.sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
svg.i{width:18px;height:18px;flex:none;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}

/* ---------- entrar ---------- */
#login{min-height:100vh;min-height:100dvh;display:grid;place-items:center;padding:24px}
.lcard{width:100%;max-width:380px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:32px 28px;box-shadow:var(--shadow)}
.brand{display:flex;align-items:center;gap:10px;font-weight:700;font-size:17px;letter-spacing:-.01em}
.mark{width:30px;height:30px;border-radius:9px;background:var(--ink);display:grid;align-content:center;gap:3px;padding:0 7px}
.mark i{display:block;height:4px;border-radius:2px;background:var(--bg)}.mark i:nth-child(2){opacity:.7}.mark i:nth-child(3){opacity:.45}
.lcard h1{font-size:22px;margin:26px 0 6px;letter-spacing:-.02em}
.lcard p{margin:0 0 20px;color:var(--muted)}
.setup{background:var(--warn-soft);color:var(--ink);border-radius:12px;padding:14px 16px;margin-top:4px;font-size:13.5px}
.setup b{display:block;margin-bottom:4px}
.setup ol{margin:8px 0 0;padding-left:18px}.setup li{margin:3px 0}
.setup code{background:rgba(0,0,0,.07);padding:1px 5px;border-radius:5px;font-size:12.5px}

/* ---------- controles ---------- */
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.field>span,.lbl{font-size:12.5px;font-weight:600;color:var(--ink2)}
.lbl small,.field>span small{font-weight:400;color:var(--muted)}
.in{width:100%;height:40px;padding:0 12px;border:1px solid var(--line2);border-radius:var(--r2);background:var(--card);outline:none;transition:border-color .15s,box-shadow .15s}
textarea.in{height:auto;min-height:84px;padding:10px 12px;resize:vertical;line-height:1.45}
.in:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft)}
.in.err{border-color:var(--bad);box-shadow:0 0 0 3px var(--bad-soft)}
.in::placeholder{color:var(--muted);opacity:.75}
.pw{position:relative}.pw .in{padding-right:44px}
.pw button{position:absolute;right:4px;top:4px;width:32px;height:32px;border:0;background:none;color:var(--muted);border-radius:8px;display:grid;place-items:center}
.count{font-size:11.5px;color:var(--muted);text-align:right;margin-top:-2px}
.hint{font-size:12.5px;color:var(--muted);margin:2px 0 0}
.msg{font-size:12.5px;margin-top:4px}.msg.bad{color:var(--bad)}.msg.ok{color:var(--ok)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:40px;padding:0 16px;border-radius:var(--r2);border:1px solid var(--line2);background:var(--card);font-weight:600;white-space:nowrap;transition:background .15s,border-color .15s,transform .1s,opacity .15s}
.btn:hover{background:var(--soft)}
.btn:active{transform:scale(.98)}
.btn.pri{background:var(--ink);border-color:var(--ink);color:var(--bg)}
.btn.pri:hover{opacity:.88}
.btn.full{width:100%}
.btn:disabled{opacity:.45;cursor:default;transform:none}
.btn.sm{height:32px;padding:0 11px;font-size:13px;border-radius:8px}
.btn.ghost{border-color:transparent;background:transparent}
.btn.ghost:hover{background:var(--soft)}
.btn.danger{color:var(--bad)}
.ib{width:34px;height:34px;display:inline-grid;place-items:center;border:0;background:transparent;border-radius:9px;color:var(--muted);transition:background .15s,color .15s}
.ib:hover{background:var(--soft);color:var(--ink)}
.ib:disabled{opacity:.35;cursor:default;background:none}
.ib.on{color:var(--accent);background:var(--accent-soft)}
.seg{display:inline-flex;background:var(--soft);border-radius:10px;padding:3px;gap:2px;flex-wrap:wrap}
.seg button{border:0;background:transparent;height:32px;padding:0 12px;border-radius:8px;font-weight:600;font-size:13px;color:var(--muted)}
.seg button[aria-pressed=true]{background:var(--card);color:var(--ink);box-shadow:0 1px 2px rgba(0,0,0,.08)}
.sw{position:relative;width:38px;height:22px;flex:none;border-radius:99px;border:0;background:var(--line2);transition:background .2s;padding:0}
.sw::after{content:"";position:absolute;left:3px;top:3px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);transition:transform .2s}
.sw[aria-checked=true]{background:var(--ok)}
.sw[aria-checked=true]::after{transform:translateX(16px)}
.color{display:flex;align-items:center;gap:8px}
.color input[type=color]{width:40px;height:40px;padding:0;border:1px solid var(--line2);border-radius:10px;background:none;cursor:pointer;flex:none}
.color input[type=color]::-webkit-color-swatch-wrapper{padding:3px}.color input[type=color]::-webkit-color-swatch{border:0;border-radius:7px}
.color input[type=color]::-moz-color-swatch{border:0;border-radius:7px}
.color .in{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;text-transform:lowercase}
input[type=range]{width:100%;accent-color:var(--ink)}
.row{display:flex;gap:12px;align-items:center}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media (max-width:520px){.grid2{grid-template-columns:1fr}}

/* ---------- estructura ---------- */
#main{min-height:100vh;min-height:100dvh}
.top{position:sticky;top:0;z-index:20;height:60px;display:flex;align-items:center;gap:10px;padding:0 16px 0 18px;background:color-mix(in srgb,var(--bg) 86%,transparent);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px);border-bottom:1px solid var(--line)}
.url{display:flex;align-items:center;gap:6px;min-width:0;margin-left:10px;padding:0 6px 0 12px;height:34px;border:1px solid var(--line);border-radius:99px;background:var(--card);color:var(--ink2);font-size:13px}
.url span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px}
.url .ib{width:26px;height:26px;border-radius:99px}
.url .ib svg{width:15px;height:15px}
.grow{flex:1}
.status{font-size:12.5px;color:var(--muted);display:flex;align-items:center;gap:6px;white-space:nowrap}
.status i{width:7px;height:7px;border-radius:50%;background:var(--ok)}
.status.dirty i{background:var(--warn)}
.shell{display:grid;grid-template-columns:200px minmax(0,1fr) 420px;max-width:1440px;margin:0 auto}
.nav{position:sticky;top:60px;align-self:start;padding:18px 10px;display:flex;flex-direction:column;gap:2px;height:calc(100vh - 60px)}
.nav button{display:flex;align-items:center;gap:10px;height:40px;padding:0 12px;border:0;background:transparent;border-radius:10px;color:var(--muted);font-weight:600;text-align:left}
.nav button:hover{background:var(--soft);color:var(--ink)}
.nav button[aria-current=page]{background:var(--card);color:var(--ink);box-shadow:var(--shadow)}
.nav .ver{margin-top:auto;padding:0 12px;font-size:11.5px;color:var(--muted)}
.ed{padding:24px 28px 120px;min-width:0}
.ed>.in-w{max-width:640px;margin:0 auto}
.ed h2{font-size:22px;letter-spacing:-.02em;margin:0 0 4px}
.ed .sub{color:var(--muted);margin:0 0 20px}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:18px;margin-bottom:16px;box-shadow:var(--shadow)}
.card h3{font-size:14.5px;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.card h3 small{font-weight:400;color:var(--muted);font-size:12.5px}
.pv{position:sticky;top:60px;align-self:start;height:calc(100vh - 60px);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:20px;border-left:1px solid var(--line)}
.phone{width:340px;height:min(700px,calc(100vh - 160px));border-radius:42px;padding:10px;background:#0d0d0f;box-shadow:0 30px 60px -20px rgba(0,0,0,.35),0 0 0 1px rgba(255,255,255,.06) inset;position:relative}
.phone::before{content:"";position:absolute;top:18px;left:50%;transform:translateX(-50%);width:86px;height:24px;border-radius:20px;background:#0d0d0f;z-index:2}
.phone iframe{width:100%;height:100%;border:0;border-radius:33px;background:#fff;display:block}
.pv .btn{gap:6px}
.pvclose{display:none}

/* ---------- enlaces ---------- */
.adds{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.adds .btn.pri{flex:1;height:46px;font-size:15px;border-radius:12px}
.adds .btn:not(.pri){height:46px;border-radius:12px}
.list{display:flex;flex-direction:column;gap:10px}
.it{background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:var(--shadow);display:flex;gap:4px;padding:12px 12px 10px 4px;transition:box-shadow .2s,opacity .2s,transform .2s}
.it.off{opacity:.55}
.it.drag{transition:none;box-shadow:0 18px 40px -12px rgba(0,0,0,.3);transform:scale(1.01);z-index:5;position:relative;opacity:.96}
.it.new{animation:pop .35s cubic-bezier(.2,.8,.2,1)}
@keyframes pop{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
.handle{width:28px;flex:none;border:0;background:none;color:var(--muted);cursor:grab;border-radius:8px;display:grid;place-items:center;touch-action:none}
.handle:active{cursor:grabbing}
.handle svg{width:16px;height:16px}
.body{flex:1;min-width:0;display:flex;flex-direction:column;gap:4px}
.line1{display:flex;align-items:center;gap:8px}
.tin{flex:1;min-width:0;border:0;background:transparent;height:30px;padding:0 6px;border-radius:7px;font-weight:600;font-size:15px;outline:none}
.uin{border:0;background:transparent;height:28px;padding:0 6px;border-radius:7px;color:var(--ink2);font-size:13.5px;outline:none;width:100%}
.tin:hover,.uin:hover,.sin:hover{background:var(--soft)}
.tin:focus,.uin:focus,.sin:focus{background:var(--soft);box-shadow:0 0 0 2px var(--accent-soft)}
.uin.err{color:var(--bad);box-shadow:0 0 0 2px var(--bad-soft)}
.sin{border:0;background:transparent;height:26px;padding:0 6px;border-radius:7px;color:var(--muted);font-size:13px;outline:none;width:100%}
.tools{display:flex;align-items:center;gap:2px;margin-top:4px;flex-wrap:wrap}
.tools .ib{width:auto;height:30px;padding:0 8px;gap:6px;font-size:12.5px;font-weight:600;display:inline-flex}
.tools .ib svg{width:16px;height:16px}
.tools .badge{margin-left:auto;font-size:12px;color:var(--muted);padding:0 8px}
@media (max-width:520px){.tools .lb{display:none}.tools .ib{width:36px;padding:0;justify-content:center}.tools .ib .thumbmini{margin:0}}
.thumbmini{width:24px;height:24px;border-radius:6px;object-fit:cover}
.it.hd{padding:10px 12px 10px 4px;background:var(--soft);box-shadow:none;border-style:dashed}
.it.hd .tin{font-size:12.5px;text-transform:uppercase;letter-spacing:.08em}
.empty{border:1.5px dashed var(--line2);border-radius:var(--r);padding:30px 20px;text-align:center;color:var(--muted)}
.empty b{display:block;color:var(--ink);font-size:15px;margin-bottom:4px}
.soc{display:flex;flex-direction:column;gap:8px}
.socrow{display:flex;align-items:center;gap:8px}
.socico{width:38px;height:38px;border-radius:10px;background:var(--soft);display:grid;place-items:center;flex:none}
.socico svg{width:19px;height:19px;fill:var(--ink)}
.socrow .in{height:38px}
.socpick{display:grid;grid-template-columns:repeat(auto-fill,minmax(96px,1fr));gap:6px;margin-top:10px}
.socpick button{display:flex;align-items:center;gap:7px;height:36px;padding:0 10px;border:1px solid var(--line);background:var(--card);border-radius:9px;font-size:12.5px;font-weight:600;color:var(--ink2)}
.socpick button:hover{border-color:var(--line2);background:var(--soft)}
.socpick svg{width:15px;height:15px;fill:currentColor;flex:none}
.steps{display:grid;gap:8px;margin:4px 0 0;padding:0;list-style:none}
.steps li{display:flex;align-items:center;gap:10px;font-size:13.5px}
.steps li i{width:20px;height:20px;border-radius:50%;border:1.5px solid var(--line2);display:grid;place-items:center;flex:none}
.steps li.done{color:var(--muted);text-decoration:line-through}
.steps li.done i{background:var(--ok);border-color:var(--ok)}
.steps li.done i::after{content:"";width:8px;height:4px;border:2px solid #fff;border-top:0;border-right:0;transform:rotate(-45deg) translate(1px,-1px)}
.steps button{border:0;background:none;padding:0;color:var(--accent);font-weight:600}
.banner{display:flex;gap:12px;align-items:center;flex-wrap:wrap;background:var(--accent-soft);border-radius:var(--r);padding:12px 14px;margin-bottom:16px;font-size:13.5px}
.banner .grow{min-width:180px}

/* ---------- perfil ---------- */
.avwrap{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
.avbig{width:96px;height:96px;border-radius:50%;background:var(--soft);display:grid;place-items:center;overflow:hidden;border:1px solid var(--line);flex:none;position:relative;cursor:pointer;color:var(--muted)}
.avbig img{width:100%;height:100%;object-fit:cover}
.avbig.rounded{border-radius:26px}.avbig.square{border-radius:8px}
.avbig.drop{outline:2px dashed var(--accent);outline-offset:3px}
.avbtns{display:flex;flex-direction:column;gap:8px;align-items:flex-start}

/* ---------- diseño ---------- */
.tpls{display:grid;grid-template-columns:repeat(auto-fill,minmax(112px,1fr));gap:10px}
.tpl{border:0;padding:0;background:none;text-align:left;border-radius:14px}
.tpl .mock{height:148px;border-radius:12px;padding:16px 12px;display:flex;flex-direction:column;align-items:center;gap:7px;overflow:hidden;border:1px solid var(--line);transition:transform .2s,box-shadow .2s}
.tpl:hover .mock{transform:translateY(-2px);box-shadow:var(--shadow)}
.tpl[aria-pressed=true] .mock{box-shadow:0 0 0 2px var(--bg),0 0 0 4px var(--accent)}
.tpl .dot{width:26px;height:26px;border-radius:50%;margin-bottom:4px}
.tpl .bar{width:100%;height:17px;flex:none}
.tpl .nm{display:block;font-size:12.5px;font-weight:600;margin:7px 2px 0;color:var(--ink2)}
.bstyles{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.bst{height:64px;border-radius:12px;border:1px solid var(--line);background:var(--soft);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;font-size:12px;font-weight:600;color:var(--ink2);padding:0 14px}
.bst[aria-pressed=true]{box-shadow:0 0 0 2px var(--accent);border-color:transparent}
.bst .bar{width:100%;height:14px;border-radius:5px}
.fonts{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px}
.fonts button{height:52px;border-radius:11px;border:1px solid var(--line);background:var(--card);font-size:17px;padding:0 12px;text-align:left;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fonts button[aria-pressed=true]{box-shadow:0 0 0 2px var(--accent);border-color:transparent}
.warnbox{display:flex;gap:8px;align-items:flex-start;background:var(--warn-soft);color:var(--ink);border-radius:10px;padding:10px 12px;font-size:12.5px;margin-top:10px}
.bgimg{display:flex;gap:12px;align-items:center}
.bgimg .prev{width:72px;height:96px;border-radius:10px;background:var(--soft) center/cover;border:1px solid var(--line);flex:none}

/* ---------- estadísticas ---------- */
.tiles{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px}
.tile{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:16px;box-shadow:var(--shadow)}
.tile span{font-size:12.5px;color:var(--muted);font-weight:600}
.tile b{display:block;font-size:28px;letter-spacing:-.03em;margin-top:4px;font-variant-numeric:tabular-nums}
@media (max-width:520px){.tiles{grid-template-columns:1fr 1fr}.tile b{font-size:24px}}
.chart svg{width:100%;height:160px;display:block}
.chart .ax{font-size:11px;fill:var(--muted)}
.rank{display:flex;flex-direction:column;gap:10px}
.rank .r{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 12px;align-items:center;font-size:13.5px}
.rank .r .t{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rank .r b{font-variant-numeric:tabular-nums}
.rank .r .bar{grid-column:1/-1;height:6px;border-radius:9px;background:var(--soft);overflow:hidden}
.rank .r .bar i{display:block;height:100%;background:var(--ink);border-radius:9px}
.note{font-size:12.5px;color:var(--muted);margin-top:12px}

/* ---------- avisos ---------- */
#toasts{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:100;display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;width:calc(100% - 32px);max-width:440px}
.toast{pointer-events:auto;display:flex;align-items:center;gap:12px;background:var(--ink);color:var(--bg);padding:11px 12px 11px 16px;border-radius:12px;box-shadow:0 12px 30px -8px rgba(0,0,0,.4);font-size:13.5px;animation:tin .25s cubic-bezier(.2,.8,.2,1)}
.toast.bad{background:var(--bad);color:#fff}
.toast button{border:0;background:rgba(255,255,255,.16);color:inherit;height:28px;padding:0 10px;border-radius:7px;font-weight:600;font-size:12.5px}
.toast a{color:inherit;font-weight:600}
@keyframes tin{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.spin{width:16px;height:16px;border-radius:50%;border:2px solid currentColor;border-right-color:transparent;animation:sp .7s linear infinite}
@keyframes sp{to{transform:rotate(360deg)}}
.loading{display:grid;place-items:center;min-height:100vh;color:var(--muted)}

/* ---------- móvil ---------- */
.fab{display:none}
@media (max-width:1180px){
  .shell{grid-template-columns:180px minmax(0,1fr)}
  .pv{display:none;position:fixed;inset:0;z-index:60;height:auto;border:0;background:color-mix(in srgb,var(--bg) 92%,transparent);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px)}
  .pv.open{display:flex}
  .pvclose{display:inline-flex}
  .phone{height:min(720px,calc(100dvh - 130px));width:min(340px,calc(100vw - 32px))}
  .fab{display:inline-flex;position:fixed;right:18px;bottom:22px;z-index:40;height:46px;border-radius:99px;padding:0 18px;box-shadow:0 12px 26px -8px rgba(0,0,0,.45)}
}
@media (max-width:760px){
  .shell{grid-template-columns:1fr}
  .nav{position:fixed;top:auto;bottom:0;left:0;right:0;height:auto;z-index:30;flex-direction:row;justify-content:space-around;padding:6px 6px calc(6px + env(safe-area-inset-bottom));background:var(--card);border-top:1px solid var(--line)}
  .nav button{flex-direction:column;gap:2px;height:52px;padding:0 4px;font-size:11px;flex:1;justify-content:center;align-items:center}
  .nav button[aria-current=page]{box-shadow:none;background:var(--soft)}
  .nav .ver{display:none}
  .ed{padding:18px 16px 190px}
  .top{padding:0 10px 0 14px;gap:4px}
  .url{display:none}
  .status span{display:none}
  .fab{bottom:calc(86px + env(safe-area-inset-bottom))}
  #toasts{bottom:calc(150px + env(safe-area-inset-bottom))}
  .top .btn.pri{padding:0 14px}
}
@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@600&family=DM+Sans:wght@600&family=Space+Grotesk:wght@600&family=Outfit:wght@600&family=Bricolage+Grotesque:wght@600&family=Syne:wght@600&family=Fraunces:wght@600&family=Playfair+Display:wght@600&family=JetBrains+Mono:wght@600&display=swap">
</head>
<body>
<div id="boot" class="loading"><div class="spin" aria-label="Cargando"></div></div>

<div id="login" hidden>
  <form class="lcard" id="loginForm" novalidate>
    <div class="brand"><span class="mark"><i></i><i></i><i></i></span>Vitrina</div>
    <h1>Entra en tu panel</h1>
    <p>Desde aquí cambias tu página de enlaces.</p>
    <div id="setupMsg" class="setup" hidden></div>
    <div id="loginFields">
      <label class="field"><span>Contraseña</span>
        <div class="pw"><input class="in" id="pass" type="password" autocomplete="current-password" required>
          <button type="button" id="eye" aria-label="Mostrar contraseña"></button></div>
      </label>
      <div id="loginErr" class="msg bad" role="alert"></div>
      <button class="btn pri full" id="loginBtn" type="submit" style="margin-top:8px;height:44px">Entrar</button>
    </div>
  </form>
</div>

<div id="main" hidden>
  <header class="top">
    <div class="brand"><span class="mark"><i></i><i></i><i></i></span><span class="bn">Vitrina</span></div>
    <div class="url" id="urlChip"><span id="urlText"></span>
      <button class="ib" id="copyUrl" title="Copiar enlace" aria-label="Copiar enlace"></button>
      <a class="ib" id="openUrl" target="_blank" rel="noopener" title="Abrir mi página" aria-label="Abrir mi página"></a>
    </div>
    <div class="grow"></div>
    <button class="ib" id="undo" title="Deshacer (Ctrl+Z)" aria-label="Deshacer"></button>
    <button class="ib" id="redo" title="Rehacer (Ctrl+Mayús+Z)" aria-label="Rehacer"></button>
    <div class="status" id="status" aria-live="polite"><i></i><span>Todo publicado</span></div>
    <button class="btn pri" id="publish" disabled>Publicar</button>
  </header>
  <div class="shell">
    <nav class="nav" id="nav" aria-label="Secciones"></nav>
    <main class="ed"><div class="in-w" id="ed"></div></main>
    <aside class="pv" id="pv" aria-label="Vista previa">
      <div class="phone"><iframe id="frame" title="Vista previa de tu página" sandbox="allow-same-origin"></iframe></div>
      <div class="row">
        <a class="btn sm" id="openUrl2" target="_blank" rel="noopener">Abrir mi página</a>
        <button class="btn sm pvclose" id="pvClose">Cerrar vista previa</button>
      </div>
    </aside>
  </div>
  <button class="btn pri fab" id="fab"></button>
</div>
<div id="toasts" aria-live="polite"></div>
<input type="file" id="file" accept="image/png,image/jpeg,image/webp,image/gif,image/*" hidden>

<script nonce="__NONCE__">/*__RENDER__*/
/*__HOST__*/</script>
<script nonce="__NONCE__">
(function () {
"use strict";
var R = window.VitrinaR, H = window.VITRINA_HOST;
var $ = function (id) { return document.getElementById(id); };

// ---------------------------------------------------------------- iconos del panel (fijos)
var IC = {
  link: '<path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1"/><path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1"/>',
  user: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
  brush: '<path d="M14.5 4.5l5 5L10 19l-6 1 1-6z"/><path d="M12.5 6.5l5 5"/>',
  chart: '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
  cog: '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
  undo: '<path d="M9 14L4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>',
  redo: '<path d="M15 14l5-5-5-5"/><path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>',
  copy: '<rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/>',
  ext: '<path d="M14 4h6v6"/><path d="M20 4l-9 9"/><path d="M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>',
  grip: '<circle cx="9" cy="6" r="1.2"/><circle cx="15" cy="6" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="9" cy="18" r="1.2"/><circle cx="15" cy="18" r="1.2"/>',
  trash: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
  image: '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/>',
  star: '<path d="M12 3l2.7 5.6 6.1.8-4.5 4.2 1.1 6-5.4-2.9-5.4 2.9 1.1-6L3.2 9.4l6.1-.8z"/>',
  text: '<path d="M4 7V5h16v2M9 19h6M12 5v14"/>',
  plus: '<path d="M12 5v14M5 12h14"/>',
  eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
  eyeoff: '<path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.5 7 10 7a9.8 9.8 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
  phone: '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>',
  x: '<path d="M6 6l12 12M18 6L6 18"/>',
  warn: '<path d="M12 3l10 18H2z"/><path d="M12 10v4M12 17.5v.5"/>',
  heading: '<path d="M5 5v14M19 5v14M5 12h14"/>',
  up: '<path d="M12 19V5M5 12l7-7 7 7"/>'
};
function svg(name) { return '<svg class="i" viewBox="0 0 24 24" aria-hidden="true">' + IC[name] + "</svg>"; }

// h('div', {class:'x', onclick: fn}, hijos…) — crea nodos sin meter nunca texto de la persona como HTML.
function h(tag, props) {
  var el = document.createElement(tag);
  if (props) for (var k in props) {
    var v = props[k];
    if (v == null || v === false) continue;
    if (k === "class") el.className = v;
    else if (k === "style") el.style.cssText = v;
    else if (k === "icon") el.insertAdjacentHTML("afterbegin", svg(v));
    else if (k === "svgraw") el.insertAdjacentHTML("afterbegin", v);
    else if (k.slice(0, 2) === "on") el.addEventListener(k.slice(2), v);
    else if (k === "value" || k === "checked" || k === "disabled") el[k] = v;
    else el.setAttribute(k, v === true ? "" : v);
  }
  for (var i = 2; i < arguments.length; i++) add(el, arguments[i]);
  return el;
}
function add(el, c) {
  if (c == null || c === false) return;
  if (Array.isArray(c)) { c.forEach(function (x) { add(el, x); }); return; }
  el.append(c instanceof Node ? c : document.createTextNode(String(c)));
}
function uid() { var a = "abcdefghijklmnopqrstuvwxyz0123456789", s = ""; var r = crypto.getRandomValues(new Uint8Array(10)); for (var i = 0; i < 10; i++) s += a[r[i] % 36]; return s; }
function clone(o) { return JSON.parse(JSON.stringify(o)); }
function plural(n, one, many) { return n.toLocaleString("es") + " " + (n === 1 ? one : many); }

// ---------------------------------------------------------------- servidor
function apiUrl(path, qs) {
  var u = H.api.replace("{p}", path);
  if (qs) u += (u.indexOf("?") >= 0 ? "&" : "?") + qs;
  return u;
}
function imgUrl(id) { return H.img.replace("{id}", id); }
function api(method, path, body, opts) {
  opts = opts || {};
  var init = { method: method, headers: { "x-vitrina": "1" }, credentials: "same-origin" };
  if (body !== undefined) {
    if (body instanceof Blob) { init.body = body; init.headers["content-type"] = body.type || "application/octet-stream"; }
    else { init.body = JSON.stringify(body); init.headers["content-type"] = "application/json"; }
  }
  return fetch(apiUrl(path, opts.qs), init).then(function (r) {
    return r.text().then(function (t) {
      var d = null; try { d = t ? JSON.parse(t) : {}; } catch (e) { d = null; }
      if (r.status === 401 && path !== "entrar" && path !== "contrasena") { showLogin(); throw new Error("Tu sesión ha caducado. Vuelve a entrar; tus cambios siguen guardados en este navegador."); }
      if (!r.ok) throw new Error((d && d.error) || ("El servidor ha respondido con un error (" + r.status + ")."));
      if (d === null) throw new Error("El servidor ha respondido algo raro. ¿Está bien instalado?");
      return d;
    });
  }, function () { throw new Error("No hay conexión con tu página. Revisa internet y vuelve a probar."); });
}

// ---------------------------------------------------------------- estado
var cfg = null, publishedJson = "", publishedAt = null, history = [], future = [], lastKey = "", lastTime = 0;
var draftEl = null, tab = "enlaces", stats = null, statDays = 30, clicks30 = {};
var DRAFT_KEY = "vitrina-borrador:" + location.host + location.pathname;

function cfgJson(c) { return JSON.stringify(R.sanitize(c)); }
function dirty() { return cfg && cfgJson(cfg) !== publishedJson; }

// Cada cambio pasa por aquí: guarda el paso para deshacer, refresca la vista previa y el borrador.
function change(fn, key) {
  var now = Date.now();
  if (!(key && key === lastKey && now - lastTime < 1200)) { history.push(JSON.stringify(cfg)); if (history.length > 120) history.shift(); }
  lastKey = key || ""; lastTime = now;
  future = [];
  fn(cfg);
  afterChange();
}
function afterChange() {
  updatePreview();
  updateStatus();
  // Mientras se ofrece recuperar el borrador anterior, no se pisa.
  if (!draftEl) try { if (dirty()) localStorage.setItem(DRAFT_KEY, JSON.stringify(cfg)); else localStorage.removeItem(DRAFT_KEY); } catch (e) {}
}
function undo() {
  if (!history.length) return;
  future.push(JSON.stringify(cfg)); cfg = JSON.parse(history.pop()); lastKey = "";
  renderTab(); afterChange();
}
function redo() {
  if (!future.length) return;
  history.push(JSON.stringify(cfg)); cfg = JSON.parse(future.pop()); lastKey = "";
  renderTab(); afterChange();
}

function updateStatus() {
  var d = dirty();
  var st = $("status");
  st.className = "status" + (d ? " dirty" : "");
  st.lastChild.textContent = d ? "Cambios sin publicar" : (publishedAt ? "Todo publicado" : "Sin publicar");
  $("publish").disabled = !d && !!publishedAt;
  $("undo").disabled = !history.length;
  $("redo").disabled = !future.length;
}

// ---------------------------------------------------------------- avisos
function toast(text, opts) {
  opts = opts || {};
  var t = h("div", { class: "toast" + (opts.bad ? " bad" : ""), role: opts.bad ? "alert" : "status" }, h("span", { class: "grow" }, text));
  if (opts.action) t.append(h("button", { onclick: function () { opts.action.fn(); t.remove(); } }, opts.action.label));
  if (opts.link) t.append(h("a", { href: opts.link.href, target: "_blank", rel: "noopener" }, opts.link.label));
  $("toasts").append(t);
  setTimeout(function () { t.remove(); }, opts.ms || (opts.bad ? 6500 : 4200));
}
function errToast(e) { toast(e && e.message ? e.message : String(e), { bad: true }); }

// ---------------------------------------------------------------- vista previa
var pvReady = false, pvFont = "", pvTimer = 0;
function previewHtml() { return R.renderPage(cfg, { img: imgUrl, preview: true }); }
function updatePreview() {
  if (pvTimer) return;
  pvTimer = setTimeout(function () {
    pvTimer = 0;
    var f = $("frame"), doc = null;
    try { doc = f.contentDocument; } catch (e) {}
    var c = R.sanitize(cfg);
    if (!pvReady || !doc || !doc.getElementById("vtcss")) {
      f.srcdoc = previewHtml().replace("<style>", '<style id="vtcss">');
      pvReady = true; pvFont = c.theme.font; return;
    }
    if (pvFont !== c.theme.font) { f.srcdoc = previewHtml().replace("<style>", '<style id="vtcss">'); pvFont = c.theme.font; return; }
    doc.getElementById("vtcss").textContent = R.css(c, imgUrl) + R.PREVIEW_CSS;
    var main = doc.querySelector("main");
    var tmp = doc.createElement("div");
    tmp.innerHTML = R.inner(c, { img: imgUrl, preview: true }); // ya viene escapado por el dibujante
    if (main) main.replaceWith(tmp.firstChild);
  }, 40);
}

// ---------------------------------------------------------------- imágenes
function pickFile(cb) {
  var f = $("file");
  f.value = "";
  f.onchange = function () { if (f.files && f.files[0]) cb(f.files[0]); };
  f.click();
}
// Reduce la foto en el navegador antes de subirla (así pesa poco y la página va rápida).
function prepImage(file, kind) {
  if (!/^image\//.test(file.type) && !/\.(jpe?g|png|webp|gif|heic|heif)$/i.test(file.name)) return Promise.reject(new Error("Ese archivo no es una imagen."));
  if (file.size > 25 * 1024 * 1024) return Promise.reject(new Error("La imagen pesa más de 25 MB. Prueba con una más pequeña."));
  if (file.type === "image/gif" && file.size <= 1200 * 1024 && kind !== "bg") return Promise.resolve(file);
  var size = kind === "avatar" ? 480 : kind === "thumb" ? 176 : 1800;
  var square = kind !== "bg";
  var load = window.createImageBitmap ? createImageBitmap(file, { imageOrientation: "from-image" }).catch(function () { return loadImg(file); }) : loadImg(file);
  return load.then(function (bmp) {
    var w = bmp.width, hh = bmp.height, sx = 0, sy = 0, sw = w, sh = hh;
    if (square) { var m = Math.min(w, hh); sx = (w - m) / 2; sy = (hh - m) / 2; sw = sh = m; }
    var scale = Math.min(1, size / Math.max(sw, sh));
    var cw = Math.max(1, Math.round(sw * scale)), ch = Math.max(1, Math.round(sh * scale));
    var cv = document.createElement("canvas"); cv.width = cw; cv.height = ch;
    var ctx = cv.getContext("2d"); ctx.imageSmoothingQuality = "high";
    ctx.drawImage(bmp, sx, sy, sw, sh, 0, 0, cw, ch);
    return new Promise(function (res) { cv.toBlob(res, "image/webp", 0.86); }).then(function (b) {
      if (b && b.type === "image/webp") return b;
      return new Promise(function (res) { cv.toBlob(res, "image/jpeg", 0.88); });
    });
  }).then(function (b) {
    if (!b) throw new Error("No he podido preparar la imagen.");
    return b;
  }, function () { throw new Error("No puedo leer esa imagen. Prueba con una JPG o PNG (las HEIC del iPhone a veces no se abren)."); });
}
function loadImg(file) {
  return new Promise(function (res, rej) {
    var u = URL.createObjectURL(file), im = new Image();
    im.onload = function () { res(im); setTimeout(function () { URL.revokeObjectURL(u); }, 1000); };
    im.onerror = function () { URL.revokeObjectURL(u); rej(new Error("x")); };
    im.src = u;
  });
}
function uploadImage(file, kind) {
  var t = h("div", { class: "toast", role: "status" }, h("div", { class: "spin" }), h("span", {}, "Subiendo imagen…"));
  $("toasts").append(t);
  return prepImage(file, kind).then(function (blob) { return api("POST", "imagen", blob); })
    .then(function (d) { t.remove(); return d.id; }, function (e) { t.remove(); throw e; });
}
function dropZone(el, kind, onId) {
  el.addEventListener("dragover", function (e) { e.preventDefault(); el.classList.add("drop"); });
  el.addEventListener("dragleave", function () { el.classList.remove("drop"); });
  el.addEventListener("drop", function (e) {
    e.preventDefault(); el.classList.remove("drop");
    var f = e.dataTransfer.files && e.dataTransfer.files[0];
    if (f) uploadImage(f, kind).then(onId, errToast);
  });
}

// ---------------------------------------------------------------- pestañas
var TABS = [
  { id: "enlaces", label: "Enlaces", icon: "link" },
  { id: "perfil", label: "Perfil", icon: "user" },
  { id: "diseno", label: "Diseño", icon: "brush" },
  { id: "estadisticas", label: "Estadísticas", icon: "chart" },
  { id: "ajustes", label: "Ajustes", icon: "cog" }
];
function renderNav() {
  var nav = $("nav"); nav.textContent = "";
  TABS.forEach(function (t) {
    nav.append(h("button", { icon: t.icon, "aria-current": tab === t.id ? "page" : null, onclick: function () { go(t.id); } }, h("span", {}, t.label)));
  });
  nav.append(h("div", { class: "ver" }, "Vitrina " + (H.version || "")));
}
function go(id) {
  tab = id; renderNav(); renderTab();
  window.scrollTo({ top: 0 });
  try { sessionStorage.setItem("vitrina-tab", id); } catch (e) {}
}
function renderTab() {
  var ed = $("ed"); ed.textContent = "";
  if (draftEl && draftEl.isConnected !== undefined) ed.append(draftEl);
  ({ enlaces: tabLinks, perfil: tabProfile, diseno: tabDesign, estadisticas: tabStats, ajustes: tabSettings })[tab](ed);
}

// ---------------------------------------------------------------- Enlaces
function tabLinks(ed) {
  ed.append(h("h2", {}, "Enlaces"), h("p", { class: "sub" }, "Lo que verá la gente al tocar el enlace de tu bio."));

  var c = cfg;
  var steps = [
    { done: !!c.profile.avatar, text: "Pon tu foto", go: "perfil" },
    { done: !!c.profile.name, text: "Escribe tu nombre", go: "perfil" },
    { done: c.items.some(function (i) { return i.kind === "link" && R.safeUrl(i.url); }), text: "Añade tu primer enlace", fn: function () { addItem("link"); } },
    { done: c.theme.template !== "papel" || c.theme.bgType !== "solid" || c.theme.bg !== R.TEMPLATES.papel.bg, text: "Elige un diseño", go: "diseno" },
    { done: !!publishedAt, text: "Pulsa «Publicar»", fn: function () { $("publish").focus(); } }
  ];
  if (!publishedAt || steps.some(function (s) { return !s.done; }) && c.items.length < 2) {
    var left = steps.filter(function (s) { return !s.done; }).length;
    if (left) {
      ed.append(h("div", { class: "card" },
        h("h3", {}, "Primeros pasos ", h("small", {}, (5 - left) + " de 5")),
        h("ul", { class: "steps" }, steps.map(function (s) {
          return h("li", { class: s.done ? "done" : "" }, h("i"), s.done ? s.text :
            h("button", { onclick: function () { if (s.go) go(s.go); else s.fn(); } }, s.text));
        }))));
    }
  }

  ed.append(h("div", { class: "adds" },
    h("button", { class: "btn pri", icon: "plus", onclick: function () { addItem("link"); } }, "Añadir enlace"),
    h("button", { class: "btn", icon: "heading", onclick: function () { addItem("header"); } }, "Título de sección")));

  var list = h("div", { class: "list", id: "list" });
  if (!c.items.length) {
    list.append(h("div", { class: "empty" }, h("b", {}, "Todavía no hay enlaces"), "Añade tu web, tu último vídeo, tu tienda o tu WhatsApp."));
  }
  c.items.forEach(function (it, idx) { list.append(itemCard(it, idx)); });
  ed.append(list);

  ed.append(socialsCard());
}

function addItem(kind) {
  var it = { id: uid(), kind: kind, title: "", url: "", sub: "", thumb: "", hidden: false, hl: false };
  change(function (c) { c.items.unshift(it); });
  if (tab !== "enlaces") go("enlaces"); else renderTab();
  var el = document.querySelector('[data-id="' + it.id + '"]');
  if (el) {
    el.classList.add("new");
    var f = el.querySelector(kind === "link" ? ".uin" : ".tin"); if (f) f.focus();
  }
}

function itemCard(it, idx) {
  var card = h("div", { class: "it" + (it.kind === "header" ? " hd" : "") + (it.hidden ? " off" : ""), "data-id": it.id });
  var handle = h("button", { class: "handle", icon: "grip", "aria-label": "Mover (arrastra o usa las flechas)", title: "Arrastra para ordenar" });
  setupDrag(handle, card);
  handle.addEventListener("keydown", function (e) {
    if (e.key === "ArrowUp" || e.key === "ArrowDown") {
      e.preventDefault();
      var i = cfg.items.findIndex(function (x) { return x.id === it.id; });
      var j = i + (e.key === "ArrowUp" ? -1 : 1);
      if (j < 0 || j >= cfg.items.length) return;
      change(function (c) { var m = c.items.splice(i, 1)[0]; c.items.splice(j, 0, m); });
      renderTab();
      var nh = document.querySelector('[data-id="' + it.id + '"] .handle'); if (nh) nh.focus();
    }
  });
  var get = function () { return cfg.items.find(function (x) { return x.id === it.id; }); };
  var title = h("input", { class: "tin", value: it.title, maxlength: 100, placeholder: it.kind === "header" ? "Título de la sección" : "Título del botón", "aria-label": "Título",
    oninput: function () { var v = title.value; change(function () { get().title = v; }, "t" + it.id); } });
  var vis = h("button", { class: "sw", role: "switch", "aria-checked": String(!it.hidden), "aria-label": "Visible en tu página", title: it.hidden ? "Oculto" : "Visible",
    onclick: function () { change(function () { get().hidden = !get().hidden; }); var o = get().hidden; vis.setAttribute("aria-checked", String(!o)); vis.title = o ? "Oculto" : "Visible"; card.classList.toggle("off", o); } });
  var del = h("button", { class: "ib", icon: "trash", "aria-label": "Borrar", title: "Borrar",
    onclick: function () {
      var i = cfg.items.findIndex(function (x) { return x.id === it.id; });
      change(function (c) { c.items.splice(i, 1); });
      renderTab();
      toast(it.kind === "header" ? "Sección borrada" : "Enlace borrado", { action: { label: "Deshacer", fn: undo } });
    } });
  var body = h("div", { class: "body" }, h("div", { class: "line1" }, title, vis));
  if (it.kind === "link") {
    var msg = h("div", { class: "msg bad", hidden: true });
    var url = h("input", { class: "uin", value: it.url, maxlength: 2000, placeholder: "Pega aquí la dirección (tuweb.com, un correo o un teléfono)", "aria-label": "Dirección", inputmode: "url", autocapitalize: "off", spellcheck: "false",
      oninput: function () { var v = url.value; url.classList.remove("err"); msg.hidden = true; change(function () { get().url = v.trim(); }, "u" + it.id); },
      onchange: function () {
        var v = R.normalizeUrl(url.value);
        if (v !== url.value.trim()) { url.value = v; change(function () { get().url = v; }, "u" + it.id); }
        checkUrl(url, msg, v);
        if (!get().title && R.safeUrl(v)) { var g = guessTitle(v); if (g) { title.value = g; change(function () { get().title = g; }, "t" + it.id); } }
      } });
    if (it.url && !R.safeUrl(it.url)) checkUrl(url, msg, it.url);
    body.append(url, msg);
    var extra = h("div");
    if (it.sub || it._showSub) extra.append(subInput());
    function subInput() {
      var s = h("input", { class: "sin", value: it.sub, maxlength: 140, placeholder: "Texto pequeño debajo (opcional)", "aria-label": "Subtítulo",
        oninput: function () { var v = s.value; change(function () { get().sub = v; }, "s" + it.id); } });
      return s;
    }
    body.append(extra);
    var thumbBtn = h("button", { class: "ib" + (it.thumb ? " on" : ""), title: it.thumb ? "Cambiar o quitar la miniatura" : "Añadir una miniatura",
      onclick: function () {
        if (get().thumb) {
          change(function () { get().thumb = ""; }); renderTab();
          toast("Miniatura quitada", { action: { label: "Deshacer", fn: undo } });
          return;
        }
        pickFile(function (f) { uploadImage(f, "thumb").then(function (id) { change(function () { get().thumb = id; }); renderTab(); }, errToast); });
      } },
      it.thumb ? h("img", { class: "thumbmini", src: imgUrl(it.thumb), alt: "" }) : h("span", { icon: "image", style: "display:inline-flex" }),
      h("span", { class: "lb" }, it.thumb ? "Quitar" : "Miniatura"));
    dropZone(card, "thumb", function (id) { change(function () { get().thumb = id; }); renderTab(); });
    var hl = h("button", { class: "ib" + (it.hl ? " on" : ""), icon: "star", title: "Hace que el botón se mueva de vez en cuando para llamar la atención", "aria-pressed": String(it.hl),
      onclick: function () { change(function () { get().hl = !get().hl; }); hl.classList.toggle("on", get().hl); hl.setAttribute("aria-pressed", String(get().hl)); } }, h("span", { class: "lb" }, "Destacar"));
    var subBtn = h("button", { class: "ib" + (it.sub ? " on" : ""), icon: "text", title: "Texto pequeño debajo del título",
      onclick: function () { if (!extra.firstChild) { var s = subInput(); extra.append(s); s.focus(); } else extra.firstChild.focus(); } }, h("span", { class: "lb" }, "Subtítulo"));
    var n = clicks30[it.id] || 0;
    body.append(h("div", { class: "tools" }, thumbBtn, subBtn, hl,
      h("span", { class: "badge", title: "Clics en los últimos 30 días" }, n ? plural(n, "clic", "clics") : "")));
  }
  body.querySelector(".line1").append(del);
  card.append(handle, body);
  return card;
}

function checkUrl(input, msg, v) {
  if (!v) { input.classList.remove("err"); msg.hidden = true; return true; }
  if (R.safeUrl(v)) { input.classList.remove("err"); msg.hidden = true; return true; }
  input.classList.add("err"); msg.hidden = false;
  msg.textContent = /^javascript:|^data:/i.test(v) ? "Ese tipo de enlace no se permite por seguridad."
    : /^@/.test(v) ? "Para un @ de Instagram, TikTok… usa «Redes sociales» (más abajo) o pega aquí el enlace completo de tu perfil."
    : "Esta dirección no parece válida. Revisa que esté bien escrita.";
  return false;
}
function guessTitle(u) {
  if (/^mailto:/i.test(u)) return "Escríbeme";
  if (/^tel:/i.test(u)) return "Llámame";
  if (/wa\.me|whatsapp/i.test(u)) return "Escríbeme por WhatsApp";
  try {
    var host = new URL(u).hostname.replace(/^www\./, "");
    var map = { "youtube.com": "YouTube", "youtu.be": "YouTube", "instagram.com": "Instagram", "tiktok.com": "TikTok", "amzn.to": "Amazon", "spotify.com": "Spotify", "open.spotify.com": "Spotify", "twitch.tv": "Twitch", "github.com": "GitHub", "linkedin.com": "LinkedIn", "x.com": "X", "twitter.com": "X", "facebook.com": "Facebook", "t.me": "Telegram" };
    if (map[host]) return map[host];
    var base = host.split(".").slice(-2, -1)[0] || host;
    return base.charAt(0).toUpperCase() + base.slice(1);
  } catch (e) { return ""; }
}

// Arrastrar para ordenar (ratón y dedo).
function setupDrag(handle, card) {
  handle.addEventListener("pointerdown", function (e) {
    if (e.button !== 0) return;
    e.preventDefault();
    var list = $("list"), startY = e.clientY, startScroll = window.scrollY, lastY = e.clientY, moved = false, raf = 0;
    var cards = [].slice.call(list.children);
    var from = cards.indexOf(card), to = from;
    // posiciones en coordenadas de la página (no de la pantalla), para que el scroll no las descuadre
    var rects = cards.map(function (c) { var r = c.getBoundingClientRect(); return { top: r.top + window.scrollY, height: r.height }; });
    var hgt = rects[from].height + 10;
    var nav = document.querySelector(".nav");
    var bottomEdge = function () { var nr = nav.getBoundingClientRect(); return (nr.top > innerHeight / 2 ? nr.top : innerHeight) - 70; };
    try { handle.setPointerCapture(e.pointerId); } catch (err) {}
    card.classList.add("drag");
    function place() {
      var dy = lastY - startY + (window.scrollY - startScroll);
      if (Math.abs(dy) > 3) moved = true;
      card.style.transform = "translateY(" + dy + "px) scale(1.01)";
      var mid = rects[from].top + rects[from].height / 2 + dy;
      to = from;
      for (var i = 0; i < rects.length; i++) {
        var m = rects[i].top + rects[i].height / 2;
        if (i < from && mid < m) { to = i; break; }
        if (i > from && mid > m) to = i;
      }
      cards.forEach(function (c, i) {
        if (c === card) return;
        var s = 0;
        if (from < to && i > from && i <= to) s = -hgt;
        if (to < from && i >= to && i < from) s = hgt;
        c.style.transition = "transform .18s ease"; c.style.transform = s ? "translateY(" + s + "px)" : "";
      });
    }
    // Mientras el dedo esté cerca de un borde, la lista se desplaza sola.
    function edgeScroll() {
      var step = lastY < 120 ? -14 : lastY > bottomEdge() ? 14 : 0;
      if (step) { window.scrollBy(0, step); place(); }
      raf = requestAnimationFrame(edgeScroll);
    }
    raf = requestAnimationFrame(edgeScroll);
    function mv(ev) { lastY = ev.clientY; place(); }
    function up() {
      cancelAnimationFrame(raf);
      handle.removeEventListener("pointermove", mv);
      handle.removeEventListener("pointerup", up);
      handle.removeEventListener("pointercancel", up);
      cards.forEach(function (c) { c.style.transform = ""; c.style.transition = ""; });
      card.classList.remove("drag");
      if (moved && to !== from) {
        change(function (c) { var m = c.items.splice(from, 1)[0]; c.items.splice(to, 0, m); });
        renderTab();
      }
    }
    handle.addEventListener("pointermove", mv);
    handle.addEventListener("pointerup", up);
    handle.addEventListener("pointercancel", up);
  });
}

function socialsCard() {
  var card = h("div", { class: "card" }, h("h3", {}, "Redes sociales ", h("small", {}, "salen como iconos")));
  var box = h("div", { class: "soc" });
  cfg.socials.forEach(function (s) {
    var info = R.SOCIALS[s.type];
    var msg = h("div", { class: "msg bad", hidden: true });
    var inp = h("input", { class: "in", value: s.url, placeholder: info.hint, "aria-label": info.label, autocapitalize: "off", spellcheck: "false",
      oninput: function () { var v = inp.value.trim(); inp.classList.remove("err"); msg.hidden = true; change(function () { find().url = v; }, "so" + s.id); },
      onchange: function () {
        var v = inp.value.trim(); if (!v) return;
        var u = R.socialUrl(s.type, v);
        if (u) { inp.value = u; change(function () { find().url = u; }, "so" + s.id); }
        else { inp.classList.add("err"); msg.hidden = false; msg.textContent = "No lo entiendo. Pega el enlace completo de tu perfil."; }
      } });
    if (s.url && !R.safeUrl(s.url)) { inp.classList.add("err"); msg.hidden = false; msg.textContent = "No lo entiendo. Pega el enlace completo de tu perfil."; }
    function find() { return cfg.socials.find(function (x) { return x.id === s.id; }); }
    box.append(h("div", {}, h("div", { class: "socrow" },
      h("span", { class: "socico", title: info.label, svgraw: R.icon(s.type) }), inp,
      h("button", { class: "ib", icon: "trash", "aria-label": "Quitar " + info.label, onclick: function () {
        change(function (c) { c.socials = c.socials.filter(function (x) { return x.id !== s.id; }); }); renderTab();
        toast(info.label + " quitado", { action: { label: "Deshacer", fn: undo } });
      } })), msg));
  });
  card.append(box);
  var used = cfg.socials.map(function (s) { return s.type; });
  var pick = h("div", { class: "socpick" });
  Object.keys(R.SOCIALS).forEach(function (k) {
    if (used.indexOf(k) >= 0) return;
    pick.append(h("button", { svgraw: R.icon(k), onclick: function () {
      var id = uid();
      change(function (c) { c.socials.push({ id: id, type: k, url: "" }); }); renderTab();
      var el = document.querySelector('.soc input[aria-label="' + R.SOCIALS[k].label + '"]'); if (el) el.focus();
    } }, R.SOCIALS[k].label));
  });
  if (cfg.socials.length < 20) card.append(h("div", { class: "lbl", style: "margin-top:14px" }, cfg.socials.length ? "Añadir otra" : "Elige las que uses"), pick);
  card.append(h("div", { class: "row", style: "margin-top:16px;justify-content:space-between;flex-wrap:wrap" }, h("span", { class: "lbl" }, "Dónde salen los iconos"),
    seg([["top", "Arriba"], ["bottom", "Abajo"]], cfg.socialsPos, function (v) { change(function (c) { c.socialsPos = v; }); })));
  return card;
}

function seg(options, current, onPick) {
  var s = h("div", { class: "seg", role: "group" });
  options.forEach(function (o) {
    s.append(h("button", { "aria-pressed": String(o[0] === current), onclick: function () {
      [].forEach.call(s.children, function (b) { b.setAttribute("aria-pressed", "false"); });
      this.setAttribute("aria-pressed", "true"); onPick(o[0]);
    } }, o[1]));
  });
  return s;
}
function counter(input, max) {
  var c = h("div", { class: "count" }, input.value.length + " / " + max);
  input.addEventListener("input", function () { c.textContent = input.value.length + " / " + max; });
  return c;
}

// ---------------------------------------------------------------- Perfil
function tabProfile(ed) {
  ed.append(h("h2", {}, "Perfil"), h("p", { class: "sub" }, "Tu foto, tu nombre y unas palabras sobre ti."));
  var p = cfg.profile;
  var av = h("button", { class: "avbig " + p.shape, "aria-label": "Subir foto", title: "Toca o arrastra una foto aquí", onclick: up },
    p.avatar ? h("img", { src: imgUrl(p.avatar), alt: "" }) : h("span", { icon: "image" }));
  function up() { pickFile(function (f) { uploadImage(f, "avatar").then(setAv, errToast); }); }
  function setAv(id) { change(function (c) { c.profile.avatar = id; }); renderTab(); }
  dropZone(av, "avatar", setAv);
  ed.append(h("div", { class: "card" }, h("h3", {}, "Foto"),
    h("div", { class: "avwrap" }, av, h("div", { class: "avbtns" },
      h("div", { class: "row" },
        h("button", { class: "btn sm", onclick: up }, p.avatar ? "Cambiar foto" : "Subir foto"),
        p.avatar ? h("button", { class: "btn sm ghost danger", onclick: function () { change(function (c) { c.profile.avatar = ""; }); renderTab(); toast("Foto quitada", { action: { label: "Deshacer", fn: undo } }); } }, "Quitar") : null),
      seg([["circle", "Círculo"], ["rounded", "Redondeada"], ["square", "Cuadrada"]], p.shape, function (v) { change(function (c) { c.profile.shape = v; }); av.className = "avbig " + v; }),
      h("p", { class: "hint" }, "Se recorta en cuadrado y se reduce sola. Sirve JPG, PNG, WebP o GIF.")))));

  var name = h("input", { class: "in", value: p.name, maxlength: 60, placeholder: "Tu nombre o el de tu marca", oninput: function () { var v = name.value; change(function (c) { c.profile.name = v; }, "name"); } });
  var bio = h("textarea", { class: "in", maxlength: 300, placeholder: "Una o dos frases: qué haces y qué van a encontrar aquí.", oninput: function () { var v = bio.value; change(function (c) { c.profile.bio = v; }, "bio"); } });
  bio.value = p.bio;
  ed.append(h("div", { class: "card" }, h("h3", {}, "Texto"),
    h("label", { class: "field" }, h("span", {}, "Nombre"), name, counter(name, 60)),
    h("label", { class: "field", style: "margin-bottom:0" }, h("span", {}, "Bio"), bio, counter(bio, 300))));

  var st = h("input", { class: "in", value: cfg.seo.title, maxlength: 70, placeholder: p.name || "Mis enlaces", oninput: function () { var v = st.value; change(function (c) { c.seo.title = v; }, "seot"); } });
  var sd = h("textarea", { class: "in", maxlength: 160, style: "min-height:64px", placeholder: p.bio ? p.bio.slice(0, 160) : "Lo que sale debajo del título", oninput: function () { var v = sd.value; change(function (c) { c.seo.desc = v; }, "seod"); } });
  sd.value = cfg.seo.desc;
  ed.append(h("div", { class: "card" }, h("h3", {}, "Al compartir tu enlace ", h("small", {}, "opcional")),
    h("p", { class: "hint", style: "margin:-6px 0 12px" }, "Lo que sale en WhatsApp, Google o Telegram. Si lo dejas vacío se usan tu nombre y tu bio."),
    h("label", { class: "field" }, h("span", {}, "Título"), st),
    h("label", { class: "field", style: "margin-bottom:0" }, h("span", {}, "Descripción"), sd)));
}

// ---------------------------------------------------------------- Diseño
function lum(hx) { var n = parseInt(hx.slice(1), 16); var c = [(n >> 16) & 255, (n >> 8) & 255, n & 255].map(function (v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }); return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2]; }
function contrast(a, b) { var x = lum(a), y = lum(b); return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05); }
function barStyle(t) {
  var r = { pill: "99px", rounded: "5px", square: "1px" }[t.btnShape];
  var al = function (hx, a) { var n = parseInt(hx.slice(1), 16); return "rgba(" + ((n >> 16) & 255) + "," + ((n >> 8) & 255) + "," + (n & 255) + "," + a + ")"; };
  switch (t.btnStyle) {
    case "outline": return "border:1.5px solid " + t.btnBg + ";border-radius:" + r;
    case "shadow": return "background:" + t.btnBg + ";border:1.5px solid " + t.text + ";box-shadow:2px 2px 0 " + t.text + ";border-radius:" + r;
    case "glass": return "background:" + al(t.btnBg, 0.2) + ";border:1px solid " + al(t.btnBg, 0.35) + ";border-radius:" + r;
    case "soft": return "background:" + al(t.btnBg, 0.16) + ";border-radius:" + r;
    case "line": return "border-bottom:1.5px solid " + al(t.text, 0.3) + ";height:12px;border-radius:0";
    default: return "background:" + t.btnBg + ";border-radius:" + r;
  }
}
function bgStyle(t) { return t.bgType === "gradient" ? "background:linear-gradient(" + t.angle + "deg," + t.bg + "," + t.bg2 + ")" : "background:" + t.bg; }

function tabDesign(ed) {
  ed.append(h("h2", {}, "Diseño"), h("p", { class: "sub" }, "Empieza con una plantilla y retoca lo que quieras."));
  var t = cfg.theme;
  var tpls = h("div", { class: "tpls" });
  Object.keys(R.TEMPLATES).forEach(function (k) {
    var tp = R.TEMPLATES[k];
    var mock = h("div", { class: "mock", style: bgStyle(tp) + ";font-family:" + R.FONTS[tp.font].css + ";color:" + tp.text },
      h("div", { class: "dot", style: "background:" + tp.text + ";opacity:.85" }),
      h("div", { class: "bar", style: barStyle(tp) }), h("div", { class: "bar", style: barStyle(tp) }), h("div", { class: "bar", style: barStyle(tp) }));
    tpls.append(h("button", { class: "tpl", "aria-pressed": String(t.template === k), "aria-label": "Plantilla " + tp.label, onclick: function () {
      change(function (c) {
        var img = c.theme.bgImage, ov = c.theme.overlay, wasImg = c.theme.bgType === "image" && img;
        c.theme = R.defaultTheme(k); c.theme.bgImage = img; c.theme.overlay = ov;
        if (wasImg) readableOnPhoto(c.theme);
      });
      renderTab();
    } }, mock, h("span", { class: "nm" }, tp.label)));
  });
  ed.append(h("div", { class: "card" }, h("h3", {}, "Plantillas"), tpls));

  // Fondo
  var fondo = h("div", { class: "card" }, h("h3", {}, "Fondo"));
  fondo.append(seg([["solid", "Color"], ["gradient", "Degradado"], ["image", "Imagen"]], t.bgType, function (v) {
    if (v === "image" && !cfg.theme.bgImage) {
      pickFile(function (f) { uploadImage(f, "bg").then(function (id) { change(function (c) { c.theme.bgImage = id; c.theme.bgType = "image"; readableOnPhoto(c.theme); }); renderTab(); }, errToast); });
      renderTab(); return;
    }
    change(function (c) { c.theme.bgType = v; if (v === "image") readableOnPhoto(c.theme); }); renderTab();
  }));
  var fb = h("div", { style: "margin-top:16px" });
  if (t.bgType !== "image") {
    fb.append(h("div", { class: "grid2" },
      colorField(t.bgType === "gradient" ? "Color de arriba" : "Color de fondo", "bg"),
      t.bgType === "gradient" ? colorField("Color de abajo", "bg2") : null));
    if (t.bgType === "gradient") fb.append(rangeField("Ángulo", "angle", 0, 360, "°"));
  } else {
    var prev = h("div", { class: "prev", style: t.bgImage ? "background-image:url(" + imgUrl(t.bgImage) + ")" : "" });
    dropZone(prev, "bg", function (id) { change(function (c) { c.theme.bgImage = id; c.theme.bgType = "image"; readableOnPhoto(c.theme); }); renderTab(); });
    fb.append(h("div", { class: "bgimg" }, prev, h("div", { class: "avbtns" },
      h("button", { class: "btn sm", onclick: function () { pickFile(function (f) { uploadImage(f, "bg").then(function (id) { change(function (c) { c.theme.bgImage = id; }); renderTab(); }, errToast); }); } }, "Cambiar imagen"),
      h("p", { class: "hint" }, "Mejor en vertical. Se reduce sola."))));
    fb.append(h("div", { style: "margin-top:14px" }, rangeField("Oscurecer la imagen", "overlay", 0, 90, "%")));
    fb.append(colorField("Color mientras carga", "bg"));
  }
  fondo.append(fb);
  ed.append(fondo);

  // Botones
  var styles = [["fill", "Relleno"], ["outline", "Contorno"], ["shadow", "Sombra"], ["glass", "Cristal"], ["soft", "Suave"], ["line", "Línea"]];
  var bs = h("div", { class: "bstyles" });
  styles.forEach(function (s) {
    var demo = Object.assign({}, t, { btnStyle: s[0] });
    bs.append(h("button", { class: "bst", "aria-pressed": String(t.btnStyle === s[0]), style: bgStyle(t) + ";color:" + t.text, onclick: function () { change(function (c) { c.theme.btnStyle = s[0]; }); renderTab(); } },
      h("div", { class: "bar", style: barStyle(demo) }), s[1]));
  });
  var botones = h("div", { class: "card" }, h("h3", {}, "Botones"), bs,
    h("div", { class: "row", style: "margin:16px 0;justify-content:space-between;flex-wrap:wrap" }, h("span", { class: "lbl" }, "Forma"),
      seg([["pill", "Píldora"], ["rounded", "Redondeada"], ["square", "Recta"]], t.btnShape, function (v) { change(function (c) { c.theme.btnShape = v; }); })));
  if (t.btnStyle !== "line") botones.append(h("div", { class: "grid2" },
    colorField(t.btnStyle === "outline" ? "Color del borde" : "Color del botón", "btnBg"),
    t.btnStyle === "soft" || t.btnStyle === "outline" ? null : colorField("Texto del botón", "btnText")));
  var cw = contrastWarning();
  if (cw) botones.append(cw);
  ed.append(botones);

  // Texto
  var fonts = h("div", { class: "fonts" });
  Object.keys(R.FONTS).forEach(function (k) {
    var f = R.FONTS[k];
    fonts.append(h("button", { "aria-pressed": String(t.font === k), style: "font-family:" + f.css + ";font-weight:600", onclick: function () { change(function (c) { c.theme.font = k; }); renderTab(); } }, f.label));
  });
  ed.append(h("div", { class: "card" }, h("h3", {}, "Letra y color del texto"), fonts,
    h("p", { class: "hint", style: "margin-top:10px" }, "Todas menos «Sistema» se cargan de Google Fonts, como en casi cualquier web. «Sistema» usa la letra del móvil de cada uno y es la que más rápido carga."),
    h("div", { style: "margin-top:16px" }, colorField("Color del texto", "text"))));
}
// Con foto de fondo: texto claro, velo oscuro suficiente y botones que se lean sobre cualquier foto.
function readableOnPhoto(t) {
  t.text = "#ffffff";
  if (t.overlay < 40) t.overlay = 40;
  if (t.btnStyle === "line" || t.btnStyle === "soft" || t.btnStyle === "outline") { t.btnStyle = "outline"; t.btnBg = "#ffffff"; t.btnText = "#111111"; }
  else if (t.btnStyle === "glass") { t.btnBg = "#ffffff"; t.btnText = "#ffffff"; }
  else if (contrast(t.btnBg, t.btnText) < 4.5) { t.btnBg = "#ffffff"; t.btnText = "#111111"; }
}
function mix(a, b, k) { var x = parseInt(a.slice(1), 16), y = parseInt(b.slice(1), 16); var o = [16, 8, 0].map(function (s) { return Math.round(((x >> s) & 255) * (1 - k) + ((y >> s) & 255) * k); }); return "#" + o.map(function (v) { return v.toString(16).padStart(2, "0"); }).join(""); }
function contrastIssues(t) {
  // Fondos que hay detrás del texto: el color (o los dos del degradado); con foto, un gris medio con el velo.
  var bgs = t.bgType === "gradient" ? [t.bg, t.bg2] : t.bgType === "image" ? [mix("#8a8a8a", "#000000", t.overlay / 100)] : [t.bg];
  var low = function (a, b, min) { return contrast(a, b) < min; };
  var bad = [];
  if (bgs.some(function (b) { return low(t.text, b, 4.5); })) bad.push("el texto y el fondo");
  if ((t.btnStyle === "fill" || t.btnStyle === "shadow") && low(t.btnBg, t.btnText, 4.5)) bad.push("el botón y su texto");
  if (t.btnStyle === "glass" && bgs.some(function (b) { return low(mix(b, t.btnBg, 0.16), t.btnText, 4.5); })) bad.push("los botones de cristal y su texto");
  if (t.btnStyle === "soft" && bgs.some(function (b) { return low(mix(b, t.btnBg, 0.14), t.text, 4.5); })) bad.push("los botones y su texto");
  if (t.btnStyle === "outline" && bgs.some(function (b) { return low(t.btnBg, b, 4.5); })) bad.push("los botones y el fondo");
  return bad;
}
function contrastWarning() {
  var bad = contrastIssues(R.sanitize(cfg).theme);
  if (!bad.length) return null;
  return h("div", { class: "warnbox", icon: "warn" }, h("span", {}, "Poco contraste entre " + bad.join(" y ") + ": puede leerse mal en el móvil."));
}
function colorField(label, key) {
  var val = cfg.theme[key];
  var pick = h("input", { type: "color", value: val, "aria-label": label });
  var txt = h("input", { class: "in", value: val, maxlength: 7, spellcheck: "false", "aria-label": label + " (código)" });
  var timer = 0;
  pick.addEventListener("input", function () { txt.value = pick.value; set(pick.value); });
  txt.addEventListener("input", function () { var v = txt.value.trim(); if (v[0] !== "#") v = "#" + v; if (/^#[0-9a-f]{6}$/i.test(v)) { pick.value = v.toLowerCase(); set(v.toLowerCase()); } });
  txt.addEventListener("change", function () { txt.value = cfg.theme[key]; });
  function set(v) {
    change(function (c) { c.theme[key] = v; }, "c" + key);
    clearTimeout(timer); timer = setTimeout(function () { refreshWarn(); }, 250);
  }
  return h("label", { class: "field", style: "margin-bottom:0" }, h("span", {}, label), h("div", { class: "color" }, pick, txt));
}
function refreshWarn() {
  if (tab !== "diseno") return;
  var old = document.querySelector(".warnbox");
  var nw = contrastWarning();
  if (old && nw) old.replaceWith(nw);
  else if (old) old.remove();
  else if (nw) { var cards = document.querySelectorAll("#ed .card"); if (cards[2]) cards[2].append(nw); }
}
function rangeField(label, key, min, max, unit) {
  var out = h("small", {}, " " + cfg.theme[key] + unit);
  var r = h("input", { type: "range", min: min, max: max, value: cfg.theme[key], "aria-label": label, oninput: function () { var v = parseInt(r.value, 10); out.textContent = " " + v + unit; change(function (c) { c.theme[key] = v; }, "r" + key); } });
  return h("label", { class: "field" }, h("span", {}, label, out), r);
}

// ---------------------------------------------------------------- Estadísticas
function loadStats(days) {
  return api("GET", "estadisticas", undefined, { qs: "dias=" + days }).then(function (d) {
    var byDay = {}, links = {}, socials = {}, views = 0, clicks = 0;
    d.rows.forEach(function (r) {
      if (r.k === "v") { byDay[r.day] = (byDay[r.day] || 0) + r.n; views += r.n; }
      else if (r.k.slice(0, 2) === "c:") { links[r.k.slice(2)] = (links[r.k.slice(2)] || 0) + r.n; clicks += r.n; }
      else if (r.k.slice(0, 2) === "s:") { socials[r.k.slice(2)] = (socials[r.k.slice(2)] || 0) + r.n; clicks += r.n; }
    });
    var series = [], start = new Date(d.from + "T00:00:00Z");
    for (var i = 0; i < d.days; i++) { var k = new Date(start.getTime() + i * 864e5).toISOString().slice(0, 10); series.push([k, byDay[k] || 0]); }
    return { days: d.days, series: series, links: links, socials: socials, views: views, clicks: clicks };
  });
}
function tabStats(ed) {
  ed.append(h("h2", {}, "Estadísticas"), h("p", { class: "sub" }, "Cuánta gente entra en tu página y qué toca."));
  ed.append(h("div", { style: "margin-bottom:16px" }, seg([["7", "7 días"], ["30", "30 días"], ["90", "90 días"]], String(statDays), function (v) { statDays = parseInt(v, 10); stats = null; renderTab(); })));
  var box = h("div", {}, h("div", { class: "card", style: "display:flex;gap:10px;align-items:center;color:var(--muted)" }, h("div", { class: "spin" }), "Cargando…"));
  ed.append(box);
  var p = stats && stats.days === statDays ? Promise.resolve(stats) : loadStats(statDays);
  p.then(function (s) {
    stats = s;
    if (tab !== "estadisticas") return;
    box.textContent = "";
    var perVisit = s.views ? (s.clicks / s.views).toLocaleString("es", { maximumFractionDigits: 1, minimumFractionDigits: 1 }) : "—";
    box.append(h("div", { class: "tiles" },
      h("div", { class: "tile" }, h("span", {}, "Visitas"), h("b", {}, s.views.toLocaleString("es"))),
      h("div", { class: "tile" }, h("span", {}, "Clics"), h("b", {}, s.clicks.toLocaleString("es"))),
      h("div", { class: "tile", title: "Clics de media por cada visita" }, h("span", {}, "Clics por visita"), h("b", {}, perVisit))));
    box.append(h("div", { class: "card chart" }, h("h3", {}, "Visitas por día"), chart(s.series)));
    var rows = cfg.items.filter(function (i) { return i.kind === "link"; }).map(function (i) { return [i.title || i.url || "Sin título", s.links[i.id] || 0]; });
    rows.sort(function (a, b) { return b[1] - a[1]; });
    var known = {}; cfg.items.forEach(function (i) { known[i.id] = 1; });
    var gone = Object.keys(s.links).reduce(function (t, k) { return known[k] ? t : t + s.links[k]; }, 0);
    if (gone) rows.push(["Enlaces que ya borraste", gone]);
    box.append(h("div", { class: "card" }, h("h3", {}, "Clics por enlace"), rank(rows, "Aún no hay enlaces.")));
    var srows = Object.keys(s.socials).map(function (k) { return [(R.SOCIALS[k] || {}).label || k, s.socials[k]]; }).sort(function (a, b) { return b[1] - a[1]; });
    if (cfg.socials.length) box.append(h("div", { class: "card" }, h("h3", {}, "Clics en redes"), rank(srows, "Todavía nadie ha tocado tus iconos.")));
    box.append(h("p", { class: "note" }, "Se cuenta sin cookies y sin guardar datos de nadie: solo un número por día. No cuenta robots ni la vista previa de este panel, pero sí tus propias visitas a la página. Los días van en hora UTC."));
  }, function (e) { box.textContent = ""; box.append(h("div", { class: "card" }, e.message)); });
}
function chart(series) {
  var W = 600, Hh = 150, pad = 18, max = Math.max(1, Math.max.apply(null, series.map(function (x) { return x[1]; })));
  var n = series.length, bw = (W - pad) / n;
  var NS = "http://www.w3.org/2000/svg";
  var s = document.createElementNS(NS, "svg");
  s.setAttribute("viewBox", "0 0 " + W + " " + (Hh + 20)); s.setAttribute("preserveAspectRatio", "none"); s.setAttribute("role", "img");
  s.setAttribute("aria-label", "Visitas por día");
  var g = function (tag, at, txt) { var e = document.createElementNS(NS, tag); for (var k in at) e.setAttribute(k, at[k]); if (txt != null) e.textContent = txt; s.append(e); return e; };
  g("line", { x1: 0, x2: W, y1: Hh, y2: Hh, stroke: "currentColor", "stroke-opacity": ".15" });
  series.forEach(function (d, i) {
    var bh = d[1] ? Math.max(3, (d[1] / max) * (Hh - 14)) : 0;
    var r = g("rect", { x: pad + i * bw + bw * 0.15, y: Hh - bh, width: Math.max(1, bw * 0.7), height: bh, rx: Math.min(4, bw * 0.3), fill: "var(--ink)" });
    var tt = document.createElementNS(NS, "title"); tt.textContent = d[0].split("-").reverse().join("/") + ": " + plural(d[1], "visita", "visitas"); r.append(tt);
  });
  g("text", { x: 0, y: 10, class: "ax" }, String(max));
  g("text", { x: pad, y: Hh + 16, class: "ax" }, series[0][0].slice(5).split("-").reverse().join("/"));
  g("text", { x: W, y: Hh + 16, class: "ax", "text-anchor": "end" }, "hoy");
  return s;
}
function rank(rows, empty) {
  if (!rows.length) return h("p", { class: "hint" }, empty);
  var max = Math.max(1, rows[0][1]);
  return h("div", { class: "rank" }, rows.map(function (r) {
    return h("div", { class: "r" }, h("span", { class: "t" }, r[0]), h("b", {}, r[1].toLocaleString("es")),
      h("div", { class: "bar" }, h("i", { style: "width:" + Math.round((r[1] / max) * 100) + "%" })));
  }));
}

// ---------------------------------------------------------------- Ajustes
function tabSettings(ed) {
  ed.append(h("h2", {}, "Ajustes"), h("p", { class: "sub" }, "Contraseña, copias de seguridad y tu enlace."));
  ed.append(h("div", { class: "card" }, h("h3", {}, "Tu enlace"),
    h("p", { class: "hint", style: "margin:-6px 0 12px" }, "Este es el enlace que pones en tu bio de Instagram, TikTok o YouTube."),
    h("div", { class: "row", style: "flex-wrap:wrap" }, h("input", { class: "in", value: H.page, readonly: true, style: "flex:1;min-width:220px", onfocus: function () { this.select(); } }),
      h("button", { class: "btn", icon: "copy", onclick: copyUrl }, "Copiar"))));

  var a = h("input", { class: "in", type: "password", autocomplete: "current-password" });
  var n1 = h("input", { class: "in", type: "password", autocomplete: "new-password", minlength: 10 });
  var n2 = h("input", { class: "in", type: "password", autocomplete: "new-password" });
  var pm = h("div", { class: "msg", role: "status" });
  var pb = h("button", { class: "btn pri", type: "submit" }, "Cambiar contraseña");
  var form = h("form", { class: "card", onsubmit: function (e) {
    e.preventDefault(); pm.className = "msg bad";
    if (n1.value.length < 10) { pm.textContent = "La nueva tiene que tener al menos 10 caracteres."; return; }
    if (n1.value !== n2.value) { pm.textContent = "Las dos nuevas no coinciden."; return; }
    pb.disabled = true;
    api("POST", "contrasena", { actual: a.value, nueva: n1.value }).then(function () {
      pm.className = "msg ok"; pm.textContent = "Hecho. Las demás sesiones abiertas se han cerrado."; a.value = n1.value = n2.value = "";
    }, function (e) { pm.textContent = e.message; }).then(function () { pb.disabled = false; });
  } }, h("h3", {}, "Contraseña"),
    h("div", { class: "grid2" }, h("label", { class: "field" }, h("span", {}, "La de ahora"), a), h("span")),
    h("div", { class: "grid2" }, h("label", { class: "field" }, h("span", {}, "Nueva ", h("small", {}, "(mínimo 10)")), n1), h("label", { class: "field" }, h("span", {}, "Repite la nueva"), n2)),
    h("div", { class: "row", style: "flex-wrap:wrap" }, pb, pm),
    h("p", { class: "note" }, H.kind === "php"
      ? "¿La olvidas? Abre index.php en el gestor de archivos de tu hosting, escribe una contraseña nueva en CONTRASENA_INICIAL y entra con ella."
      : "¿La olvidas? En Cloudflare, abre tu Worker → Ajustes → Variables y secretos, cambia el secreto CONTRASENA y entra con el nuevo."));
  ed.append(form);

  var imp = h("input", { type: "file", accept: "application/json,.json", hidden: true, onchange: function () {
    var f = imp.files[0]; if (!f) return;
    if (!confirm("Esto cambia toda tu página por la de la copia. ¿Seguro?")) return;
    f.text().then(function (t) { var d = JSON.parse(t); return api("POST", "copia", d); }).then(function (d) {
      cfg = clone(d.config); publishedJson = cfgJson(cfg); publishedAt = Date.now(); history = []; future = [];
      afterChange(); toast("Copia restaurada y publicada.");
    }).catch(function (e) { errToast(e instanceof SyntaxError ? new Error("Ese archivo no es una copia de Vitrina.") : e); });
  } });
  ed.append(h("div", { class: "card" }, h("h3", {}, "Copia de seguridad"),
    h("p", { class: "hint", style: "margin:-6px 0 12px" }, "Un archivo con tu página publicada y sus imágenes. Sirve para no perder nada o para pasarla a otra Vitrina."),
    h("div", { class: "row", style: "flex-wrap:wrap" },
      h("a", { class: "btn", href: apiUrl("copia"), download: "" }, "Descargar copia"),
      h("button", { class: "btn", onclick: function () { imp.click(); } }, "Restaurar una copia"), imp)));

  ed.append(h("div", { class: "card" }, h("h3", {}, "Sesión"),
    h("button", { class: "btn danger", onclick: function () {
      api("POST", "salir", {}).catch(function () {}).then(function () { location.reload(); });
    } }, "Cerrar sesión")));
}

// ---------------------------------------------------------------- publicar
function publish() {
  var bad = cfg.items.filter(function (i) { return i.kind === "link" && i.url && !R.safeUrl(i.url); });
  var badS = cfg.socials.filter(function (s) { return s.url && !R.safeUrl(s.url); });
  if (bad.length || badS.length) {
    go("enlaces");
    toast("Revisa " + plural(bad.length + badS.length, "dirección que está mal escrita", "direcciones que están mal escritas") + " (en rojo).", { bad: true });
    return;
  }
  var btn = $("publish"); btn.disabled = true; btn.textContent = "Publicando…";
  api("PUT", "config", { config: R.sanitize(cfg) }).then(function (d) {
    publishedJson = JSON.stringify(d.config); publishedAt = d.publishedAt;
    if (d.missing) { history.push(JSON.stringify(cfg)); cfg = clone(d.config); renderTab(); updatePreview(); }
    try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
    if (d.missing) toast(plural(d.missing, "foto ya no existía y se ha quitado", "fotos ya no existían y se han quitado") + ". Súbela otra vez si la quieres.", { bad: true, ms: 9000 });
    else toast("Publicado. Tu página ya está al día.", { link: { href: H.page, label: "Ver" } });
    if (tab === "enlaces") renderTab();
  }, errToast).then(function () { btn.textContent = "Publicar"; updateStatus(); });
}

function copyUrl() {
  var done = function () { toast("Enlace copiado: " + H.page); };
  if (navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(H.page).then(done, fallback); else fallback();
  function fallback() { var t = h("textarea", { style: "position:fixed;opacity:0" }); t.value = H.page; document.body.append(t); t.select(); try { document.execCommand("copy"); done(); } catch (e) {} t.remove(); }
}

// ---------------------------------------------------------------- arranque
function showLogin(setup) {
  $("boot").hidden = true; $("main").hidden = true; $("login").hidden = false;
  var sm = $("setupMsg");
  if (setup) {
    sm.hidden = false; $("loginFields").hidden = true; sm.textContent = "";
    if (setup === "nodb") {
      sm.append(h("b", {}, "Falta conectar la base de datos"),
        H.kind === "php" ? "No puedo crear la carpeta de datos. Revisa que la carpeta donde subiste index.php permita escribir (permisos 755)."
          : h("ol", {}, h("li", {}, "En Cloudflare, ve a Almacenamiento y bases de datos → D1 y crea una base (por ejemplo «vitrina»)."),
            h("li", {}, "Abre tu Worker → Enlaces (Bindings) → Añadir → Base de datos D1."), h("li", {}, ["Ponle de nombre de variable ", h("code", {}, "DB"), " y elige la base que creaste."]),
            h("li", {}, "Guarda y recarga esta página.")));
    } else if (setup === "weak") {
      sm.append(h("b", {}, "La contraseña es demasiado débil"),
        H.kind === "php" ? h("span", {}, ["La contraseña de ", h("code", {}, "CONTRASENA_INICIAL"), " en index.php tiene menos de 10 caracteres. Pon una más larga y recarga esta página."]) :
        h("span", {}, ["El secreto ", h("code", {}, "CONTRASENA"), " de Cloudflare tiene menos de 10 caracteres o es el del ejemplo. En tu Worker → Settings → Variables and Secrets, cámbialo por uno más largo y recarga esta página."]));
    } else {
      sm.append(h("b", {}, "Falta la contraseña"),
        H.kind === "php" ? h("span", {}, ["Abre index.php y escribe tu contraseña entre las comillas de ", h("code", {}, "CONTRASENA_INICIAL"), ". Luego recarga."])
          : h("ol", {}, h("li", {}, "En Cloudflare, abre tu Worker → Ajustes → Variables y secretos."),
            h("li", {}, ["Añade un secreto (tipo «Secret») llamado ", h("code", {}, "CONTRASENA"), " con la contraseña que quieras."]), h("li", {}, "Guarda y recarga esta página.")));
    }
  } else { sm.hidden = true; $("loginFields").hidden = false; setTimeout(function () { $("pass").focus(); }, 30); }
}

function setupLogin() {
  var eye = $("eye"); eye.innerHTML = svg("eye");
  eye.onclick = function () { var p = $("pass"); var show = p.type === "password"; p.type = show ? "text" : "password"; eye.innerHTML = svg(show ? "eyeoff" : "eye"); eye.setAttribute("aria-label", show ? "Ocultar contraseña" : "Mostrar contraseña"); };
  $("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();
    var b = $("loginBtn"), err = $("loginErr"); err.textContent = "";
    if (!$("pass").value) { err.textContent = "Escribe tu contraseña."; return; }
    b.disabled = true; b.textContent = "Entrando…";
    api("POST", "entrar", { password: $("pass").value }).then(function () { $("pass").value = ""; return start(); }, function (e2) { err.textContent = e2.message; })
      .then(function () { b.disabled = false; b.textContent = "Entrar"; });
  });
}

function start() {
  return api("GET", "config").then(function (d) {
    var server = d.config ? R.sanitize(d.config) : null;
    publishedAt = d.publishedAt;
    publishedJson = server ? JSON.stringify(server) : "";
    cfg = server ? clone(server) : R.defaultConfig();
    history = []; future = [];
    var draft = null;
    try { draft = JSON.parse(localStorage.getItem(DRAFT_KEY) || "null"); } catch (e) {}
    draftEl = null;
    if (draft && cfgJson(draft) !== publishedJson && cfgJson(draft) !== cfgJson(cfg)) {
      var bn = h("div", { class: "banner", id: "draftBanner" }, h("span", { class: "grow" }, "Tienes cambios sin publicar de la última vez en este navegador."),
        h("button", { class: "btn sm pri", onclick: function () { change(function () {}); cfg = R.sanitize(draft); draftEl = null; renderTab(); afterChange(); } }, "Recuperarlos"),
        h("button", { class: "btn sm ghost", onclick: function () { draftEl = null; bn.remove(); try { localStorage.removeItem(DRAFT_KEY); } catch (e) {} } }, "Descartar"));
      draftEl = bn;
    }
    $("boot").hidden = true; $("login").hidden = true; $("main").hidden = false;
    $("urlText").textContent = H.page.replace(/^https?:\/\//, "").replace(/\/$/, "");
    $("openUrl").href = $("openUrl2").href = H.page;
    try { var st = sessionStorage.getItem("vitrina-tab"); if (st && TABS.some(function (t) { return t.id === st; })) tab = st; } catch (e) {}
    renderNav(); renderTab(); pvReady = false; updatePreview(); updateStatus();
    loadStats(30).then(function (s) {
      clicks30 = s.links; if (statDays === 30) stats = s;
      if (tab === "enlaces") document.querySelectorAll(".it[data-id]").forEach(function (el) {
        var b = el.querySelector(".badge"), n = clicks30[el.getAttribute("data-id")] || 0; if (b) b.textContent = n ? plural(n, "clic", "clics") : "";
      });
    }, function () {});
  }, function (e) { if ($("main").hidden && $("login").hidden) { $("boot").hidden = true; showLogin(); } errToast(e); });
}

function init() {
  $("undo").innerHTML = svg("undo"); $("redo").innerHTML = svg("redo");
  $("copyUrl").innerHTML = svg("copy"); $("openUrl").innerHTML = svg("ext");
  $("fab").insertAdjacentHTML("afterbegin", svg("phone")); $("fab").append("Vista previa");
  $("undo").onclick = undo; $("redo").onclick = redo; $("publish").onclick = publish; $("copyUrl").onclick = copyUrl;
  $("fab").onclick = function () { $("pv").classList.add("open"); $("pvClose").focus(); };
  $("pvClose").onclick = function () { $("pv").classList.remove("open"); $("fab").focus(); };
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && $("pv").classList.contains("open")) $("pv").classList.remove("open");
    if (!(e.ctrlKey || e.metaKey) || $("main").hidden) return;
    var k = e.key.toLowerCase();
    if (k === "s") { e.preventDefault(); if (!$("publish").disabled) publish(); return; }
    var inText = /^(INPUT|TEXTAREA)$/.test(document.activeElement.tagName) && document.activeElement.type !== "color" && document.activeElement.type !== "range";
    if (inText) return;
    if (k === "z" && !e.shiftKey) { e.preventDefault(); undo(); }
    else if ((k === "z" && e.shiftKey) || k === "y") { e.preventDefault(); redo(); }
  });
  window.addEventListener("beforeunload", function (e) { if (dirty()) { e.preventDefault(); e.returnValue = ""; } });
  setupLogin();
  api("GET", "estado").then(function (s) {
    if (s.setup) { showLogin(s.setup); return; }
    if (!s.logged) { showLogin(); return; }
    return start();
  }).catch(function (e) { $("boot").hidden = true; showLogin(); errToast(e); });
}
init();
})();
</script>
</body>
</html>

VT_PANEL;
  $html = str_replace('__NONCE__', $nonce, $html);
  $html = str_replace('/*__HOST__*/', $host, $html);
  $pos = strpos($html, '/*__RENDER__*/');
  if ($pos !== false) $html = substr_replace($html, vt_render_js(), $pos, strlen('/*__RENDER__*/'));
  vt_common_headers();
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store');
  header('Referrer-Policy: no-referrer');
  header('X-Frame-Options: DENY');
  header('X-Robots-Tag: noindex, nofollow');
  header("Content-Security-Policy: default-src 'none'; script-src 'nonce-$nonce'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' blob: data:; connect-src 'self'; frame-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
  echo $html;
  exit;
}
function vt_placeholder() {
  vt_common_headers();
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store');
  echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Página en preparación</title>'
    . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3efe6;color:#161513;font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;text-align:center;padding:24px;box-sizing:border-box}h1{font-size:22px;margin:0 0 8px}p{margin:0;opacity:.7}a{color:inherit}</style></head>'
    . '<body><div><h1>Esta página está en preparación</h1><p>Si es tuya, entra en <a href="?panel">el panel</a> para montarla.</p></div></body></html>';
  exit;
}
function vt_public() {
  global $VT;
  $cfg = vt_config();
  if (!$cfg) vt_placeholder();
  if ($_SERVER['REQUEST_METHOD'] === 'GET' && !vt_is_bot()) vt_count('v');
  $p = $cfg['profile'];
  $title = $cfg['seo']['title'] !== '' ? $cfg['seo']['title'] : ($p['name'] !== '' ? $p['name'] : 'Mis enlaces');
  $desc = $cfg['seo']['desc'] !== '' ? $cfg['seo']['desc'] : vt_cut(preg_replace('/\s+/u', ' ', $p['bio']), 160);
  $font = $VT['fonts'][$cfg['theme']['font']];
  $fontHref = $font['g'] ? 'https://fonts.googleapis.com/css2?family=' . $font['g'] . '&display=swap' : '';
  $json = json_encode($cfg, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  vt_common_headers();
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: public, max-age=0, must-revalidate');
  header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'");
  $base = vt_page_url();
  echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">';
  echo '<title>' . vt_h($title) . '</title>';
  if ($desc !== '') echo '<meta name="description" content="' . vt_h($desc) . '"><meta property="og:description" content="' . vt_h($desc) . '">';
  echo '<meta property="og:type" content="profile"><meta property="og:title" content="' . vt_h($title) . '">';
  if ($p['avatar']) echo '<meta property="og:image" content="' . vt_h($base . '?i=' . $p['avatar']) . '"><link rel="icon" href="?i=' . vt_h($p['avatar']) . '">';
  echo '<meta name="theme-color" content="' . vt_h($cfg['theme']['bg']) . '">';
  if ($fontHref) echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="' . vt_h($fontHref) . '">';
  echo '<style id="vtcss">body{margin:0;background:' . vt_h($cfg['theme']['bg']) . '}</style></head><body data-hit="?api=hit">';
  // Sin JavaScript se ve al menos la lista de enlaces.
  echo '<noscript><main style="max-width:600px;margin:0 auto;padding:48px 20px;font-family:system-ui,sans-serif;color:' . vt_h($cfg['theme']['text']) . '"><h1>' . vt_h($p['name']) . '</h1>';
  foreach ($cfg['items'] as $i) if (!$i['hidden'] && $i['kind'] === 'link' && $i['url']) echo '<p><a style="color:inherit" href="' . vt_h($i['url']) . '">' . vt_h($i['title'] !== '' ? $i['title'] : $i['url']) . '</a></p>';
  echo '</main></noscript>';
  echo '<script type="application/json" id="vtd">' . $json . '</script><script src="?v=' . substr(md5(vt_render_js()), 0, 10) . '"></script></body></html>';
  exit;
}
function vt_page_script() {
  vt_common_headers();
  header('Content-Type: text/javascript; charset=utf-8');
  header('Cache-Control: public, max-age=86400');
  echo vt_render_js();
  echo "\n(function(){var R=window.VitrinaR;var el=document.getElementById('vtd');if(!el)return;var c=JSON.parse(el.textContent);"
    . "var img=function(id){return '?i='+id};document.getElementById('vtcss').textContent=R.css(R.sanitize(c),img);"
    . "document.body.insertAdjacentHTML('afterbegin',R.inner(R.sanitize(c),{img:img}));})();\n";
  echo <<<'VT_HIT'

(function(){var u=document.body.getAttribute('data-hit');if(!u||!navigator.sendBeacon)return;function s(d){try{navigator.sendBeacon(u,new Blob([JSON.stringify(d)],{type:'text/plain'}))}catch(e){}}document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[data-i],a[data-s]');if(!a)return;if(a.dataset.i)s({t:'c',id:a.dataset.i});else s({t:'s',id:a.dataset.s})},true)})();
VT_HIT;
  exit;
}
function vt_image($id) {
  if (!preg_match('/^[a-f0-9]{24}$/', $id)) { http_response_code(404); exit; }
  $f = vt_dir() . '/img/' . $id . '.img';
  if (!is_file($f)) { http_response_code(404); header('Cache-Control: no-store'); exit; }
  $b = file_get_contents($f);
  $type = vt_image_type($b);
  if (!$type) { http_response_code(404); exit; }
  vt_common_headers();
  header('Content-Type: ' . $type);
  header('Content-Length: ' . strlen($b));
  header('Cache-Control: public, max-age=31536000, immutable');
  header("Content-Security-Policy: default-src 'none'; sandbox");
  echo $b;
  exit;
}

// ------------------------------------------------------------------ entrada
try {
  if (isset($_GET['api']) && is_string($_GET['api'])) vt_api($_GET['api']);
  if (isset($_GET['i']) && is_string($_GET['i'])) vt_image($_GET['i']);
  if (isset($_GET['panel']) || isset($_GET['admin'])) vt_panel();
  if (isset($_GET['v'])) vt_page_script();
  vt_public();
} catch (Throwable $e) {
  if (isset($_GET['api'])) vt_fail('Ha fallado algo en el servidor. Prueba otra vez en un momento.', 500);
  http_response_code(500);
  echo 'Ha fallado algo en el servidor.';
}
