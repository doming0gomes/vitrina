# Vitrina

Tu propia página de enlaces, como Linktree o Beacons, pero tuya y gratis. La pones en la bio de Instagram, TikTok o YouTube.

Tiene dos partes:

- **La página** que ve la gente: tu foto, tu nombre, unas líneas sobre ti, los iconos de tus redes y tus botones.
- **El panel**, con contraseña, donde lo cambias todo y lo ves en directo en un móvil de muestra: subir la foto, añadir, ordenar y ocultar enlaces, elegir una de las 10 plantillas o cambiar colores, fondo (color, degradado o foto), letra y forma de los botones. También cuenta las visitas y los clics de cada enlace, sin cookies.

Sin anuncios, sin «hecho con…» al pie y sin pagar por quitarlo. Tus datos se quedan en tu cuenta.

## ¿Qué camino elijo?

| Si… | Usa | Tiempo | Coste |
|---|---|---|---|
| No tienes web (lo normal) | **A · Cloudflare con un botón** | 5 minutos | Gratis, sin tarjeta |
| No quieres crear una cuenta de GitHub | **B · Cloudflare copiando y pegando** | 10 minutos | Gratis, sin tarjeta |
| Ya tienes una web con hosting (WordPress, Hostinger, IONOS…) | **C · Un archivo en tu web** | 5 minutos | Lo que ya pagas |

¿Por qué Cloudflare? Su plan gratuito da 100.000 peticiones al día (miles de visitas) y no pide tarjeta. Firebase, en cambio, ya exige el plan de pago para guardar fotos.

---

## A · Cloudflare con un botón

