import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Reportes/**/*.php',
        './resources/views/filament/reportes/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
