// Vitrina — tu página de enlaces. Pega TODO este archivo en el editor de tu Worker de Cloudflare.
// Necesita: una base de datos D1 conectada con el nombre DB y un secreto llamado CONTRASENA.
var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/render.js
function vitrinaRenderer() {
  "use strict";
  var ICONS = { "bluesky": "M5.202 2.857C7.954 4.922 10.913 9.11 12 11.358c1.087-2.247 4.046-6.436 6.798-8.501C20.783 1.366 24 .213 24 3.883c0 .732-.42 6.156-.667 7.037-.856 3.061-3.978 3.842-6.755 3.37 4.854.826 6.089 3.562 3.422 6.299-5.065 5.196-7.28-1.304-7.847-2.97-.104-.305-.152-.448-.153-.327 0-.121-.05.022-.153.327-.568 1.666-2.782 8.166-7.847 2.97-2.667-2.737-1.432-5.473 3.422-6.3-2.777.473-5.899-.308-6.755-3.369C.42 10.04 0 4.615 0 3.883c0-3.67 3.217-2.517 5.202-1.026", "discord": "M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z", "facebook": "M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z", "github": "M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12", "instagram": "M7.0301.084c-1.2768.0602-2.1487.264-2.911.5634-.7888.3075-1.4575.72-2.1228 1.3877-.6652.6677-1.075 1.3368-1.3802 2.127-.2954.7638-.4956 1.6365-.552 2.914-.0564 1.2775-.0689 1.6882-.0626 4.947.0062 3.2586.0206 3.6671.0825 4.9473.061 1.2765.264 2.1482.5635 2.9107.308.7889.72 1.4573 1.388 2.1228.6679.6655 1.3365 1.0743 2.1285 1.38.7632.295 1.6361.4961 2.9134.552 1.2773.056 1.6884.069 4.9462.0627 3.2578-.0062 3.668-.0207 4.9478-.0814 1.28-.0607 2.147-.2652 2.9098-.5633.7889-.3086 1.4578-.72 2.1228-1.3881.665-.6682 1.0745-1.3378 1.3795-2.1284.2957-.7632.4966-1.636.552-2.9124.056-1.2809.0692-1.6898.063-4.948-.0063-3.2583-.021-3.6668-.0817-4.9465-.0607-1.2797-.264-2.1487-.5633-2.9117-.3084-.7889-.72-1.4568-1.3876-2.1228C21.2982 1.33 20.628.9208 19.8378.6165 19.074.321 18.2017.1197 16.9244.0645 15.6471.0093 15.236-.005 11.977.0014 8.718.0076 8.31.0215 7.0301.0839m.1402 21.6932c-1.17-.0509-1.8053-.2453-2.2287-.408-.5606-.216-.96-.4771-1.3819-.895-.422-.4178-.6811-.8186-.9-1.378-.1644-.4234-.3624-1.058-.4171-2.228-.0595-1.2645-.072-1.6442-.079-4.848-.007-3.2037.0053-3.583.0607-4.848.05-1.169.2456-1.805.408-2.2282.216-.5613.4762-.96.895-1.3816.4188-.4217.8184-.6814 1.3783-.9003.423-.1651 1.0575-.3614 2.227-.4171 1.2655-.06 1.6447-.072 4.848-.079 3.2033-.007 3.5835.005 4.8495.0608 1.169.0508 1.8053.2445 2.228.408.5608.216.96.4754 1.3816.895.4217.4194.6816.8176.9005 1.3787.1653.4217.3617 1.056.4169 2.2263.0602 1.2655.0739 1.645.0796 4.848.0058 3.203-.0055 3.5834-.061 4.848-.051 1.17-.245 1.8055-.408 2.2294-.216.5604-.4763.96-.8954 1.3814-.419.4215-.8181.6811-1.3783.9-.4224.1649-1.0577.3617-2.2262.4174-1.2656.0595-1.6448.072-4.8493.079-3.2045.007-3.5825-.006-4.848-.0608M16.953 5.5864A1.44 1.44 0 1 0 18.39 4.144a1.44 1.44 0 0 0-1.437 1.4424M5.8385 12.012c.0067 3.4032 2.7706 6.1557 6.173 6.1493 3.4026-.0065 6.157-2.7701 6.1506-6.1733-.0065-3.4032-2.771-6.1565-6.174-6.1498-3.403.0067-6.156 2.771-6.1496 6.1738M8 12.0077a4 4 0 1 1 4.008 3.9921A3.9996 3.9996 0 0 1 8 12.0077", "kick": "M1.333 0h8v5.333H12V2.667h2.667V0h8v8H20v2.667h-2.667v2.666H20V16h2.667v8h-8v-2.667H12v-2.666H9.333V24h-8Z", "patreon": "M22.957 7.21c-.004-3.064-2.391-5.576-5.191-6.482-3.478-1.125-8.064-.962-11.384.604C2.357 3.231 1.093 7.391 1.046 11.54c-.039 3.411.302 12.396 5.369 12.46 3.765.047 4.326-4.804 6.068-7.141 1.24-1.662 2.836-2.132 4.801-2.618 3.376-.836 5.678-3.501 5.673-7.031Z", "pinterest": "M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z", "snapchat": "M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z", "spotify": "M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z", "telegram": "M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z", "threads": "M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.879-6.43 2.525-8.482C5.845 1.205 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 0 1 3.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.308-.883-2.359-.89h-.029c-.844 0-1.992.232-2.721 1.32L7.734 7.847c.98-1.454 2.568-2.256 4.478-2.256h.044c3.194.02 5.097 1.975 5.287 5.388.108.046.216.094.321.142 1.49.7 2.58 1.761 3.154 3.07.797 1.82.871 4.79-1.548 7.158-1.85 1.81-4.094 2.628-7.277 2.65Zm1.003-11.69c-.242 0-.487.007-.739.021-1.836.103-2.98.946-2.916 2.143.067 1.256 1.452 1.839 2.784 1.767 1.224-.065 2.818-.543 3.086-3.71a10.5 10.5 0 0 0-2.215-.221z", "tiktok": "M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z", "twitch": "M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z", "whatsapp": "M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z", "x": "M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z", "youtube": "M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z", "linkedin": "M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z", "email": "M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm1 2.3V17h16V7.3l-8 5.6-8-5.6zM5.6 7l6.4 4.5L18.4 7H5.6z", "web": "M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.9 6h-2.9a15.7 15.7 0 0 0-1.3-3.6A8 8 0 0 1 18.9 8zM12 4.1c.8 1.1 1.5 2.4 1.9 3.9h-3.8c.4-1.5 1.1-2.8 1.9-3.9zM4.3 14a8.2 8.2 0 0 1 0-4h3.3a16.5 16.5 0 0 0 0 4H4.3zm.8 2h2.9c.3 1.3.8 2.5 1.3 3.6A8 8 0 0 1 5.1 16zM8 8H5.1a8 8 0 0 1 4.2-3.6C8.8 5.5 8.3 6.7 8 8zm4 11.9c-.8-1.1-1.5-2.4-1.9-3.9h3.8c-.4 1.5-1.1 2.8-1.9 3.9zM14.3 14H9.7a14.7 14.7 0 0 1 0-4h4.6a14.7 14.7 0 0 1 0 4zm.4 5.6c.5-1.1 1-2.3 1.3-3.6h2.9a8 8 0 0 1-4.2 3.6zM16.4 14a16.5 16.5 0 0 0 0-4h3.3a8.2 8.2 0 0 1 0 4h-3.3z" };
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
    discord: { label: "Discord", base: "https://discord.gg/", hint: "c\xF3digo de invitaci\xF3n" },
    telegram: { label: "Telegram", base: "https://t.me/", hint: "@usuario o canal" },
    whatsapp: { label: "WhatsApp", base: "https://wa.me/", hint: "n\xFAmero con prefijo, p. ej. 34600111222" },
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
  var TEMPLATES = {
    papel: { label: "Papel", font: "dmsans", bgType: "solid", bg: "#f3efe6", bg2: "#e6dfcf", angle: 160, text: "#161513", btnStyle: "fill", btnShape: "pill", btnBg: "#161513", btnText: "#f3efe6" },
    noche: { label: "Noche", font: "inter", bgType: "solid", bg: "#0f1013", bg2: "#1c1e24", angle: 160, text: "#f2f2f0", btnStyle: "outline", btnShape: "rounded", btnBg: "#f2f2f0", btnText: "#0f1013" },
    lima: { label: "Lima", font: "grotesk", bgType: "solid", bg: "#d4f45a", bg2: "#b9e23a", angle: 160, text: "#121212", btnStyle: "shadow", btnShape: "rounded", btnBg: "#ffffff", btnText: "#121212" },
    cristal: { label: "Cristal", font: "outfit", bgType: "gradient", bg: "#2d1f8f", bg2: "#9c2a6b", angle: 150, text: "#ffffff", btnStyle: "glass", btnShape: "pill", btnBg: "#ffffff", btnText: "#ffffff" },
    editorial: { label: "Editorial", font: "fraunces", bgType: "solid", bg: "#f7f3ec", bg2: "#ede6d8", angle: 160, text: "#1b1a17", btnStyle: "line", btnShape: "square", btnBg: "#1b1a17", btnText: "#f7f3ec" },
    oceano: { label: "Oc\xE9ano", font: "inter", bgType: "gradient", bg: "#0b2545", bg2: "#17577f", angle: 170, text: "#eaf2fa", btnStyle: "soft", btnShape: "rounded", btnBg: "#eaf2fa", btnText: "#0b2545" },
    chicle: { label: "Chicle", font: "bricolage", bgType: "solid", bg: "#ffe3ec", bg2: "#ffc9da", angle: 160, text: "#3a1024", btnStyle: "fill", btnShape: "pill", btnBg: "#d6286a", btnText: "#ffffff" },
    terminal: { label: "Terminal", font: "mono", bgType: "solid", bg: "#0a0f0a", bg2: "#122012", angle: 160, text: "#7cff8a", btnStyle: "outline", btnShape: "square", btnBg: "#7cff8a", btnText: "#0a0f0a" },
    arcilla: { label: "Arcilla", font: "syne", bgType: "gradient", bg: "#b34a24", bg2: "#a8431f", angle: 200, text: "#fff6ef", btnStyle: "fill", btnShape: "rounded", btnBg: "#fff6ef", btnText: "#7a2c12" },
    museo: { label: "Museo", font: "playfair", bgType: "solid", bg: "#ffffff", bg2: "#f2f2f2", angle: 160, text: "#111111", btnStyle: "outline", btnShape: "square", btnBg: "#111111", btnText: "#ffffff" }
  };
  var BTN_STYLES = ["fill", "outline", "shadow", "glass", "soft", "line"];
  var BTN_SHAPES = ["pill", "rounded", "square"];
  var BG_TYPES = ["solid", "gradient", "image"];
  var SHAPES = ["circle", "rounded", "square"];
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function(c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  __name(esc, "esc");
  function str(v, max) {
    if (typeof v !== "string") return "";
    v = v.replace(/[\x00-\x09\x0b-\x1f\x7f\u2028\u2029]/g, "").replace(WS_EDGE, "");
    var a = Array.from(v);
    return a.length > max ? a.slice(0, max).join("") : v;
  }
  __name(str, "str");
  function oneOf(v, list, def) {
    return list.indexOf(v) >= 0 ? v : def;
  }
  __name(oneOf, "oneOf");
  function hex(v, def) {
    return typeof v === "string" && /^#[0-9a-fA-F]{6}$/.test(v) ? v.toLowerCase() : def;
  }
  __name(hex, "hex");
  function int(v, min, max, def) {
    if (typeof v === "string" && /^-?\d+(\.\d+)?$/.test(v)) v = Number(v);
    if (typeof v !== "number" || !isFinite(v)) return def;
    return Math.max(min, Math.min(max, Math.round(v)));
  }
  __name(int, "int");
  var WS = "	\n\v\f\r \xA0\u1680\u2000-\u200A\u2028\u2029\u202F\u205F\u3000\uFEFF";
  var WS_ANY = new RegExp("[" + WS + "]");
  var WS_EDGE = new RegExp("^[" + WS + "]+|[" + WS + "]+$", "g");
  function imgId(v) {
    return typeof v === "string" && /^[a-f0-9]{24}$/.test(v) ? v : "";
  }
  __name(imgId, "imgId");
  function itemId(v) {
    return typeof v === "string" && /^[a-z0-9]{4,16}$/.test(v) ? v : "";
  }
  __name(itemId, "itemId");
  function arr(v) {
    return Array.isArray(v) ? v : [];
  }
  __name(arr, "arr");
  function safeUrl(u) {
    if (typeof u !== "string") return "";
    u = u.replace(WS_EDGE, "");
    if (!u || u.length > 2e3 || WS_ANY.test(u)) return "";
    if (/^https?:\/\/[^"<>\\`]+$/i.test(u)) {
      var host = u.replace(/^https?:\/\//i, "").split(/[\/?#]/)[0].replace(/^[^@]*@/, "").split(":")[0];
      return /^[^.]+(\.[^.]+)+$/.test(host) || host === "localhost" ? u : "";
    }
    if (/^mailto:[^"<>\\`@]+@[^"<>\\`@]+\.[^"<>\\`@?]+(\?[^"<>\\`]*)?$/i.test(u)) return u;
    if (/^tel:\+?[0-9][0-9\-.()]{2,30}$/i.test(u)) return u;
    return "";
  }
  __name(safeUrl, "safeUrl");
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
  __name(socialUrl, "socialUrl");
  function normalizeUrl(input) {
    var v = String(input || "").trim();
    if (!v) return "";
    if (/^(https?:|mailto:|tel:)/i.test(v)) return v;
    if (/^\+?[\d\s\-().]{6,}$/.test(v)) return "tel:" + v.replace(/[\s]/g, "");
    if (/^@|[\s,]/.test(v)) return v;
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return "mailto:" + v;
    return "https://" + v.replace(/^\/+/, "");
  }
  __name(normalizeUrl, "normalizeUrl");
  function defaultTheme(name) {
    var t = TEMPLATES[name] || TEMPLATES.papel;
    return {
      template: TEMPLATES[name] ? name : "papel",
      font: t.font,
      bgType: t.bgType,
      bg: t.bg,
      bg2: t.bg2,
      angle: t.angle,
      bgImage: "",
      overlay: 35,
      text: t.text,
      btnStyle: t.btnStyle,
      btnShape: t.btnShape,
      btnBg: t.btnBg,
      btnText: t.btnText
    };
  }
  __name(defaultTheme, "defaultTheme");
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
  __name(defaultConfig, "defaultConfig");
  function sanitize(c) {
    c = c && typeof c === "object" ? c : {};
    var d = defaultConfig();
    var p = c.profile && typeof c.profile === "object" ? c.profile : {};
    var t = c.theme && typeof c.theme === "object" ? c.theme : {};
    var base = defaultTheme(oneOf(t.template, Object.keys(TEMPLATES), "papel"));
    var seen = /* @__PURE__ */ Object.create(null);
    function uniq(id) {
      if (!id || seen[id]) return "";
      seen[id] = 1;
      return id;
    }
    __name(uniq, "uniq");
    var o = {
      v: 1,
      profile: {
        name: str(p.name, 60),
        bio: str(p.bio, 300),
        avatar: imgId(p.avatar),
        shape: oneOf(p.shape, SHAPES, "circle")
      },
      socials: arr(c.socials).slice(0, 20).map(function(s) {
        s = s && typeof s === "object" ? s : {};
        return { id: uniq(itemId(s.id)), type: oneOf(s.type, Object.keys(SOCIALS), ""), url: safeUrl(s.url) };
      }).filter(function(s) {
        return s.id && s.type;
      }),
      socialsPos: oneOf(c.socialsPos, ["top", "bottom"], "top"),
      items: arr(c.items).slice(0, 100).map(function(i) {
        i = i && typeof i === "object" ? i : {};
        var kind = oneOf(i.kind, ["link", "header"], "link");
        return {
          id: uniq(itemId(i.id)),
          kind,
          title: str(i.title, 100),
          url: kind === "link" ? safeUrl(i.url) : "",
          sub: kind === "link" ? str(i.sub, 140) : "",
          thumb: kind === "link" ? imgId(i.thumb) : "",
          hidden: i.hidden === true,
          hl: kind === "link" && i.hl === true
        };
      }).filter(function(i) {
        return i.id;
      }),
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
  __name(sanitize, "sanitize");
  function usedImages(c) {
    var out = [];
    if (c.profile.avatar) out.push(c.profile.avatar);
    if (c.theme.bgImage) out.push(c.theme.bgImage);
    c.items.forEach(function(i) {
      if (i.thumb) out.push(i.thumb);
    });
    return out;
  }
  __name(usedImages, "usedImages");
  function fontHref(fontId) {
    var f = FONTS[fontId];
    return f && f.g ? "https://fonts.googleapis.com/css2?family=" + f.g + "&display=swap" : "";
  }
  __name(fontHref, "fontHref");
  function icon(type) {
    var d = ICONS[type] || ICONS.web;
    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="' + d + '"/></svg>';
  }
  __name(icon, "icon");
  function hexToRgb(h) {
    var n = parseInt(h.slice(1), 16);
    return [n >> 16 & 255, n >> 8 & 255, n & 255];
  }
  __name(hexToRgb, "hexToRgb");
  function rgba(h, a) {
    var c = hexToRgb(h);
    return "rgba(" + c[0] + "," + c[1] + "," + c[2] + "," + a + ")";
  }
  __name(rgba, "rgba");
  function isDark(h) {
    var c = hexToRgb(h);
    return (c[0] * 299 + c[1] * 587 + c[2] * 114) / 1e3 < 140;
  }
  __name(isDark, "isDark");
  function css(c, img) {
    var t = c.theme;
    var font = (FONTS[t.font] || FONTS.sistema).css;
    var radius = { pill: "999px", rounded: "16px", square: "4px" }[t.btnShape];
    var thumbR = { pill: "999px", rounded: "10px", square: "2px" }[t.btnShape];
    var bgLayer;
    if (t.bgType === "gradient") bgLayer = "linear-gradient(" + t.angle + "deg," + t.bg + "," + t.bg2 + ")";
    else if (t.bgType === "image" && t.bgImage) {
      var ov = (t.overlay / 100).toFixed(2);
      bgLayer = "linear-gradient(rgba(0,0,0," + ov + "),rgba(0,0,0," + ov + ')),url("' + img(t.bgImage) + '") center/cover no-repeat,' + t.bg;
    } else bgLayer = t.bg;
    var btn;
    switch (t.btnStyle) {
      case "outline":
        btn = ".b{border:2px solid " + t.btnBg + ";color:" + t.btnBg + ";background:transparent}.b:hover{background:" + t.btnBg + ";color:" + t.btnText + "}";
        break;
      case "shadow":
        btn = ".b{background:" + t.btnBg + ";color:" + t.btnText + ";border:2px solid " + t.text + ";box-shadow:4px 4px 0 " + t.text + "}.b:hover{transform:translate(-2px,-2px);box-shadow:6px 6px 0 " + t.text + "}.b:active{transform:translate(2px,2px);box-shadow:2px 2px 0 " + t.text + "}";
        break;
      case "glass":
        btn = ".b{background:" + rgba(t.btnBg, 0.16) + ";color:" + t.btnText + ";border:1px solid " + rgba(t.btnBg, 0.32) + ";-webkit-backdrop-filter:blur(14px) saturate(1.4);backdrop-filter:blur(14px) saturate(1.4)}.b:hover{background:" + rgba(t.btnBg, 0.26) + "}";
        break;
      case "soft":
        btn = ".b{background:" + rgba(t.btnBg, 0.14) + ";color:" + t.text + ";border:1px solid " + rgba(t.btnBg, 0.12) + "}.b:hover{background:" + rgba(t.btnBg, 0.22) + "}";
        break;
      case "line":
        btn = ".l{gap:0}.b,.b.wt{justify-content:space-between;text-align:left;padding:18px 2px;min-height:0;border-bottom:1px solid " + rgba(t.text, 0.22) + ";color:" + t.text + ";font-weight:500;font-size:17px;border-radius:0!important}.b:first-child{border-top:1px solid " + rgba(t.text, 0.22) + "}.b::after{content:'\\2192';font-size:18px;transition:transform .2s ease;margin-left:12px}.b:hover{transform:none}.b:hover::after{transform:translateX(4px)}.b .th{position:static;transform:none;margin-right:14px;flex:none}.b .tx{flex:1}.l .h{text-align:left;align-self:flex-start;margin:26px 0 8px}";
        break;
      default:
        btn = ".b{background:" + t.btnBg + ";color:" + t.btnText + "}.b:hover{background:" + rgba(t.btnBg, 0.88) + "}";
    }
    return "*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}html{background:" + t.bg + ";-webkit-text-size-adjust:100%}body{min-height:100vh;min-height:100dvh;font-family:" + font + ";color:" + t.text + ";-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility}body::before{content:'';position:fixed;inset:0;z-index:-1;background:" + bgLayer + "}.w{max-width:600px;margin:0 auto;padding:56px 20px 40px;display:flex;flex-direction:column;align-items:center;text-align:center}.av{width:104px;height:104px;object-fit:cover;display:block;background:" + rgba(t.text, 0.08) + "}.ini{width:104px;height:104px;display:grid;place-items:center;font-size:42px;font-weight:700;background:" + t.btnBg + ";color:" + t.btnText + "}.circle{border-radius:50%}.rounded{border-radius:28px}.square{border-radius:6px}h1{font-size:23px;font-weight:700;line-height:1.25;letter-spacing:-.01em;margin-top:18px;overflow-wrap:anywhere}.bio{margin-top:8px;font-size:15.5px;line-height:1.5;opacity:.8;max-width:460px;overflow-wrap:anywhere}.s{display:flex;flex-wrap:wrap;justify-content:center;gap:2px;margin-top:18px;max-width:100%}.s.bot{margin-top:32px}.s a{width:42px;height:42px;display:grid;place-items:center;border-radius:50%;color:" + t.text + ";transition:transform .18s ease,background .18s ease}.s a:hover{transform:translateY(-2px);background:" + rgba(t.text, 0.08) + "}.s svg{width:23px;height:23px;fill:currentColor}.l{width:100%;display:flex;flex-direction:column;gap:14px;margin-top:30px}.b{position:relative;display:flex;align-items:center;justify-content:center;min-height:60px;padding:12px 24px;border-radius:" + radius + ";text-decoration:none;font-weight:600;font-size:16px;line-height:1.3;overflow-wrap:anywhere;transition:transform .18s cubic-bezier(.2,.8,.2,1),background .18s ease,box-shadow .18s ease,color .18s ease;-webkit-tap-highlight-color:transparent}.b:hover{transform:translateY(-2px)}.b:active{transform:scale(.985)}.b:focus-visible,.s a:focus-visible{outline:2px solid " + t.text + ";outline-offset:3px}.b .th{position:absolute;left:8px;top:50%;transform:translateY(-50%);width:44px;height:44px;object-fit:cover;border-radius:" + thumbR + "}.b.wt{padding:12px 60px}.b small{display:block;font-weight:400;font-size:13.5px;opacity:.75;margin-top:3px}.h{font-size:12.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;opacity:.85;margin:18px 0 0}.h:first-child{margin-top:0}.hl{animation:vt-hl 4s ease-in-out infinite}@keyframes vt-hl{0%,86%,100%{transform:none}89%{transform:rotate(-1.6deg) scale(1.02)}92%{transform:rotate(1.6deg) scale(1.02)}95%{transform:rotate(-.8deg)}}.vac{opacity:.6;font-size:14px;margin-top:30px}" + btn + "@media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}";
  }
  __name(css, "css");
  function inner(c, o) {
    var p = c.profile;
    var img = o.img;
    var h = '<main class="w">';
    if (p.avatar) h += '<img class="av ' + p.shape + '" src="' + esc(img(p.avatar)) + '" alt="" width="104" height="104">';
    else if (p.name) h += '<div class="ini ' + p.shape + '" aria-hidden="true">' + esc(Array.from(p.name)[0].toUpperCase()) + "</div>";
    if (p.name) h += "<h1>" + esc(p.name) + "</h1>";
    if (p.bio) h += '<p class="bio">' + esc(p.bio).replace(/\n/g, "<br>") + "</p>";
    var soc = c.socials.filter(function(s) {
      return safeUrl(s.url);
    });
    var socHtml = "";
    if (soc.length) {
      socHtml = '<nav class="s' + (c.socialsPos === "bottom" ? " bot" : "") + '" aria-label="Redes">';
      soc.forEach(function(s) {
        var label = (SOCIALS[s.type] || SOCIALS.web).label;
        socHtml += '<a href="' + esc(safeUrl(s.url)) + '" data-s="' + esc(s.type) + '" aria-label="' + esc(label) + '" title="' + esc(label) + '" rel="noopener me">' + icon(s.type) + "</a>";
      });
      socHtml += "</nav>";
    }
    if (c.socialsPos !== "bottom") h += socHtml;
    var vis = c.items.filter(function(i) {
      return !i.hidden && (i.kind === "header" ? i.title : safeUrl(i.url));
    });
    if (vis.length) {
      h += '<div class="l">';
      vis.forEach(function(i) {
        if (i.kind === "header") {
          h += '<h2 class="h">' + esc(i.title) + "</h2>";
          return;
        }
        var u = safeUrl(i.url);
        var title = i.title || u.replace(/^(https?:\/\/|mailto:|tel:)/i, "").replace(/\/$/, "");
        h += '<a class="b' + (i.thumb ? " wt" : "") + (i.hl ? " hl" : "") + '" href="' + esc(u) + '" data-i="' + esc(i.id) + '" rel="noopener">';
        if (i.thumb) h += '<img class="th" src="' + esc(img(i.thumb)) + '" alt="" width="44" height="44" loading="lazy">';
        h += '<span class="tx">' + esc(title) + (i.sub ? "<small>" + esc(i.sub) + "</small>" : "") + "</span></a>";
      });
      h += "</div>";
    } else if (o.preview) {
      h += '<p class="vac">Aqu\xED saldr\xE1n tus enlaces.</p>';
    }
    if (c.socialsPos === "bottom") h += socHtml;
    h += "</main>";
    return h;
  }
  __name(inner, "inner");
  var PREVIEW_CSS = "a{pointer-events:none}html{scrollbar-width:none}::-webkit-scrollbar{display:none}";
  function pageTitle(c) {
    return c.seo.title || c.profile.name || "Mis enlaces";
  }
  __name(pageTitle, "pageTitle");
  function pageDesc(c) {
    return c.seo.desc || c.profile.bio.replace(/\s+/g, " ").slice(0, 160);
  }
  __name(pageDesc, "pageDesc");
  function renderPage(c, o) {
    c = sanitize(c);
    o = o || {};
    var img = o.img || function(id) {
      return "/i/" + id;
    };
    var href = fontHref(c.theme.font);
    var title = pageTitle(c), desc = pageDesc(c);
    var h = '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>' + esc(title) + "</title>" + (desc ? '<meta name="description" content="' + esc(desc) + '">' : "") + '<meta property="og:type" content="profile"><meta property="og:title" content="' + esc(title) + '">' + (desc ? '<meta property="og:description" content="' + esc(desc) + '">' : "") + (c.profile.avatar && o.absImg ? '<meta property="og:image" content="' + esc(o.absImg(c.profile.avatar)) + '"><link rel="icon" href="' + esc(img(c.profile.avatar)) + '">' : "") + '<meta name="theme-color" content="' + c.theme.bg + '"><meta name="color-scheme" content="' + (isDark(c.theme.bg) ? "dark" : "light") + '">' + (href ? '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="' + esc(href) + '">' : "") + "<style>" + css(c, img) + (o.preview ? PREVIEW_CSS : "") + "</style></head><body" + (o.hit ? ' data-hit="' + esc(o.hit) + '"' : "") + ">" + inner(c, { img, preview: o.preview }) + (o.inlineScript ? "<script>" + HIT_SCRIPT + "<\/script>" : "") + "</body></html>";
    return h;
  }
  __name(renderPage, "renderPage");
  var HIT_SCRIPT = "(function(){var u=document.body.getAttribute('data-hit');if(!u||!navigator.sendBeacon)return;function s(d){try{navigator.sendBeacon(u,new Blob([JSON.stringify(d)],{type:'text/plain'}))}catch(e){}}document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[data-i],a[data-s]');if(!a)return;if(a.dataset.i)s({t:'c',id:a.dataset.i});else s({t:'s',id:a.dataset.s})},true)})();";
  return {
    SOCIALS,
    FONTS,
    TEMPLATES,
    ICONS,
    BTN_STYLES,
    BTN_SHAPES,
    esc,
    safeUrl,
    socialUrl,
    normalizeUrl,
    sanitize,
    defaultConfig,
    defaultTheme,
    usedImages,
    renderPage,
    css,
    inner,
    fontHref,
    icon,
    isDark,
    pageTitle,
    PREVIEW_CSS,
    pageDesc,
    HIT_SCRIPT
  };
}
__name(vitrinaRenderer, "vitrinaRenderer");

// src/worker.js
import PANEL_HTML from "./e78a694fca5b487cfcd300f1ffa293fa5c38d3c4-panel.html";
var R = vitrinaRenderer();
var RENDER_SRC = "var __name=function(f){return f};var VitrinaR=(" + vitrinaRenderer.toString().replace(/<\/(script)/gi, "<\\/$1") + ")();";
var VERSION = "1.0.0";
var ITER = 2e4;
var SESSION_DAYS = 30;
var MAX_FAILS = 8;
var FAIL_WINDOW = 15 * 6e4;
var MAX_IMG = 1400 * 1024;
var MAX_IMAGES = 400;
var MAX_CONFIG = 200 * 1024;
var MAX_BACKUP = 40 * 1024 * 1024;
var ready = null;
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
    ]).catch((e) => {
      ready = null;
      throw e;
    });
  }
  return ready.then(() => env.DB);
}
__name(db, "db");
var SetupError = class extends Error {
  static {
    __name(this, "SetupError");
  }
  constructor(code) {
    super(code);
    this.code = code;
  }
};
async function kvGet(env, k) {
  const row = await (await db(env)).prepare("SELECT v FROM kv WHERE k = ?").bind(k).first();
  return row ? row.v : null;
}
__name(kvGet, "kvGet");
async function kvSet(env, k, v) {
  await (await db(env)).prepare("INSERT INTO kv (k, v) VALUES (?, ?) ON CONFLICT(k) DO UPDATE SET v = excluded.v").bind(k, v).run();
}
__name(kvSet, "kvSet");
async function loadConfig(env) {
  const raw = await kvGet(env, "config");
  if (!raw) return null;
  try {
    return R.sanitize(JSON.parse(raw));
  } catch {
    return null;
  }
}
__name(loadConfig, "loadConfig");
var enc = new TextEncoder();
function hexOf(buf) {
  return [...new Uint8Array(buf)].map((b) => b.toString(16).padStart(2, "0")).join("");
}
__name(hexOf, "hexOf");
function randHex(bytes) {
  return hexOf(crypto.getRandomValues(new Uint8Array(bytes)));
}
__name(randHex, "randHex");
async function sha256(s) {
  return hexOf(await crypto.subtle.digest("SHA-256", enc.encode(s)));
}
__name(sha256, "sha256");
async function sameText(a, b) {
  const [ha, hb] = await Promise.all([a, b].map((s) => crypto.subtle.digest("SHA-256", enc.encode(String(s)))));
  return crypto.subtle.timingSafeEqual(ha, hb);
}
__name(sameText, "sameText");
async function pbkdf2(pass, saltHex, iter) {
  const key = await crypto.subtle.importKey("raw", enc.encode(pass), "PBKDF2", false, ["deriveBits"]);
  const salt = new Uint8Array(saltHex.match(/../g).map((h) => parseInt(h, 16)));
  return hexOf(await crypto.subtle.deriveBits({ name: "PBKDF2", hash: "SHA-256", salt, iterations: iter }, key, 256));
}
__name(pbkdf2, "pbkdf2");
function b64encode(bytes) {
  if (typeof bytes.toBase64 === "function") return bytes.toBase64();
  let s = "";
  for (let i = 0; i < bytes.length; i += 32768) s += String.fromCharCode.apply(null, bytes.subarray(i, i + 32768));
  return btoa(s);
}
__name(b64encode, "b64encode");
function b64decode(str) {
  if (typeof Uint8Array.fromBase64 === "function") return Uint8Array.fromBase64(str);
  const bin = atob(str);
  const out = new Uint8Array(bin.length);
  for (let i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
  return out;
}
__name(b64decode, "b64decode");
function imageType(b) {
  if (b.length < 12) return "";
  if (b[0] === 137 && b[1] === 80 && b[2] === 78 && b[3] === 71) return "image/png";
  if (b[0] === 255 && b[1] === 216 && b[2] === 255) return "image/jpeg";
  if (b[0] === 71 && b[1] === 73 && b[2] === 70 && b[3] === 56) return "image/gif";
  if (b[0] === 82 && b[1] === 73 && b[2] === 70 && b[3] === 70 && b[8] === 87 && b[9] === 69 && b[10] === 66 && b[11] === 80) return "image/webp";
  return "";
}
__name(imageType, "imageType");
function today() {
  return (/* @__PURE__ */ new Date()).toISOString().slice(0, 10);
}
__name(today, "today");
var BASE_HEADERS = {
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
__name(json, "json");
function fail(msg, status = 400) {
  return json({ error: msg }, status);
}
__name(fail, "fail");
async function readJson(request, max) {
  const len = parseInt(request.headers.get("content-length") || "0", 10);
  if (len > max) throw new HttpError(413, "Es demasiado grande.");
  const text = await request.text();
  if (text.length > max) throw new HttpError(413, "Es demasiado grande.");
  try {
    return JSON.parse(text);
  } catch {
    throw new HttpError(400, "Los datos no son v\xE1lidos.");
  }
}
__name(readJson, "readJson");
var HttpError = class extends Error {
  static {
    __name(this, "HttpError");
  }
  constructor(status, msg) {
    super(msg);
    this.status = status;
  }
};
function cookieOf(request, name) {
  const m = (request.headers.get("cookie") || "").match(new RegExp("(?:^|;\\s*)" + name + "=([a-f0-9]{64})"));
  return m ? m[1] : "";
}
__name(cookieOf, "cookieOf");
function sessionCookie(token, url, maxAge) {
  const secure = url.protocol === "https:" ? "; Secure" : "";
  return `vt=${token}; Path=/; HttpOnly; SameSite=Strict; Max-Age=${maxAge}${secure}`;
}
__name(sessionCookie, "sessionCookie");
async function isLogged(request, env) {
  const token = cookieOf(request, "vt");
  if (!token) return false;
  const row = await (await db(env)).prepare("SELECT exp FROM ses WHERE h = ?").bind(await sha256(token)).first();
  return !!row && row.exp > Date.now();
}
__name(isLogged, "isLogged");
var ipSalt = "";
async function ipKey(request, env) {
  if (!ipSalt) {
    ipSalt = await kvGet(env, "ipSalt") || "";
    if (!ipSalt) {
      ipSalt = randHex(16);
      await kvSet(env, "ipSalt", ipSalt);
    }
  }
  return (await sha256(ipSalt + ":" + (request.headers.get("cf-connecting-ip") || "local"))).slice(0, 32);
}
__name(ipKey, "ipKey");
var secretChecked = false;
var EXAMPLE_PASSWORD = "pon-aqui-una-contrasena-larga";
async function passwordState(env) {
  const secret = typeof env.CONTRASENA === "string" ? env.CONTRASENA : "";
  let rec = null;
  try {
    rec = JSON.parse(await kvGet(env, "pw") || "null");
  } catch {
    rec = null;
  }
  if (secret && !secretChecked) {
    let sf = null;
    try {
      sf = JSON.parse(await kvGet(env, "secretFp") || "null");
    } catch {
      sf = null;
    }
    const salt = sf && sf.salt ? sf.salt : randHex(16);
    const fp = await sha256(salt + ":" + secret);
    if (!sf) await kvSet(env, "secretFp", JSON.stringify({ salt, fp }));
    else if (!await sameText(fp, sf.fp)) {
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
__name(passwordState, "passwordState");
async function verifyPassword(env, pass) {
  const st = await passwordState(env);
  if (st.mode === "secret") return sameText(pass, st.secret);
  if (st.mode === "panel") return sameText(await pbkdf2(pass, st.rec.salt, st.rec.iter), st.rec.hash);
  return false;
}
__name(verifyPassword, "verifyPassword");
function publicHeaders(extra = {}) {
  return {
    ...BASE_HEADERS,
    "content-type": "text/html; charset=utf-8",
    "cache-control": "public, max-age=0, must-revalidate",
    "content-security-policy": "default-src 'none'; img-src 'self'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'self'; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'",
    ...extra
  };
}
__name(publicHeaders, "publicHeaders");
function placeholderPage() {
  return `<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>P\xE1gina en preparaci\xF3n</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3efe6;color:#161513;font:16px/1.5 system-ui,-apple-system,'Segoe UI',sans-serif;text-align:center;padding:24px;box-sizing:border-box}h1{font-size:22px;margin:0 0 8px}p{margin:0;opacity:.7}a{color:inherit}</style></head><body><div><h1>Esta p\xE1gina est\xE1 en preparaci\xF3n</h1><p>Si es tuya, entra en <a href="/admin">/admin</a> para montarla.</p></div></body></html>`;
}
__name(placeholderPage, "placeholderPage");
async function publicPage(request, env, url, ctx) {
  let cfg = null;
  try {
    cfg = await loadConfig(env);
  } catch (e) {
    if (!(e instanceof SetupError)) throw e;
  }
  if (!cfg) return new Response(placeholderPage(), { status: 200, headers: publicHeaders({ "cache-control": "no-store" }) });
  const ua = request.headers.get("user-agent") || "";
  const purpose = (request.headers.get("sec-purpose") || request.headers.get("purpose") || "").toLowerCase();
  if (request.method === "GET" && ua && !BOT_RE.test(ua) && !purpose.includes("prefetch") && !tooMany(request)) {
    ctx.waitUntil(count(env, ctx, "v").catch(() => {
    }));
  }
  const html = R.renderPage(cfg, {
    img: /* @__PURE__ */ __name((id) => "/i/" + id, "img"),
    absImg: /* @__PURE__ */ __name((id) => url.origin + "/i/" + id, "absImg"),
    hit: "/api/hit",
    inlineScript: true
  });
  return new Response(html, { headers: publicHeaders({ "content-security-policy": await publicCsp() }) });
}
__name(publicPage, "publicPage");
var hitHash = "";
async function publicCsp() {
  if (!hitHash) {
    const d = await crypto.subtle.digest("SHA-256", enc.encode(R.HIT_SCRIPT));
    hitHash = btoa(String.fromCharCode(...new Uint8Array(d)));
  }
  return `default-src 'none'; img-src 'self'; style-src 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; script-src 'sha256-${hitHash}'; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'`;
}
__name(publicCsp, "publicCsp");
function panelPage(url) {
  const nonce = randHex(16);
  const html = PANEL_HTML.replace(/__NONCE__/g, nonce).replace("/*__RENDER__*/", () => RENDER_SRC).replace("/*__HOST__*/", () => "var VITRINA_HOST=" + JSON.stringify({ kind: "cloudflare", api: "/api/{p}", img: "/i/{id}", page: url.origin + "/", version: VERSION }) + ";");
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
__name(panelPage, "panelPage");
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
__name(serveImage, "serveImage");
var BOT_RE = /bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|telegram|slack|discord|embedly|headless|lighthouse|curl|wget|python|go-http/i;
async function hit(request, env, ctx) {
  const ua = request.headers.get("user-agent") || "";
  if (!ua || BOT_RE.test(ua)) return new Response(null, { status: 204 });
  let d;
  try {
    d = await readJson(request, 512);
  } catch {
    return new Response(null, { status: 204 });
  }
  if (!d || typeof d !== "object") return new Response(null, { status: 204 });
  if (tooMany(request)) return new Response(null, { status: 204 });
  let key = "";
  if (d.t === "c" || d.t === "s") {
    const cfg = await loadConfig(env);
    if (!cfg) return new Response(null, { status: 204 });
    if (d.t === "c" && cfg.items.some((i) => i.id === d.id && i.kind === "link" && !i.hidden)) key = "c:" + d.id;
    if (d.t === "s" && cfg.socials.some((s) => s.type === d.id)) key = "s:" + d.id;
  }
  if (!key) return new Response(null, { status: 204 });
  await count(env, ctx, key);
  return new Response(null, { status: 204 });
}
__name(hit, "hit");
async function count(env, ctx, key) {
  const DB = await db(env);
  await DB.prepare("INSERT INTO stats (day, k, n) VALUES (?, ?, 1) ON CONFLICT(day, k) DO UPDATE SET n = n + 1").bind(today(), key).run();
  if (Math.random() < 0.02) ctx.waitUntil(cleanup(env));
}
__name(count, "count");
var recent = /* @__PURE__ */ new Map();
function tooMany(request) {
  const ip = request.headers.get("cf-connecting-ip") || "local";
  const minute = Math.floor(Date.now() / 6e4);
  const k = ip + "|" + minute;
  const n = (recent.get(k) || 0) + 1;
  recent.set(k, n);
  if (recent.size > 5e3) recent.clear();
  return n > 60;
}
__name(tooMany, "tooMany");
async function cleanup(env) {
  const DB = await db(env);
  const old = new Date(Date.now() - 400 * 864e5).toISOString().slice(0, 10);
  await DB.batch([
    DB.prepare("DELETE FROM stats WHERE day < ?").bind(old),
    DB.prepare("DELETE FROM fails WHERE ts < ?").bind(Date.now() - 864e5),
    DB.prepare("DELETE FROM ses WHERE exp < ?").bind(Date.now())
  ]);
}
__name(cleanup, "cleanup");
async function handleApi(request, env, url, ctx) {
  const path = url.pathname.slice(5);
  const method = request.method;
  if (path === "hit" && method === "POST") return hit(request, env, ctx);
  const origin = request.headers.get("origin");
  if (method !== "GET" && origin && origin !== url.origin) return fail("Origen no permitido.", 403);
  if (method !== "GET" && request.headers.get("x-vitrina") !== "1") return fail("Petici\xF3n no v\xE1lida.", 403);
  if (path === "estado" && method === "GET") {
    let setup = null, logged = false;
    try {
      const st = await passwordState(env);
      if (st.mode === "none") setup = "nopass";
      else if (st.mode === "weak") setup = "weak";
      else logged = await isLogged(request, env);
    } catch (e) {
      if (e instanceof SetupError) setup = e.code;
      else throw e;
    }
    return json({ setup, logged, version: VERSION });
  }
  if (path === "entrar" && method === "POST") {
    const DB2 = await db(env);
    const ip = await ipKey(request, env);
    const since = Date.now() - FAIL_WINDOW;
    const body = await readJson(request, 2048);
    const pass = typeof body.password === "string" ? body.password : "";
    const st = await passwordState(env);
    if (st.mode === "none") return fail("Falta la contrase\xF1a inicial (el secreto CONTRASENA en Cloudflare).", 409);
    if (st.mode === "weak") return fail("La contrase\xF1a de Cloudflare es demasiado corta o es la del ejemplo. C\xE1mbiala por una de 10 caracteres o m\xE1s.", 409);
    const ts = Date.now() + Math.random();
    const [, cnt] = await DB2.batch([
      DB2.prepare("INSERT INTO fails (ip, ts) VALUES (?, ?)").bind(ip, ts),
      DB2.prepare("SELECT COUNT(*) AS n FROM fails WHERE ip = ? AND ts > ?").bind(ip, since)
    ]);
    const n = cnt.results[0].n;
    if (n > MAX_FAILS) return fail("Demasiados intentos. Espera 15 minutos y vuelve a probar.", 429);
    if (!pass || !await verifyPassword(env, pass)) {
      const left = MAX_FAILS - n;
      return fail(left > 0 ? `Contrase\xF1a incorrecta. Te quedan ${left} intentos.` : "Contrase\xF1a incorrecta. Espera 15 minutos.", 401);
    }
    const token = randHex(32);
    await DB2.batch([
      DB2.prepare("DELETE FROM fails WHERE ip = ?").bind(ip),
      DB2.prepare("INSERT INTO ses (h, exp) VALUES (?, ?)").bind(await sha256(token), Date.now() + SESSION_DAYS * 864e5)
    ]);
    return json({ ok: true }, 200, { "set-cookie": sessionCookie(token, url, SESSION_DAYS * 86400) });
  }
  if (path === "salir" && method === "POST") {
    const token = cookieOf(request, "vt");
    if (token) await (await db(env)).prepare("DELETE FROM ses WHERE h = ?").bind(await sha256(token)).run();
    return json({ ok: true }, 200, { "set-cookie": sessionCookie("", url, 0) });
  }
  if (!await isLogged(request, env)) return fail("Tienes que entrar otra vez.", 401);
  const DB = await db(env);
  if (path === "config" && method === "GET") {
    const cfg = await loadConfig(env);
    const at = await kvGet(env, "publishedAt");
    return json({ config: cfg, publishedAt: at ? Number(at) : null });
  }
  if (path === "config" && method === "PUT") {
    const body = await readJson(request, MAX_CONFIG);
    const cfg = R.sanitize(body.config);
    const { results: imgRows } = await DB.prepare("SELECT id FROM img").all();
    const exists = new Set((imgRows || []).map((r) => r.id));
    let missing = 0;
    const keep = /* @__PURE__ */ __name((id) => {
      if (id && !exists.has(id)) {
        missing++;
        return "";
      }
      return id;
    }, "keep");
    cfg.profile.avatar = keep(cfg.profile.avatar);
    cfg.theme.bgImage = keep(cfg.theme.bgImage);
    if (cfg.theme.bgType === "image" && !cfg.theme.bgImage) cfg.theme.bgType = "solid";
    cfg.items.forEach((i) => {
      i.thumb = keep(i.thumb);
    });
    const now = Date.now();
    await kvSet(env, "config", JSON.stringify(cfg));
    await kvSet(env, "publishedAt", String(now));
    ctx.waitUntil(removeUnused(env, cfg, exists));
    return json({ ok: true, config: cfg, publishedAt: now, missing });
  }
  if (path === "imagen" && method === "POST") {
    const len = parseInt(request.headers.get("content-length") || "0", 10);
    if (len > MAX_IMG) return fail("La imagen pesa demasiado (m\xE1ximo 1,4 MB).", 413);
    const bytes = new Uint8Array(await request.arrayBuffer());
    if (bytes.length > MAX_IMG) return fail("La imagen pesa demasiado (m\xE1ximo 1,4 MB).", 413);
    const type = imageType(bytes);
    if (!type) return fail("Ese archivo no es una imagen JPG, PNG, WebP o GIF.", 415);
    const count2 = (await DB.prepare("SELECT COUNT(*) AS n FROM img").first()).n;
    if (count2 >= MAX_IMAGES) return fail("Has llegado al m\xE1ximo de im\xE1genes. Publica para borrar las que ya no usas.", 409);
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
    if (!await verifyPassword(env, actual)) return fail("La contrase\xF1a actual no es correcta.", 401);
    if (nueva.length < 10) return fail("La nueva contrase\xF1a tiene que tener al menos 10 caracteres.");
    if (nueva.length > 200) return fail("La nueva contrase\xF1a es demasiado larga.");
    const salt = randHex(16);
    const rec = { src: "panel", iter: ITER, salt, hash: await pbkdf2(nueva, salt, ITER) };
    await kvSet(env, "pw", JSON.stringify(rec));
    secretChecked = true;
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
    return json({ app: "vitrina", v: 1, exported: (/* @__PURE__ */ new Date()).toISOString(), config: cfg, images }, 200, {
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
      try {
        bytes = b64decode(im.data);
      } catch {
        continue;
      }
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
__name(handleApi, "handleApi");
async function removeUnused(env, cfg, exists) {
  const DB = await db(env);
  const used = new Set(R.usedImages(cfg));
  let since = {};
  try {
    since = JSON.parse(await kvGet(env, "imgUnused") || "{}") || {};
  } catch {
    since = {};
  }
  const now = Date.now(), next = {}, drop = [];
  for (const id of exists) {
    if (used.has(id)) continue;
    const t = typeof since[id] === "number" ? since[id] : now;
    if (now - t > 7 * 864e5) drop.push(id);
    else next[id] = t;
  }
  await kvSet(env, "imgUnused", JSON.stringify(next));
  for (let i = 0; i < drop.length; i += 20) {
    await DB.batch(drop.slice(i, i + 20).map((id) => DB.prepare("DELETE FROM img WHERE id = ?").bind(id)));
  }
}
__name(removeUnused, "removeUnused");
var worker_default = {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);
    const p = url.pathname;
    try {
      if (p.startsWith("/api/")) return await handleApi(request, env, url, ctx);
      if (request.method !== "GET" && request.method !== "HEAD") return new Response("M\xE9todo no permitido", { status: 405 });
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
export {
  worker_default as default
};
