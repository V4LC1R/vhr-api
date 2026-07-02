<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script>
            // Aplica o tema antes da primeira pintura para evitar flash de tela clara.
            (function () {
                try {
                    var raw = localStorage.getItem('vhr-ui');
                    var theme = raw ? (JSON.parse(raw).state || {}).theme : 'system';
                    if (!theme) theme = 'system';
                    var isDark = theme === 'dark'
                        || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', isDark);
                } catch (e) {}
            })();
        </script>
        @routes
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