Necesitas una cuenta gratuita de [Cloudflare](https://dash.cloudflare.com/sign-up) y otra de [GitHub](https://github.com/signup). En GitHub se guarda una copia del código a tu nombre, y así podrás actualizarlo.

1. Pulsa este botón:

   [![Deploy to Cloudflare](https://deploy.workers.cloudflare.com/button)](https://deploy.workers.cloudflare.com/?url=https://github.com/doming0gomes/vitrina)

2. Entra con tu cuenta de Cloudflare y conecta tu GitHub cuando te lo pida.
3. En **CONTRASENA**, escribe la contraseña de tu panel: al menos 10 caracteres (mejor 14 o más) y que no uses en otro sitio. Lo demás déjalo como está. La base de datos se crea sola.
4. Pulsa **Create and deploy** y espera uno o dos minutos.
5. Te da una dirección del tipo `https://vitrina.tu-nombre.workers.dev`. Esa es tu página.
6. Entra en esa dirección añadiendo **/admin** al final (`https://vitrina.tu-nombre.workers.dev/admin`), escribe tu contraseña y monta tu página. Cuando termines, pulsa **Publicar**.

## B · Cloudflare copiando y pegando (sin GitHub)

Necesitas una cuenta gratuita de [Cloudflare](https://dash.cloudflare.com/sign-up) y el archivo `descargas/vitrina-cloudflare.js` de esta carpeta.

1. **Crea el Worker.** En el panel de Cloudflare: **Workers & Pages** → **Create application** → **Start with Hello World!** → **Get started**. Ponle de nombre `vitrina` y pulsa **Deploy**.
2. **Pega el código.** Abre tu Worker y pulsa el icono **Edit code** (**</>**). Borra todo lo que hay en `worker.js`, abre `vitrina-cloudflare.js` con el Bloc de notas (Windows) o TextEdit (Mac), copia **todo** y pégalo. Pulsa **Deploy** (o **Save**).
3. **Crea la base de datos.** En el menú de la izquierda: **Storage & Databases** → **D1 SQL Database** → **Create Database**. Ponle de nombre `vitrina` y pulsa **Create**.
4. **Conéctala.** Vuelve a tu Worker → pestaña **Bindings** → **Add binding** → **D1 database** → **Add binding**. En *Variable name* escribe `DB` (en mayúsculas), elige la base `vitrina` y pulsa **Add binding**.
5. **Pon tu contraseña.** En tu Worker → **Settings** → **Variables and Secrets** → **Add**. Tipo **Secret**, nombre `CONTRASENA` (sin tilde ni ñ), y en el valor tu contraseña (al menos 10 caracteres). Pulsa **Deploy**.
6. Abre la dirección que te da Cloudflare (`https://vitrina.tu-nombre.workers.dev`) con **/admin** al final, entra con tu contraseña y monta tu página.

Si te equivocas en algún paso, el panel te dice qué falta.

## C · Un archivo en tu web (si ya tienes hosting)

Sirve en cualquier hosting con PHP 7.4 o más nuevo, que es casi cualquiera.

1. Abre `descargas/index.php` con el Bloc de notas o TextEdit. Arriba del todo verás:
   ```php
   const CONTRASENA_INICIAL = '';
   ```
   Escribe tu contraseña entre las comillas (al menos 10 caracteres) y guarda. No uses comillas simples (`'`) ni la barra `\` dentro de la contraseña.
2. En el gestor de archivos de tu hosting (o por FTP), crea una carpeta en tu web, por ejemplo `enlaces`, y sube ahí el `index.php`.
3. Tu página queda en `https://tuweb.com/enlaces/` y el panel en `https://tuweb.com/enlaces/?panel`.

Vitrina crea sola, al lado del archivo, una carpeta `vitrina-datos` con tu página, tus fotos y las estadísticas. Si alguna vez cambias de hosting, copia también esa carpeta (o usa **Ajustes → Descargar copia**).

---

## Usar tu propio dominio (opcional)

- **Cloudflare (A o B):** si tu dominio ya está en Cloudflare, en tu Worker → **Settings** → **Domains & Routes** → **Add** → **Custom domain** y escribe, por ejemplo, `enlaces.tudominio.com`. Si tu dominio está en otro sitio, tendrías que pasarlo a Cloudflare antes. La dirección `workers.dev` sigue funcionando gratis.
- **Hosting (C):** ya estás en tu dominio.

## El panel, en cinco cosas

- **Enlaces:** pega una dirección (`tuweb.com`, un correo o un teléfono) y Vitrina la completa sola. Arrastra los botones para ordenarlos, ocúltalos con el interruptor sin borrarlos, añade una miniatura, un texto pequeño debajo o haz que uno se mueva para llamar la atención.
- **Redes:** elige las tuyas y escribe tu @. Salen como iconos arriba o abajo.
- **Diseño:** 10 plantillas, y luego retocas el fondo, los botones y la letra. Si eliges colores que se leen mal, te avisa.
- **Estadísticas:** visitas por día, clics en cada enlace y en cada red, de los últimos 7, 30 o 90 días.
- **Ajustes:** cambiar la contraseña y descargar o restaurar una copia de seguridad.

Puedes deshacer (Ctrl+Z) y nada cambia en tu página hasta que pulsas **Publicar** (o Ctrl+S). Si cierras el navegador sin publicar, la próxima vez te ofrece recuperar los cambios.

## Preguntas

**¿He olvidado la contraseña?**
En Cloudflare, cambia el secreto `CONTRASENA` (paso 5 de B) y entra con el nuevo. En el hosting, escribe en `CONTRASENA_INICIAL` (dentro de `index.php`) una contraseña distinta de la que había. En los dos casos se cierran todas las sesiones abiertas, así que también sirve si crees que alguien ha entrado.

**¿Qué guarda de quien visita mi página?**
Nada personal: ni cookies ni IPs. Solo suma uno al contador del día cuando alguien entra o toca un enlace. Los robots y la vista previa del panel no cuentan. Tus propias visitas a la página, sí. (La única excepción es quien falla la contraseña del panel: se guarda un código irreversible de su conexión durante un día, para frenar a quien intente adivinarla.)

**¿Y las letras de Google?**
Si eliges una letra que no sea «Sistema», la página la carga de Google Fonts, como casi todas las webs. Si no quieres eso, elige «Sistema».

**¿Cuánto aguanta gratis?**
En Cloudflare, 100.000 peticiones al día. Cada visita gasta una, más una por cada foto que vea por primera vez y una por cada clic, así que son decenas de miles de visitas al día. Si algún día las pasas, la página da error hasta la medianoche (hora UTC). La base de datos gratis guarda 500 MB, y tu página con fotos ocupa unos pocos MB.

**¿Es segura?**
La contraseña se guarda cifrada (con hash) si la cambias desde el panel, y tras 8 intentos fallidos desde la misma conexión se bloquea 15 minutos (por eso conviene una larga, de 14 caracteres o más). La sesión va en una cookie que no puede leer ningún script. Solo se aceptan enlaces web (`https://` o `http://`), de correo (`mailto:`) y de teléfono (`tel:`), y las fotos se comprueban antes de guardarlas.

---

Hecha por [new3sc](https://www.instagram.com/new3sc) con Claude.
